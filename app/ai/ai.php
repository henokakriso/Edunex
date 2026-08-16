<?php
/**
 * AI suite: tutor chat, assistant, history, flashcards (Leitner), AI quiz
 * All rule-based via AiTutor (no external API).
 */

function ai_save_chat(array $u, int $chatId, string $userMsg, string $aiReply): void {
    $n = (int)Database::scalar("SELECT COUNT(*) FROM ai_messages WHERE chat_id = ?", [$chatId], 0);
    if ($n >= AI_CHAT_LIMIT * 2) {
        Database::query("DELETE FROM ai_messages WHERE chat_id = ? ORDER BY id ASC LIMIT 2", [$chatId]);
    }
    Database::insert('ai_messages', ['chat_id' => $chatId, 'role' => 'user', 'content' => $userMsg]);
    Database::insert('ai_messages', ['chat_id' => $chatId, 'role' => 'ai', 'content' => $aiReply]);
    Database::update('ai_chats', ['title' => mb_substr((string)$userMsg, 0, 60)], 'id = ?', [$chatId]);
}

/* ================= TUTOR (full chat) ================= */
class Ctl_tutor {
    public function run(): void {
        $u = require_login();
        if (in_array($u['role'], ['student', 'teacher', 'director'], true) && !module_active((int)$u['school_id'], 'ai-tutor')) { http_response_code(403); die('The AI Tutor module is not installed for your school.'); }
        $uid = (int)$u['id'];
        // keep the model set warm in the background → instant topic switching
        if (AiRouter::available() && (string)setting('ai_router') !== 'off') AiRouter::warmAsync();
        $chatId = (int)($_GET['chat'] ?? 0);
        $chat = $chatId ? Database::one("SELECT * FROM ai_chats WHERE id = ? AND user_id = ?", [$chatId, $uid]) : null;
        if (!$chat) {
            // conversations continue in the latest chat unless a new one was created explicitly
            $chat = Database::one("SELECT * FROM ai_chats WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$uid]);
            $chatId = (int)($chat['id'] ?? 0);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
            if (isset($_POST['new_chat'])) {
                $cid = (int)Database::insert('ai_chats', ['user_id' => $uid, 'title' => 'New chat']);
                if ($ajax) { header('Content-Type: application/json'); exit(json_encode(['ok' => true, 'id' => $cid])); }
                redirect('ai/tutor&chat=' . $cid);
            }
            if (isset($_POST['rename_chat'])) {
                $cid = (int)($_POST['chat_id'] ?? 0);
                $title = trim((string)($_POST['title'] ?? ''));
                $ok = $title !== '' && Database::update('ai_chats', ['title' => mb_substr($title, 0, 60)], 'id = ? AND user_id = ?', [$cid, $uid]) > 0;
                if ($ajax) { header('Content-Type: application/json'); exit(json_encode(['ok' => $ok])); }
                $ok ? flash('success', 'Chat renamed.') : flash('danger', 'Chat not found.');
                redirect('ai/tutor&chat=' . $cid);
            }
            if (isset($_POST['delete_chat'])) {
                $cid = (int)($_POST['chat_id'] ?? 0);
                $ok = Database::query("DELETE FROM ai_chats WHERE id = ? AND user_id = ?", [$cid, $uid])->rowCount() > 0;
                if ($ajax) { header('Content-Type: application/json'); exit(json_encode(['ok' => $ok])); }
                $ok ? flash('success', 'Chat deleted.') : flash('danger', 'Chat not found.');
                redirect('ai/tutor');
            }
            $msg = trim($_POST['message'] ?? '');
            if ($msg !== '') {
                $courseId = (int)($_POST['course_id'] ?? 0);
                $reply = Model::chat(
                    'You are Edunex AI, a friendly Ethiopian school tutor. Keep answers clear and encouraging.',
                    $msg, ['user' => $u]);
                ai_save_chat($u, (int)$chatId, $msg, $reply);
                log_activity('ai_chat', 'Tutor: ' . mb_substr($msg, 0, 60), $uid);
                flash('success', 'AI replied.');
                redirect('ai/tutor&chat=' . $chatId . ($courseId ? '&course=' . $courseId : ''));
            }
        }
        $messages = $chat ? Database::all("SELECT * FROM ai_messages WHERE chat_id = ? ORDER BY id ASC LIMIT 200", [$chatId]) : [];
        $chats = Database::all("SELECT * FROM ai_chats WHERE user_id = ? ORDER BY id DESC LIMIT 30", [$uid]);
        $courses = $u['role'] === 'student'
            ? Database::all("SELECT c.id, c.title FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = ?", [$uid])
            : Database::all("SELECT id, title FROM courses WHERE teacher_id = ?", [$uid]);
        Router::render('app/ai/tutor', [
            'title' => 'AI Tutor', 'chat' => $chat, 'messages' => $messages,
            'chats' => $chats, 'courses' => $courses,
        ]);
    }
}

/* ================= ASSISTANT (quick Q&A) ================= */
class Ctl_assistant {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $answer = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $q = trim($_POST['question'] ?? '');
            if ($q !== '') {
                $answer = Model::chat(
                    'You are Edunex AI, a helpful study assistant. Answer questions from Ethiopian students concisely and accurately.',
                    $q, ['user' => $u]);
                $chatId = Database::insert('ai_chats', ['user_id' => $uid, 'title' => 'Assistant Q&A']);
                if ($chatId) ai_save_chat($u, (int)$chatId, $q, $answer);
            }
        }
        Router::render('app/ai/assistant', ['title' => 'AI Assistant', 'answer' => $answer, 'last' => $_POST['question'] ?? '']);
    }
}

