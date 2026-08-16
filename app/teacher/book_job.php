<?php
/**
 * Teacher: book-generation job page — live progress, stop button, result.
 */

class Ctl_book_job {
    public function run(): void {
        $u = require_role('teacher');
        $id = (string)($_GET['job'] ?? '');
        if (!AiJob::validId($id) || !is_dir(AiJob::dir($id))) {
            flash('danger', 'Job not found.');
            redirect('teacher/book');
        }
        $st = AiJob::read($id);
        // Page load on a finished job: finalize once and show the result.
        $result = null;
        if (($st['state'] ?? '') === 'done' || ($st['state'] ?? '') === 'cancelled') {
            $result = AiJob::finalize($id);
        } elseif (($st['state'] ?? '') === 'error') {
            $result = AiJob::finalize($id);
        }
        Router::render('app/teacher/book_job', [
            'title' => 'Generating course…', 'job' => $id, 'state' => $st, 'result' => $result,
        ]);
    }
}
