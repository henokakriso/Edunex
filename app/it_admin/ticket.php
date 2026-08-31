<?php
/**
 * Ticket API — creates fix tickets from any page
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