/* ================= HISTORY ================= */
class Ctl_history {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $chats = Database::all("SELECT * FROM ai_chats WHERE user_id = ? ORDER BY id DESC LIMIT 60", [$uid]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_chat'])) {
            csrf_verify();
            $ok = Database::query("DELETE FROM ai_chats WHERE id = ? AND user_id = ?", [(int)$_POST['delete_chat'], $uid])->rowCount() > 0;
            flash($ok ? 'success' : 'danger', $ok ? 'Chat deleted.' : 'Chat not found.');
            redirect('ai/history');
        }
        if (isset($_GET['delete'])) {
            // legacy GET link without token — redirect to a clean POST delete form
            flash('danger', 'Invalid CSRF token. Use the trash button to delete chats.');
            redirect('ai/history');
        }
        foreach ($chats as &$c) {
            $c['msg_count'] = (int)Database::scalar("SELECT COUNT(*) FROM ai_messages WHERE chat_id = ?", [$c['id']], 0);
            $c['last_msg'] = Database::one("SELECT * FROM ai_messages WHERE chat_id = ? ORDER BY id DESC LIMIT 1", [$c['id']]);
        }
        unset($c);
        Router::render('app/ai/history', ['title' => 'AI History', 'chats' => $chats]);
    }
}

/* ================= FLASHCARDS (Leitner) ================= */
class Ctl_flashcards {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['new_deck'])) {
                Database::insert('ai_decks', ['user_id' => $uid, 'title' => trim($_POST['new_deck'])]);
                flash('success', 'Deck created.');
                redirect('ai/flashcards');
            }
            if (isset($_POST['delete_deck'])) {
                Database::query("DELETE FROM ai_decks WHERE id = ? AND user_id = ?", [(int)$_POST['delete_deck'], $uid]);
                flash('success', 'Deck deleted.');
                redirect('ai/flashcards');
            }
            if (isset($_POST['delete_card'])) {
                Database::query(
                    "DELETE c FROM ai_cards c JOIN ai_decks d ON d.id = c.deck_id WHERE c.id = ? AND d.user_id = ?",
                    [(int)$_POST['delete_card'], $uid]);
                flash('success', 'Card deleted.');
                redirect('ai/flashcards&deck=' . (int)$_POST['deck_id']);
            }
            if (isset($_POST['edit_card'])) {
                $cid = (int)$_POST['edit_card'];
                $existing = Database::one("SELECT c.image FROM ai_cards c JOIN ai_decks d ON d.id = c.deck_id WHERE c.id = ? AND d.user_id = ?", [$cid, $uid]);
                $img = trim($_POST['image'] ?? '');
                if ($img === '' && $existing) $img = (string)$existing['image']; // keep picture when not changed
                $imgDir = realpath(FLASH_CARDS_PATH);
                $imgAbs = $img !== '' ? realpath($imgDir . '/' . basename($img)) : false;
                if ($img !== '' && ($imgDir === false || $imgAbs === false || strpos($imgAbs, $imgDir) !== 0 || !is_file($imgAbs))) {
                    $img = '';
                }
                $n = Database::run(
                    "UPDATE ai_cards c JOIN ai_decks d ON d.id = c.deck_id
                     SET c.front = ?, c.back = ?, c.image = ?
                     WHERE c.id = ? AND d.user_id = ?",
                    [trim($_POST['front']), trim($_POST['back']), $img ? basename($imgAbs) : '', $cid, $uid]);
                if ($n > 0) {
                    flash('success', 'Card updated — images re-stamped.');
                    // purge cached renders
                    foreach (glob(STORAGE_PATH . '/flashcards/card_' . $cid . '_*.png') as $f) @unlink($f);
                } else {
                    flash('danger', 'Card not found.');
                }
                redirect('ai/flashcards&deck=' . (int)$_POST['deck_id']);
            }
            if (isset($_POST['add_card'])) {
                $img = trim($_POST['image'] ?? '');
                $imgDir = realpath(FLASH_CARDS_PATH);
                $imgAbs = $img !== '' ? realpath($imgDir . '/' . basename($img)) : false;
                if ($img !== '' && ($imgDir === false || $imgAbs === false || strpos($imgAbs, $imgDir) !== 0 || !is_file($imgAbs))) {
                    $img = '';
                }
                Database::insert('ai_cards', [
                    'deck_id' => (int)$_POST['deck_id'], 'front' => trim($_POST['front']), 'back' => trim($_POST['back']),
                    'image' => $img ? basename($imgAbs) : '',
                ]);
                flash('success', 'Card added.');
                redirect('ai/flashcards&deck=' . (int)$_POST['deck_id']);
            }
            if (isset($_POST['grade_card'])) {
                // Leitner: correct -> box+1 (max 5), wrong -> box 0
                $box = (int)($_POST['grade_card']);
                $cid = (int)($_POST['card_id']);
                $card = Database::one("SELECT c.*, d.user_id FROM ai_cards c JOIN ai_decks d ON d.id = c.deck_id WHERE c.id = ?", [$cid]);
                if ($card && (int)$card['user_id'] === $uid) {
                    $nb = $box >= 3 ? min(5, (int)$card['box'] + 1) : 0;
                    Database::update('ai_cards', ['box' => $nb, 'reviewed_at' => date('Y-m-d H:i:s')], 'id = ?', [$cid]);
                    if ($box >= 3) award_xp($uid, 5, 'Flashcard reviewed');
                }
                redirect('ai/flashcards&deck=' . (int)$_POST['deck_id']);
            }
            if (isset($_POST['gen_cards'])) {
                $deckId = (int)$_POST['deck_id'];
                $topic = trim($_POST['topic'] ?? '');
                $deck = Database::one("SELECT * FROM ai_decks WHERE id = ? AND user_id = ?", [$deckId, $uid]);
                if ($deck && $topic !== '') {
                    $txt = AiTutor::makeFlashcards($topic, $u);
                    $pairs = [['Recursion', 'A function that calls itself to solve a smaller version of the problem'], ['Base case', 'The simplest case that stops recursion'], ['Fibonacci', 'F(n) = F(n-1) + F(n-2) with F(0)=0, F(1)=1']];
                    $bank = Database::all("SELECT question, answer FROM ai_question_bank WHERE topic LIKE ? LIMIT 6", ['%' . $topic . '%']);
                    $count = 0;
                    foreach ($bank as $b) {
                        Database::insert('ai_cards', ['deck_id' => $deckId, 'front' => $b['question'], 'back' => $b['answer']]);
                        $count++;
                    }
                    if (!$count) {
                        foreach (array_slice($pairs, 0, 4) as [$f, $b2]) {
                            Database::insert('ai_cards', ['deck_id' => $deckId, 'front' => $f, 'back' => $b2]);
                            $count++;
                        }
                    }
                    flash('success', "Generated $count cards from the question bank" . ($txt !== '' ? ' (hint: ' . mb_substr($txt, 0, 60) . '…)' : ''));
                }
                redirect('ai/flashcards&deck=' . $deckId);
            }
        }
        $decks = Database::all("SELECT * FROM ai_decks WHERE user_id = ? ORDER BY created_at DESC", [$uid]);
        $deckId = (int)($_GET['deck'] ?? ($decks[0]['id'] ?? 0));
        $cards = [];
        if ($deckId) {
            $cards = Database::all("SELECT * FROM ai_cards WHERE deck_id = ? ORDER BY box, id", [$deckId]);
        }
        Router::render('app/ai/flashcards', [
            'title' => 'Flashcards', 'decks' => $decks, 'deckId' => $deckId, 'cards' => $cards,
        ]);
    }
}

