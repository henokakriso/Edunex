<?php
/**
 * IT Admin — Fix Ticket System
 * Flow: User reports issue -> ticket created with API token -> IT admin uses token to access only that page -> fixes -> resolves
 */

class Ctl_it_admin {
    public function run(): void {
        $u = require_role('it_admin');
        $action = trim($_GET['r'] ?? '', '/');
        $route = str_replace('it_admin/', '', $action);

        match ($route) {
            'dashboard' => $this->dashboard($u),
            'tickets' => $this->tickets($u),
            'ticket' => $this->ticketDetail($u),
            'fix' => $this->fixEntry($u),
            'fix-session' => $this->fixSession($u),
            'resolve' => $this->resolve($u),
            'audit' => $this->audit($u),
            default => $this->dashboard($u),
        };
    }

    private function dashboard(array $u): void {
        $sid = (int)($u['school_id'] ?? 0);
        $stats = [
            'open' => (int)Database::scalar("SELECT COUNT(*) FROM it_fix_tickets WHERE school_id = ? AND status = 'open'", [$sid]),
            'in_progress' => (int)Database::scalar("SELECT COUNT(*) FROM it_fix_tickets WHERE school_id = ? AND status = 'in_progress'", [$sid]),
            'resolved_today' => (int)Database::scalar("SELECT COUNT(*) FROM it_fix_tickets WHERE school_id = ? AND status = 'resolved' AND DATE(resolved_at) = CURDATE()", [$sid]),
            'total' => (int)Database::scalar("SELECT COUNT(*) FROM it_fix_tickets WHERE school_id = ?", [$sid]),
        ];

        $recentTickets = Database::all(
            "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS requested_by_name
             FROM it_fix_tickets t
             JOIN users u ON u.id = t.requested_by
             WHERE t.school_id = ?
             ORDER BY t.created_at DESC LIMIT 10", [$sid]);

        Router::render('app/it_admin/dashboard', [
            'title' => 'IT Admin Dashboard',
            'stats' => $stats,
            'recentTickets' => $recentTickets,
        ]);
    }

    private function tickets(array $u): void {
        $sid = (int)($u['school_id'] ?? 0);
        $status = $_GET['status'] ?? '';

        $where = "t.school_id = ?";
        $args = [$sid];
        if ($status && in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $where .= " AND t.status = ?";
            $args[] = $status;
        }

        $tickets = Database::all(
            "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS requested_by_name,
                    CONCAT(i.first_name, ' ', i.last_name) AS it_admin_name
             FROM it_fix_tickets t
             JOIN users u ON u.id = t.requested_by
             LEFT JOIN users i ON i.id = t.it_admin_id
             WHERE $where
             ORDER BY t.created_at DESC", $args);

        Router::render('app/it_admin/tickets', [
            'title' => 'Fix Tickets',
            'tickets' => $tickets,
            'currentStatus' => $status,
        ]);
    }

    private function ticketDetail(array $u): void {
        $sid = (int)($u['school_id'] ?? 0);
        $tid = (int)($_GET['id'] ?? 0);

        $ticket = Database::one(
            "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS requested_by_name
             FROM it_fix_tickets t
             JOIN users u ON u.id = t.requested_by
             WHERE t.id = ? AND t.school_id = ?", [$tid, $sid]);
        if (!$ticket) { flash('danger', 'Ticket not found.'); redirect('it_admin/tickets'); }

        $scopes = Database::all("SELECT * FROM it_fix_scopes WHERE ticket_id = ?", [$tid]);
        $logs = Database::all(
            "SELECT l.*, CONCAT(i.first_name, ' ', i.last_name) AS it_admin_name
             FROM it_fix_logs l
             JOIN users i ON i.id = l.it_admin_id
             WHERE l.ticket_id = ? ORDER BY l.created_at", [$tid]);

        Router::render('app/it_admin/ticket', [
            'title' => 'Ticket #' . $tid,
            'ticket' => $ticket,
            'scopes' => $scopes,
            'logs' => $logs,
        ]);
    }

    private function fixEntry(array $u): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $token = trim((string)($_POST['token'] ?? ''));
            if ($token === '') { flash('danger', 'Enter a ticket token.'); redirect('it_admin/fix'); }

            $ticket = Database::one(
                "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS requested_by_name
                 FROM it_fix_tickets t
                 JOIN users u ON u.id = t.requested_by
                 WHERE t.api_token = ? AND t.school_id = ? AND t.status IN ('open', 'in_progress')",
                [$token, $u['school_id'] ?? 0]);

            if (!$ticket) { flash('danger', 'Invalid or closed ticket token.'); redirect('it_admin/fix'); }

            Database::update('it_fix_tickets', [
                'it_admin_id' => $u['id'],
                'status' => 'in_progress',
                'claimed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$ticket['id']]);

            log_activity('it_fix.claim', "Ticket #{$ticket['id']} claimed by IT admin", $u['id']);

            redirect('it_admin/fix-session?id=' . $ticket['id']);
        }
        Router::render('app/it_admin/fix_entry', ['title' => 'Enter Fix Token']);
    }

    private function fixSession(array $u): void {
        $sid = (int)($u['school_id'] ?? 0);
        $tid = (int)($_GET['id'] ?? 0);

        $ticket = Database::one(
            "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS requested_by_name
             FROM it_fix_tickets t
             JOIN users u ON u.id = t.requested_by
             WHERE t.id = ? AND t.school_id = ? AND t.status = 'in_progress' AND t.it_admin_id = ?",
            [$tid, $sid, $u['id']]);

        if (!$ticket) { flash('danger', 'No active fix session for this ticket.'); redirect('it_admin/tickets'); }

        $scopes = Database::all("SELECT * FROM it_fix_scopes WHERE ticket_id = ?", [$tid]);

        Router::render('app/it_admin/fix_session', [
            'title' => 'Fixing Ticket #' . $tid,
            'ticket' => $ticket,
            'scopes' => $scopes,
        ]);
    }

    private function resolve(array $u): void {
        $sid = (int)($u['school_id'] ?? 0);
        $tid = (int)($_POST['ticket_id'] ?? 0);

        $ticket = Database::one(
            "SELECT * FROM it_fix_tickets WHERE id = ? AND school_id = ? AND it_admin_id = ? AND status = 'in_progress'",
            [$tid, $sid, $u['id']]);

        if (!$ticket) { flash('danger', 'Ticket not found or not in progress.'); redirect('it_admin/tickets'); }

        $note = trim((string)($_POST['resolution_note'] ?? ''));
        Database::update('it_fix_tickets', [
            'status' => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$tid]);

        Database::insert('it_fix_logs', [
            'ticket_id' => $tid,
            'it_admin_id' => $u['id'],
            'action' => 'resolve',
            'detail' => $note ?: 'Ticket resolved',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        log_activity('it_fix.resolve', "Ticket #$tid resolved by IT admin", $u['id']);
        flash('success', 'Ticket #' . $tid . ' resolved.');
        redirect('it_admin/tickets');
    }

    private function audit(array $u): void {
        $sid = (int)($u['school_id'] ?? 0);
        $logs = Database::all(
            "SELECT l.*, CONCAT(i.first_name, ' ', i.last_name) AS it_admin_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS requested_by_name
             FROM it_fix_logs l
             JOIN users i ON i.id = l.it_admin_id
             JOIN it_fix_tickets t ON t.id = l.ticket_id
             JOIN users u ON u.id = t.requested_by
             WHERE t.school_id = ?
             ORDER BY l.created_at DESC LIMIT 200", [$sid]);

        Router::render('app/it_admin/audit', ['title' => 'Fix Audit Log', 'logs' => $logs]);
    }
}

/**
 * Ticket API — creates fix tickets from any page
 */
class Ctl_ticket_api {
    public function run(): void {
        $u = require_login();
        $action = trim($_GET['r'] ?? '', '/');
        $route = str_replace('ticket/', '', $action);

        match ($route) {
            'create' => $this->create($u),
            'status' => $this->status($u),
            default => http_response_code(404),
        };
    }

    private function create(array $u): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'POST required'; return; }
        csrf_verify();

        $pageRoute = trim((string)($_POST['page_route'] ?? ''));
        $pageLabel = trim((string)($_POST['page_label'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($pageRoute === '') { flash('danger', 'Page route required.'); back(); return; }

        $token = bin2hex(random_bytes(32));

        $ticketId = Database::insert('it_fix_tickets', [
            'school_id' => (int)($u['school_id'] ?? 0) ?: null,
            'requested_by' => (int)$u['id'],
            'page_route' => $pageRoute,
            'page_label' => $pageLabel,
            'description' => $description,
            'api_token' => $token,
            'status' => 'open',
        ]);

        Database::insert('it_fix_scopes', [
            'ticket_id' => $ticketId,
            'scope_route' => $pageRoute,
            'scope_label' => $pageLabel,
        ]);

        log_activity('it_fix.create', "Ticket #$ticketId created for $pageRoute", (int)$u['id']);

        $_SESSION['fix_ticket'] = [
            'id' => (int)$ticketId,
            'token' => $token,
            'page' => $pageLabel ?: $pageRoute,
        ];

        flash('success', 'Fix ticket #' . $ticketId . ' created. Share token with IT admin.');
        back();
    }

    private function status(array $u): void {
        header('Content-Type: application/json');
        $ticket = $_SESSION['fix_ticket'] ?? null;
        if (!$ticket) { echo json_encode(['active' => false]); return; }
        echo json_encode([
            'active' => true,
            'id' => $ticket['id'],
            'page' => $ticket['page'],
        ]);
    }
}

/**
 * IT Admin access check — verifies IT admin has a valid ticket for the current page
 */
function it_admin_can_access(array $u, string $pageRoute): bool {
    if (($u['role'] ?? '') !== 'it_admin') return true;
    $ticket = Database::one(
        "SELECT id FROM it_fix_tickets
         WHERE it_admin_id = ? AND status = 'in_progress'
         AND (page_route = ? OR page_route LIKE ?)
         AND claimed_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
         LIMIT 1",
        [(int)$u['id'], $pageRoute, $pageRoute . '%']);
    return (bool)$ticket;
}

function it_admin_ticket_for_page(array $u, string $pageRoute): ?array {
    if (($u['role'] ?? '') !== 'it_admin') return null;
    return Database::one(
        "SELECT t.*, CONCAT(r.first_name, ' ', r.last_name) AS requested_by_name
         FROM it_fix_tickets t
         JOIN users r ON r.id = t.requested_by
         WHERE t.it_admin_id = ? AND t.status = 'in_progress'
         AND (t.page_route = ? OR t.page_route LIKE ?)
         AND t.claimed_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
         LIMIT 1",
        [(int)$u['id'], $pageRoute, $pageRoute . '%']);
}
