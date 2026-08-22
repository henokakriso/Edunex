<?php
/**
 * AiRouter — PHP bridge to the native C AI router (storage/bin/ai_router).
 *
 * The C router picks the best model per request (subject-aware),
 * keeps all models warm for zero-delay switching, answers exact
 * repeats from its on-disk cache, and streams tokens as NDJSON:
 *   R <model>  C  T <delta>  D  E <error>
 */
class AiRouter
{
    private const BIN = 'ai_router';

    public static function available(): bool
    {
        $bin = self::path();
        return $bin !== null && is_file($bin) && is_executable($bin);
    }

    private static function path(): ?string
    {
        $bin = STORAGE_PATH . '/bin/' . self::BIN;
        return is_file($bin) ? $bin : null;
    }

    private static function cacheDir(): string
    {
        $dir = STORAGE_PATH . '/cache/ai';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir;
    }

    /**
     * @param array $messages [{role,content}, ...]
     * @return array{model:string,text:string,cached:bool}|null null = router failed (caller falls back)
     */
    public static function chat(array $messages, string $tags = 'general', int $maxTokens = 180, float $temp = 0.5, int $timeout = 170): ?array
    {
        $out = ['model' => 'unknown', 'text' => '', 'cached' => false];
        $ok = self::run(['chat'], [
            'system'      => array_shift($messages)['content'] ?? 'You are Edunex, a helpful tutor.',
            'messages'    => array_values($messages),
            'models'      => self::modelsFor($tags),
            'tags'        => $tags,
            'max_tokens'  => $maxTokens,
            'temperature' => $temp,
        ], function (string $tag, string $val) use (&$out): void {
            switch ($tag) {
                case 'R': $out['model'] = $val; break;
                case 'C': $out['cached'] = true; break;
                case 'T': $out['text'] .= $val; break;
            }
        }, $timeout);
        return $ok ? $out : null;
    }

    /**
     * Streams deltas to $onDelta(fn(string)). Returns
     * ['model'=>, 'cached'=>, 'done'=>bool] or null on failure.
     */
    public static function stream(array $messages, string $tags, int $maxTokens, float $temp, callable $onDelta, int $timeout = 170, ?callable $onModel = null): ?array
    {
        $out = ['model' => 'unknown', 'cached' => false, 'done' => false];
        $ok = self::run(['chat'], [
            'system'      => array_shift($messages)['content'] ?? 'You are Edunex, a helpful tutor.',
            'messages'    => array_values($messages),
            'models'      => self::modelsFor($tags),
            'tags'        => $tags,
            'max_tokens'  => $maxTokens,
            'temperature' => $temp,
        ], function (string $tag, string $val) use (&$out, $onDelta, $onModel): void {
            switch ($tag) {
                case 'R': $out['model'] = $val; if ($onModel) $onModel($val); break;
                case 'C': $out['cached'] = true; break;
                case 'T': $onDelta($val); break;
                case 'D': $out['done'] = true; break;
            }
        }, $timeout);
        return $ok ? $out : null;
    }

    /** Preloads every enabled model so switching is instant. */
    public static function warm(int $timeout = 60): void
    {
        $models = self::enabledModels();
        if (!$models) return;
        self::run(array_merge(['warm'], $models), null, function (): void {}, $timeout);
    }

    /** Detached background warm-up (no latency on the page). Re-runs at most every 10 min. */
    public static function warmAsync(): void
    {
        $mark = self::cacheDir() . '/warm.mark';
        if (is_file($mark) && (time() - (int)filemtime($mark)) < 600) return;
        @touch($mark);
        $bin = self::path();
        $models = self::enabledModels();
        if (!$bin || !$models) return;
        $args = array_merge([$bin, 'warm'], array_map('escapeshellarg', $models));
        $cmd = 'env EDUNEX_CACHE=' . escapeshellarg(self::cacheDir()) . ' ' . implode(' ', $args) . ' > /dev/null 2>&1 &';
        @exec($cmd);
    }

    /** The C router decides per request; PHP hands it the full enabled set. */
    private static function modelsFor(string $tags): array
    {
        return self::enabledModels();
    }

    private static function enabledModels(): array
    {
        $models = setting('ai_models') ? json_decode((string)setting('ai_models'), true) : null;
        if (is_array($models) && $models) {
            return array_values(array_filter(array_map('trim', (array)$models), 'strlen'));
        }
        // Auto-discover all models from Ollama so the C router can pick the fastest one
        $ch = curl_init('http://127.0.0.1:11434/api/tags');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 2]);
        $r = curl_exec($ch);
        curl_close($ch);
        if ($r !== false) {
            $d = json_decode((string)$r, true);
            if (isset($d['models']) && is_array($d['models'])) {
                $found = [];
                foreach ($d['models'] as $m) {
                    $name = $m['name'] ?? $m['model'] ?? '';
                    if ($name !== '') $found[] = preg_replace('/:latest$/', '', $name);
                }
                if ($found) {
                    // Only keep the best models — avoid keeping too many loaded
                    $keep = ['edunex-tutor', 'llama3.2:1b', 'phi3:mini', 'gemma2:2b', 'qwen2.5:3b', 'qwen2.5:0.5b'];
                    $filtered = array_values(array_filter($found, fn($n) => in_array($n, $keep)));
                    return $filtered ?: array_slice($found, 0, 5);
                }
            }
        }
        return [(string)(setting('ai_model') ?: 'edunex-tutor')];
    }

    private static function run(array $argv, ?array $payload, callable $onEvent, int $timeout): bool
    {
        $bin = self::path();
        if (!$bin) return false;

        $spec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = $_ENV;
        $env['EDUNEX_CACHE'] = self::cacheDir();
        $env['EDUNEX_AI_HOST'] = rtrim((string)(setting('ai_api_url') ?: 'http://127.0.0.1:11434'), '/');

        $proc = proc_open(array_merge([$bin], $argv), $spec, $pipes, STORAGE_PATH . '/bin', $env);
        if (!is_resource($proc)) return false;

        if ($payload !== null) {
            fwrite($pipes[0], json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        $deadline = microtime(true) + $timeout;
        $buffer = '';
        $aborted = false;
        while (true) {
            if (connection_aborted()) { $aborted = true; break; } // stop button / closed tab
            $read = [$pipes[1]];
            $w = null; $x = null;
            $left = min($deadline - microtime(true), 0.5); // poll often → instant stop detection
            if ($left <= 0) break;
            $sel = @stream_select($read, $w, $x, 0, (int)($left * 1e6));
            if ($sel === false) break;                    // select error
            if ($sel === 0) continue;                     // timeout: keep polling (cold model = slow first token)
            $chunk = fread($pipes[1], 65536);
            if ($chunk === '' || $chunk === false) {
                if (feof($pipes[1])) break;
                continue;
            }
            $buffer .= $chunk;
            while (($nl = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $nl);
                $buffer = substr($buffer, $nl + 1);
                $line = trim($line);
                if ($line === '') continue;
                $tag = $line[0];
                $val = strlen($line) > 2 ? substr($line, 2) : '';
                if ($tag === 'E') { $full = null; break 2; }
                if ($tag === 'T') $val = str_replace(["\\\\", "\\n", "\\r"], ["\\", "\n", "\r"], $val);
                $onEvent($tag, $val);
                if ($tag === 'D') break 2;
            }
        }
        $ok = !isset($full) || $full !== null;
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (is_resource($proc)) {
            // force-quit the C engine when the user stopped — frees the CPU immediately
            proc_terminate($proc, $aborted ? 9 : 15);
            proc_close($proc);
        }
        return $ok;
    }
}