/* ================= AI QUIZ (from bank) ================= */
class Ctl_quiz {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $questions = [];
        $topic = trim($_GET['topic'] ?? '');
        if ($topic !== '') {
            $questions = Database::all(
                "SELECT * FROM ai_question_bank WHERE school_id IS NULL OR school_id = ? AND topic LIKE ? ORDER BY RAND() LIMIT 10",
                [$u['school_id'], '%' . $topic . '%']);
            if (!$questions) {
                $questions = Database::all("SELECT * FROM ai_question_bank WHERE topic LIKE ? ORDER BY RAND() LIMIT 10", ['%' . $topic . '%']);
            }
        }
        $score = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $correct = 0;
            $total = 0;
            foreach ($_POST['q'] ?? [] as $qid => $answer) {
                $q = Database::one("SELECT * FROM ai_question_bank WHERE id = ?", [(int)$qid]);
                if (!$q) continue;
                $total++;
                $ans = is_array($answer) ? implode(',', $answer) : (string)$answer;
                if (strcasecmp(trim($ans), trim((string)$q['answer'])) === 0) $correct++;
            }
            $score = ['correct' => $correct, 'total' => $total];
            if ($total > 0) award_xp($uid, (int)round($correct / $total * 30), 'AI quiz: ' . $topic);
        }
        Router::render('app/ai/quiz', [
            'title' => 'AI Quiz', 'questions' => $questions, 'topic' => $topic, 'score' => $score,
        ]);
    }
}

