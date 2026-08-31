<?php
/**
 * Ticket API — creates, tracks, and freezes fix tickets from any page
 * Any logged-in user can create tickets (no role restriction)
 */
class Ctl_ticket_api {
    public function run(): void {
        $u = require_login();
        $action = trim($_GET['r'] ?? '', '/');
        $route = str_replace('ticket/', '', $action);

        match ($route) {
            'create' => $this->create($u),
            'status' => $this->status($u),
            'tracking' => $this->tracking($u),
            'freeze' => $this->freeze($u),
            'unfreeze' => $this->unfreeze($u),
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
            'priority' => 'normal',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
        ]);

        Database::insert('it_fix_scopes', [
            'ticket_id' => $ticketId,
            'scope_route' => $pageRoute,
            'scope_label' => $pageLabel,
        ]);

        Database::insert('it_fix_logs', [
            'ticket_id' => $ticketId,
            'it_admin_id' => (int)$u['id'],
            'action' => 'created',
            'detail' => "Ticket created for: $pageLabel ($pageRoute)",
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

    private function tracking(array $u): void {
        header('Content-Type: application/json');

        // Auto-escalate open tickets past 6 hours
        Database::run("UPDATE it_fix_tickets SET priority = 'high', escalated_at = NOW() WHERE requested_by = ? AND status IN ('open','in_progress') AND priority = 'normal' AND created_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)", [(int)$u['id']]);
        // Auto-escalate to critical past 12 hours
        Database::run("UPDATE it_fix_tickets SET priority = 'critical', escalated_at = NOW() WHERE requested_by = ? AND status IN ('open','in_progress') AND priority = 'high' AND created_at < DATE_SUB(NOW(), INTERVAL 12 HOUR)", [(int)$u['id']]);
        // Auto-close expired tickets (past 24 hours)
        Database::run("UPDATE it_fix_tickets SET status = 'closed' WHERE requested_by = ? AND status IN ('open','in_progress') AND expires_at IS NOT NULL AND expires_at < NOW()", [(int)$u['id']]);

        $tickets = Database::all(
            "SELECT t.id, t.page_route, t.page_label, t.description, t.status, t.priority,
                    t.frozen, t.frozen_reason, t.created_at, t.claimed_at, t.resolved_at,
                    t.expires_at, t.escalated_at, t.admin_status,
                    CONCAT(a.first_name, ' ', a.last_name) AS admin_name,
                    a.first_name AS admin_first_name
             FROM it_fix_tickets t
             LEFT JOIN users a ON a.id = t.it_admin_id
             WHERE t.requested_by = ?
             ORDER BY t.created_at DESC
             LIMIT 50",
            [(int)$u['id']]
        );

        // Get activity logs for each ticket
        foreach ($tickets as &$ticket) {
            $ticket['logs'] = Database::all(
                "SELECT l.action, l.detail, l.created_at,
                        CONCAT(u.first_name, ' ', u.last_name) AS actor_name
                 FROM it_fix_logs l
                 LEFT JOIN users u ON u.id = l.it_admin_id
                 WHERE l.ticket_id = ?
                 ORDER BY l.created_at DESC
                 LIMIT 20",
                [(int)$ticket['id']]
            );
            // Calculate time remaining
            if ($ticket['expires_at']) {
                $ticket['hours_remaining'] = max(0, round((strtotime($ticket['expires_at']) - time()) / 3600, 1));
            } else {
                $ticket['hours_remaining'] = null;
            }
        }
        unset($ticket);

        echo json_encode(['tickets' => $tickets]);
    }

    private function freeze(array $u): void {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST required']); return; }
        csrf_verify();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? ''));

        if (!$ticketId) { echo json_encode(['error' => 'Invalid ticket']); return; }

        $ticket = Database::one(
            "SELECT id, status, frozen FROM it_fix_tickets WHERE id = ? AND requested_by = ?",
            [$ticketId, (int)$u['id']]
        );

        if (!$ticket) { echo json_encode(['error' => 'Ticket not found']); return; }

        Database::update('it_fix_tickets', [
            'frozen' => 1,
            'frozen_reason' => $reason ?: 'Frozen by user',
        ], 'id = ?', [$ticketId]);

        Database::insert('it_fix_logs', [
            'ticket_id' => $ticketId,
            'it_admin_id' => (int)$u['id'],
            'action' => 'frozen_by_user',
            'detail' => $reason ?: 'Frozen by user',
        ]);

        log_activity('it_fix.freeze', "Ticket #$ticketId frozen by user", (int)$u['id']);

        echo json_encode(['ok' => true, 'message' => 'Ticket frozen. IT admin cannot proceed until you unfreeze it.']);
    }

    private function unfreeze(array $u): void {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST required']); return; }
        csrf_verify();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        if (!$ticketId) { echo json_encode(['error' => 'Invalid ticket']); return; }

        $ticket = Database::one(
            "SELECT id, frozen FROM it_fix_tickets WHERE id = ? AND requested_by = ?",
            [$ticketId, (int)$u['id']]
        );

        if (!$ticket) { echo json_encode(['error' => 'Ticket not found']); return; }

        Database::update('it_fix_tickets', [
            'frozen' => 0,
            'frozen_reason' => null,
        ], 'id = ?', [$ticketId]);

        Database::insert('it_fix_logs', [
            'ticket_id' => $ticketId,
            'it_admin_id' => (int)$u['id'],
            'action' => 'unfrozen_by_user',
            'detail' => 'Ticket unfrozen by user',
        ]);

        log_activity('it_fix.unfreeze', "Ticket #$ticketId unfrozen by user", (int)$u['id']);

        echo json_encode(['ok' => true, 'message' => 'Ticket unfrozen. IT admin can now proceed.']);
    }
}
