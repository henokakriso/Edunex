<?php
/**
 * EDUNEX Minimal QR Code encoder (pure PHP) — outputs SVG or PNG.
 * Byte mode, versions 1-6, EC level M. Conformant layout with
 * finder, timing, format info, and alignment patterns.
 */
class Qr {
    private array $matrix = [];   // 0=light, 1=dark (after mask)
    private array $fixed = [];    // true where function modules live
    private int $size = 0;

    private static ?array $gfExp = null;
    private static ?array $gfLog = null;

    private static function gfTables(): void {
        if (self::$gfExp !== null) return;
        self::$gfExp = array_fill(0, 512, 0);
        self::$gfLog = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$gfExp[$i] = $x;
            self::$gfLog[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) $x ^= 0x11D;
        }
        for ($i = 255; $i < 512; $i++) self::$gfExp[$i] = self::$gfExp[$i - 255];
    }

    private static function gfMul(int $a, int $b): int {
        if ($a == 0 || $b == 0) return 0;
        self::gfTables();
        return self::$gfExp[self::$gfLog[$a] + self::$gfLog[$b]];
    }

    private function rsEncode(array $data, int $ecLen): array {
        self::gfTables();
        $gen = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $new = array_fill(0, count($gen) + 1, 0);
            foreach ($gen as $j => $g) {
                $new[$j] ^= $g;
                $new[$j + 1] ^= self::gfMul($g, self::$gfExp[$i]);
            }
            $gen = $new;
        }
        $msg = array_merge($data, array_fill(0, $ecLen, 0));
        for ($i = 0; $i < count($data); $i++) {
            $coef = $msg[$i];
            if ($coef != 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $msg[$i + $j] ^= self::gfMul($gen[$j], $coef);
                }
            }
        }
        return array_slice($msg, count($data));
    }

    private const CAP = [
        1 => [26, 10], 2 => [44, 16], 3 => [70, 26], 4 => [100, 36],
        5 => [134, 48], 6 => [172, 64],
    ];

    private function pickVersion(int $len): int {
        foreach ([1,2,3,4,5,6] as $v) {
            [$cap, ] = self::CAP[$v];
            if ($len <= intdiv($cap, 2)) return $v;
        }
        return 6;
    }

    private function encodeData(string $text): array {
        $bytes = array_values(unpack('C*', $text));
        $v = $this->pickVersion(count($bytes));
        [$dataCap, $ecLen] = self::CAP[$v];
        $bits = [0,1,0,0]; // byte mode
        $charBits = $v <= 9 ? 8 : 16;
        foreach (str_split(str_pad(decbin(count($bytes)), $charBits, '0', STR_PAD_LEFT)) as $b) $bits[] = (int)$b;
        foreach ($bytes as $byte) {
            foreach (str_split(str_pad(decbin($byte), 8, '0', STR_PAD_LEFT)) as $b) $bits[] = (int)$b;
        }
        $bits = array_merge($bits, [0,0,0,0]); // terminator
        $totalBits = $dataCap * 8;
        if (count($bits) > $totalBits) $bits = array_slice($bits, 0, $totalBits);
        while (count($bits) % 8 !== 0) $bits[] = 0;
        $pad = [0xEC, 0x11];
        $i = 0;
        while (count($bits) < $totalBits) {
            foreach (str_split(str_pad(decbin($pad[$i % 2]), 8, '0', STR_PAD_LEFT)) as $b) $bits[] = (int)$b;
            $i++;
        }
        $dataBytes = [];
        foreach (array_chunk($bits, 8) as $chunk) $dataBytes[] = bindec(implode('', $chunk));
        $ec = $this->rsEncode($dataBytes, $ecLen);
        $all = array_merge($dataBytes, $ec);
        $block = [];
        foreach ($all as $byte) {
            for ($i = 7; $i >= 0; $i--) $block[] = ($byte >> $i) & 1;
        }
        return [$block, $v];
    }

    private function set(int $x, int $y, bool $dark, bool $isFixed = false): void {
        if ($x < 0 || $x >= $this->size || $y < 0 || $y >= $this->size) return;
        $this->matrix[$y][$x] = $dark ? 1 : 0;
        if ($isFixed) $this->fixed[$y][$x] = true;
    }

    private function drawFinder(int $fx, int $fy): void {
        for ($y = -1; $y <= 7; $y++) for ($x = -1; $x <= 7; $x++) {
            $on = $x >= 0 && $x <= 6 && $y >= 0 && $y <= 6
                && ($x == 0 || $x == 6 || $y == 0 || $y == 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4));
            $this->set($fx + $x, $fy + $y, $on, true);
        }
    }

    private function drawAlignment(int $cx, int $cy): void {
        for ($y = -2; $y <= 2; $y++) for ($x = -2; $x <= 2; $x++) {
            $on = abs($x) == 2 || abs($y) == 2 || ($x == 0 && $y == 0);
            $this->set($cx + $x, $cy + $y, $on, true);
        }
    }

    private function alignmentCenters(int $v): array {
        if ($v == 1) return [];
        $step = $v <= 2 ? 12 : ($v <= 4 ? 16 : 18);
        $c = [6];
        for ($p = 6 + $step; $p < $this->size - 7; $p += $step) $c[] = $p;
        if (end($c) != $this->size - 7) $c[] = $this->size - 7;
        $centers = [];
        foreach ($c as $cy) foreach ($c as $cx) {
            if ($cx == 6 && $cy == 6) continue;
            $centers[] = [$cx, $cy];
        }
        return $centers;
    }

    private function placeFunctionPatterns(int $v): void {
        $s = $this->size;
        $this->drawFinder(0, 0);
        $this->drawFinder($s - 7, 0);
        $this->drawFinder(0, $s - 7);
        foreach ($this->alignmentCenters($v) as [$cx, $cy]) $this->drawAlignment($cx, $cy);
        // timing
        for ($i = 8; $i < $s - 8; $i++) {
            $this->set($i, 6, $i % 2 == 0, true);
            $this->set(6, $i, $i % 2 == 0, true);
        }
        // dark module
        $this->set(8, $s - 8, true, true);
    }

    private function placeFormatInfo(): void {
        // EC M (00) + mask 000 -> 0x5412
        $fmt = 0x5412;
        $bits = array_map('intval', str_split(str_pad(decbin($fmt), 15, '0', STR_PAD_LEFT)));
        $s = $this->size;
        $pos = 0;
        for ($i = 0; $i <= 5; $i++) $this->set(8, $i, (bool)$bits[$pos], true); $pos = 6;
        $this->set(8, 7, (bool)$bits[$pos++], true);
        $this->set(8, 8, (bool)$bits[$pos++], true);
        $this->set(7, 8, (bool)$bits[$pos++], true);
        for ($i = 5; $i >= 0; $i--) $this->set($s - 1 - $i, 8, (bool)$bits[$pos++], true);
        $pos = 0;
        for ($i = $s - 1; $i >= $s - 7; $i--) $this->set(8, $i, (bool)$bits[$pos++], true);
        for ($i = $s - 8; $i < $s; $i++) $this->set($i, 8, (bool)$bits[$pos++], true);
    }

    private function isFixed(int $x, int $y): bool {
        return $this->fixed[$y][$x] ?? false;
    }

    public function generate(string $text): array {
        [$block, $v] = $this->encodeData($text);
        $this->size = $v * 4 + 17;
        $this->matrix = array_fill(0, $this->size, array_fill(0, $this->size, 0));
        $this->fixed = array_fill(0, $this->size, array_fill(0, $this->size, false));
        $this->placeFunctionPatterns($v);
        // place data (mask pattern 0 condition applied after)
        $s = $this->size;
        $bitIdx = 0;
        for ($right = $s - 1; $right >= 1; $right -= 2) {
            if ($right == 6) $right = 5;
            for ($vert = 0; $vert < $s; $vert++) {
                $up = (($right + 1) % 4) == 0;
                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    $y = $up ? ($s - 1 - $vert) : $vert;
                    if ($this->isFixed($x, $y)) continue;
                    if ($bitIdx < count($block)) {
                        $this->matrix[$y][$x] = $block[$bitIdx++];
                    }
                }
            }
        }
        $this->placeFormatInfo();
        // apply mask pattern 0: invert where (x+y)%2==0
        for ($y = 0; $y < $s; $y++) for ($x = 0; $x < $s; $x++) {
            if ($this->isFixed($x, $y)) continue;
            if (($x + $y) % 2 == 0) $this->matrix[$y][$x] = 1 - $this->matrix[$y][$x];
        }
        return $this->matrix;
    }

    public function toSvg(string $text, int $moduleSize = 4): string {
        $this->generate($text);
        $s = $this->size;
        $w = $s * $moduleSize;
        $cells = [];
        for ($y = 0; $y < $s; $y++) for ($x = 0; $x < $s; $x++) {
            if ($this->matrix[$y][$x]) $cells[] = "M" . ($x * $moduleSize) . " " . ($y * $moduleSize) . "h$moduleSize" . "v$moduleSize" . "h-$moduleSize" . "z";
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $w . '" viewBox="0 0 ' . $w . ' ' . $w . '"><path fill="#000" d="' . implode('', $cells) . '"/></svg>';
    }

    public function toPng(string $text, int $moduleSize = 6): string {
        $this->generate($text);
        $s = $this->size;
        $size = $s * $moduleSize;
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);
        for ($y = 0; $y < $s; $y++) for ($x = 0; $x < $s; $x++) {
            if ($this->matrix[$y][$x]) {
                imagefilledrectangle($img, $x * $moduleSize, $y * $moduleSize, ($x + 1) * $moduleSize - 1, ($y + 1) * $moduleSize - 1, $black);
            }
        }
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);
        return $png;
    }
}