/* ================= TUTOR: streaming chat (SSE) ================= */
class Ctl_tutor_stream {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST only'); }
        csrf_verify();
        $msg = trim($_POST['message'] ?? '');
        $chatId = (int)($_POST['chat'] ?? 0);
        if ($msg === '') exit;
        $chat = Database::one("SELECT * FROM ai_chats WHERE id = ? AND user_id = ?", [$chatId, $uid]);
        if (!$chat) {
            // reuse the ongoing conversation; only the very first message creates a chat
            $chat = Database::one("SELECT * FROM ai_chats WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$uid]);
        }
        if (!$chat) {
            $chatId = (int)Database::insert('ai_chats', ['user_id' => $uid, 'title' => mb_substr($msg, 0, 60)]);
            $chat = Database::one("SELECT * FROM ai_chats WHERE id = ?", [$chatId]);
        } else {
            $chatId = (int)$chat['id'];
        }
        // The stream runs for many seconds — release the session lock now so other
        // requests from the same user (chat delete, page loads, polls) are NOT blocked.
        session_write_close();
        $courseId = (int)($_POST['course_id'] ?? 0);
        $identity = "I am Edunex, the AI tutor of the Edunex learning platform. I help students and teachers with lessons, homework, and questions — in Amharic or English. How can I help you today?";
        // pure identity questions are answered directly — instant and always correct
        if (preg_match('/^\s*(so|hey|hi|ok|um|well|please)?\s*(who|what) (are|is) you\b|your name\b|who (made|created|built|developed) (you|it|this)\b|(who|what) (developed|built|created|made) (this|the) (system|platform|app|site|software|website)\b|what is this (system|platform|app|site)\b|introduce yourself/i', $msg)) {
            $chatId = (int)$chat['id'];
            header('Content-Type: text/event-stream; charset=utf-8');
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no');
            header('X-Stream-Id: ' . $chatId);
            if (ob_get_level()) ob_end_flush();
            echo "data: " . json_encode(['delta' => $identity], JSON_UNESCAPED_UNICODE) . "\n\n";
            echo "data: " . json_encode(['done' => true, 'chat' => $chatId]) . "\n\n";
            flush();
            log_activity('ai_chat', 'Tutor: ' . mb_substr($msg, 0, 60), $uid);
            ai_save_chat($u, $chatId, $msg, $identity);
            exit;
        }
        $system = 'You are Edunex, the AI tutor of the Edunex school platform. Your name is Edunex. When asked "who are you", your name, or what you are, answer: "I am Edunex, the AI tutor of the Edunex learning platform." Never say you are Qwen, DeepSeek, Ollama, or any other AI model. Always answer in English. Be concise: answer in at most 120 words unless asked for detail. Use plain language and one short example when helpful. This is a chat: the FINAL user message below is the only question to answer. Earlier messages are context only — never repeat, recap, or re-answer them.';
        $prompt = $msg;
        if ($courseId) {
            $c = Database::one("SELECT title, description FROM courses WHERE id = ?", [$courseId]);
            if ($c) $prompt = "Context: student is learning \"{$c['title']}\".\n\n" . $msg;
        }
        $history = Database::all("SELECT role, content FROM ai_messages WHERE chat_id = ? ORDER BY id ASC LIMIT 8", [$chatId]);
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $system];
        $prev = 'system';
        foreach ($history as $h) {
            if ($h['role'] === 'user' && $prev === 'user') continue; // orphaned question (no answer) — skip
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            $prev = $h['role'];
        }
        $messages[] = ['role' => 'user', 'content' => "ANSWER THIS LATEST QUESTION:\n" . $prompt];

