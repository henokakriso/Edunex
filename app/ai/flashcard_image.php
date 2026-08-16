<?php
require_once __DIR__ . "/ai.php";

/* ================= FLASHCARD IMAGE RENDER (C backend) ================= */
class Ctl_flashcard_image {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $cardId = (int)($_GET['card'] ?? 0);
        $side = $_GET['side'] ?? 'front';
        $download = isset($_GET['dl']);
        $card = Database::one(
            "SELECT c.*, d.user_id FROM ai_cards c JOIN ai_decks d ON d.id = c.deck_id WHERE c.id = ?", [$cardId]);
        if (!$card || (int)$card['user_id'] !== $uid || $card['image'] === '') {
            http_response_code(404);
            exit('Not found');
        }
        $srcDir = realpath(FLASH_CARDS_PATH);
        $src = $srcDir !== false ? realpath($srcDir . '/' . basename((string)$card['image'])) : false;
        if ($src === false || strpos($src, $srcDir) !== 0 || !is_file($src)) {
            http_response_code(404);
            exit('Image missing');
        }
        $bin = FcardTool::binary();
        if (!is_file($bin) || !is_executable($bin)) {
            http_response_code(500);
            exit('Flashcard image tool unavailable');
        }
        $text = $side === 'back' ? (string)$card['back'] : (string)$card['front'];
        if ($text === '') $text = $side === 'back' ? 'Answer' : 'Question';
        $cacheDir = STORAGE_PATH . '/flashcards';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
        $hash = md5($cardId . '|' . $side . '|' . $text . '|' . filemtime($src));
        $out = $cacheDir . '/card_' . $cardId . '_' . $side . '_' . $hash . '.png';
        if (!is_file($out)) {
            $tf = tempnam(sys_get_temp_dir(), 'fct');
            file_put_contents($tf, $text);
            $cmd = escapeshellarg($bin) . ' ' . escapeshellarg($src) . ' ' . escapeshellarg($tf) . ' ' . escapeshellarg($out)
                . ($side === 'back' ? ' --band bottom' : ' --band bottom');
            exec($cmd . ' 2>&1', $o, $rc);
            @unlink($tf);
            if ($rc !== 0 || !is_file($out)) {
                error_log('[fcard] render failed: ' . implode(' ', $o));
                http_response_code(500);
                exit('Render failed');
            }
        }
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        if ($download) {
            $safe = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)$card['front']);
            $safe = mb_substr($safe, 0, 40) ?: 'flashcard';
            header('Content-Disposition: attachment; filename="' . $safe . '.png"');
        }
        readfile($out);
        exit;
    }
}
