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

    private function tracking(array $u): void {
        header('Content-Type: application/json');

        $tickets = Database::all(
            "SELECT t.id, t.page_route, t.page_label, t.description, t.status, t.frozen,
                    t.frozen_reason, t.created_at, t.claimed_at, t.resolved_at,
                    CONCAT(a.first_name, ' ', a.last_name) AS admin_name,
                    (SELECT detail FROM it_fix_logs l WHERE l.ticket_id = t.id ORDER BY l.created_at DESC LIMIT 1) AS last_admin_note
             FROM it_fix_tickets t
             LEFT JOIN users a ON a.id = t.it_admin_id
             WHERE t.requested_by = ?
             ORDER BY t.created_at DESC
             LIMIT 50",
            [(int)$u['id']]
        );

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
