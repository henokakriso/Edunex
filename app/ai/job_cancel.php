<?php
/**
 * AI job endpoints: cancellation.
 */

class Ctl_ai_job_cancel {
    public function run(): void {
        $u = require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST only'); }
        csrf_verify();
        $id = (string)($_POST['job'] ?? ($_GET['job'] ?? ''));
        if (!AiJob::validId($id)) { http_response_code(400); exit('Bad job id'); }
        header('Content-Type: application/json');
        echo json_encode(['ok' => AiJob::cancel($id)]);
    }
}
