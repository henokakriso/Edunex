<?php
/**
 * Learning Games: memory match, quiz race
 */
class Ctl_index {
    public function run(): void {
        $u = require_login();
        $game = $_GET['game'] ?? 'memory';
        $deckId = (int)($_GET['deck'] ?? 0);
        $courseId = (int)($_GET['course'] ?? 0);

        $decks = Database::all(
            "SELECT d.id, d.title, COUNT(c.id) AS card_count
             FROM ai_flashcard_decks d JOIN ai_flashcards c ON c.deck_id = d.id
             WHERE d.user_id = ? GROUP BY d.id ORDER BY d.title", [(int)$u['id']]
        );

        $cards = [];
        if ($deckId) {
            $cards = Database::all(
                "SELECT front, back FROM ai_flashcards WHERE deck_id = ? AND user_id = ? ORDER BY RAND()", [$deckId, (int)$u['id']]
            );
        }

        $questions = [];
        if ($courseId && in_array($game, ['quiz_race'], true)) {
            $questions = Database::all(
                "SELECT q.question, q.type, q.options, q.correct_answer
                 FROM exam_questions q
                 JOIN exams e ON e.id = q.exam_id
                 WHERE e.course_id = ? ORDER BY RAND() LIMIT 10", [$courseId]
            );
        }

        $courses = Database::all(
            "SELECT DISTINCT c.id, c.title FROM courses c
             JOIN enrollments en ON en.course_id = c.id
             WHERE en.user_id = ? AND c.status = 'published' ORDER BY c.title", [(int)$u['id']]
        );

        Router::render('app/games/index', [
            'title' => 'Learning Games', 'game' => $game,
            'decks' => $decks, 'cards' => $cards, 'deckId' => $deckId,
            'courses' => $courses, 'courseId' => $courseId, 'questions' => $questions,
        ]);
    }
}