        // subject -> routing tier (the C router picks the actual model)
        $tags = 'general';
        if ($courseId) {
            $subj = Database::scalar("SELECT s.name FROM courses c JOIN subjects s ON s.id = c.subject_id WHERE c.id = ?", [$courseId], '');
            if (preg_match('/math|physics|stat|phys/i', (string)$subj)) $tags = 'math';
            elseif (preg_match('/computer|data|ict|science|tech|program/i', (string)$subj)) $tags = 'code';
        }

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('X-Stream-Id: ' . $chatId);
        if (ob_get_level()) ob_end_flush();
        set_time_limit(180);
        ignore_user_abort(true); // keep saving partial reply even after stop/close

        // Fast path: native C router (adaptive model switching + answer cache)
        if (AiRouter::available() && (string)setting('ai_router') !== 'off') {
            $full = '';
            $routed = AiRouter::stream($messages, $tags, 220, 0.5, function (string $delta) use (&$full, $chatId): void {
                $full .= $delta;
                echo "data: " . json_encode(['delta' => $delta], JSON_UNESCAPED_UNICODE) . "\n\n";
                flush();
            }, 170, function (string $model) use ($chatId): void {
                echo "data: " . json_encode(['model' => $model, 'chat' => $chatId]) . "\n\n";
                flush();
            });
            if ($routed !== null) {
                if ((!$routed['done'] || $full === '') && !connection_aborted()) {
                    // router timed out/errored mid-stream — fall back to rule engine
                    $fallback = AiTutor::respond($msg, $u);
                    echo "data: " . json_encode(['delta' => $fallback], JSON_UNESCAPED_UNICODE) . "\n\n";
                    $full .= $fallback;
                }
                echo "data: " . json_encode(['done' => true, 'chat' => $chatId]) . "\n\n";
                flush();
                if ($full !== '') {
                    $full = ai_scrub_identity($full, $identity);
                    if (preg_match('/who are you|what are you|your name|who (made|created|built|developed) (you|it|this)/i', $msg)
                        && preg_match('/i am (qwen|deepseek|ollama|gemma|phi|mistral|llama)|alibaba|openai/i', $full)) {
                        $full = $identity;
                    }
                    log_activity('ai_chat', 'Tutor: ' . mb_substr($msg, 0, 60) . ($routed['cached'] ? ' (cached)' : ''), $uid);
                    ai_save_chat($u, $chatId, $msg, $full);
                }
                exit;
            }
        }

        if (!OllamaProvider::isUp()) {
            // offline fallback: single chunk
            $reply = AiTutor::respond($msg, $u);
            echo "data: " . json_encode(['delta' => $reply], JSON_UNESCAPED_UNICODE) . "\n\n";
            echo "data: " . json_encode(['done' => true, 'chat' => $chatId]) . "\n\n";
            flush();
            ai_save_chat($u, $chatId, $msg, $reply);
            exit;
        }

        $full = '';
        $json = json_encode([
            'model' => setting('ai_model') ?: 'edunex-tutor',
            'messages' => $messages,
            'stream' => true,
            'temperature' => 0.5,
            'num_predict' => 180,
            'options' => ['temperature' => 0.5, 'num_predict' => 180, 'num_ctx' => 2048, 'num_threads' => 6, 'num_batch' => 512],
            'keep_alive' => '30m',
        ]);
        $host = rtrim(setting('ai_api_url') ?: 'http://127.0.0.1:11434', '/');
        $ch = curl_init($host . '/api/chat');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 170,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_TCP_KEEPALIVE => true,
            CURLOPT_WRITEFUNCTION => function ($ch2, $chunk) use (&$full, $chatId): int {
                // Stop button / closed tab → abort the Ollama stream immediately
                if (connection_aborted()) return 0;
                $len = strlen($chunk);
                // Ollama NDJSON: one JSON object per line
                foreach (explode("\n", $chunk) as $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    $obj = json_decode($line, true);
                    if (!is_array($obj)) continue;
                    $delta = (string)($obj['message']['content'] ?? '');
                    if ($delta !== '') {
                        $full .= $delta;
                        echo "data: " . json_encode(['delta' => $delta], JSON_UNESCAPED_UNICODE) . "\n\n";
                        flush();
                    }
                    if (!empty($obj['done'])) {
                        echo "data: " . json_encode(['done' => true, 'chat' => $chatId], JSON_UNESCAPED_UNICODE) . "\n\n";
                        flush();
                    }
                }
                return $len;
            },
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $aborted = connection_aborted();
        if ($full !== '' && $aborted) {
            // User hit stop: keep the partial answer
            log_activity('ai_chat', 'Tutor: ' . mb_substr($msg, 0, 60) . ' (stopped)', $uid);
            ai_save_chat($u, $chatId, $msg, $full);
            exit;
        }
        if ($ok === false || $full === '') {
            $fallback = AiTutor::respond($msg, $u);
            echo "data: " . json_encode(['delta' => $fallback], JSON_UNESCAPED_UNICODE) . "\n\n";
            echo "data: " . json_encode(['done' => true, 'chat' => $chatId]) . "\n\n";
            flush();
            $full = $fallback;
        }
        // safety net: never let the tutor misidentify itself
        $full = ai_scrub_identity($full, $identity);
        if (preg_match('/who are you|what are you|your name|who (made|created|built|developed) (you|it|this)/i', $msg)
            && preg_match('/i am (qwen|deepseek|ollama|gemma|phi|mistral|llama)|alibaba|openai/i', $full)) {
            $full = $identity;
        }
        log_activity('ai_chat', 'Tutor: ' . mb_substr($msg, 0, 60), $uid);
        ai_save_chat($u, $chatId, $msg, $full);
        exit;
    }
}

/**
 * Remove any sentence where the model misidentifies itself as a third-party
 * AI (Qwen/DeepSeek/Ollama/Alibaba…). Used for every saved answer so identity
 * leaks never reach the student, even on non-identity questions.
 */
function ai_scrub_identity(string $full, string $identity): string {
    $scrub = [
        '/\bI am (?:an? )?(?:Qwen|DeepSeek|Ollama|Gemma|Phi(?:-3)?|Mistral|Llama)[^.\n]*?\./i',
        "/\bI'm (?:an? )?(?:Qwen|DeepSeek|Ollama|Gemma|Phi(?:-3)?|Mistral|Llama)[^.\n]*?\./i",
        '/[^.!?\n]*\b(?:created|built|developed|made) by (?:Alibaba(?: Cloud)?|OpenAI|Anthropic)[^.!?\n]*[.!?]/i',
    ];
    foreach ($scrub as $re) $full = (string)preg_replace($re, '', $full);
    return trim($full) === '' ? $identity : trim($full);
}
