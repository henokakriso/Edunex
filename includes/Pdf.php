<?php
/**
 * EDUNEX Official PDF Design System v2
 * 
 * Clean, structured PDF generation with:
 * - Official Edunex header with branding
 * - Ministry authority header (optional)
 * - Auto-generated Document ID
 * - Section headers with accent lines
 * - Tables with alternating row shading
 * - Footer with branding, license, page numbers
 */
class Pdf {
    private array $pages = [];    // Array of content streams, one per page
    private int $currentPage = 0;
    private float $y;
    private float $margin = 50;
    private float $pageW;
    private float $pageH;
    private float $headerH;
    private float $footerH = 50;
    private string $docId;
    private bool $isMinistry;
    private string $fontName = 'Helvetica';
    private string $boldFont = 'Helvetica-Bold';
    private string $italicFont = 'Helvetica-Oblique';
    private bool $headerDrawn = false;
    private string $watermarkText = '';
    private string $watermarkSub = '';
    private ?string $watermarkImage = null;
    private array $images = []; // name => ['path'=>, 'objId'=>]
    private array $headerImages = []; // 'flag' => path, 'ministry' => path

    public function __construct(string $orientation = 'portrait', string $pageSize = 'A4', bool $ministry = false) {
        $w = $h = 612;
        if ($pageSize === 'A4') { $w = 595.28; $h = 841.89; }
        if ($orientation === 'landscape') [$w, $h] = [$h, $w];
        $this->pageW = $w;
        $this->pageH = $h;
        $this->isMinistry = $ministry;
        $this->headerH = $ministry ? 80 : 50;
        $this->docId = 'EDU-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $this->startPage();
    }

    /** Start a new page content stream */
    private function startPage(): void {
        $this->pages[] = '';
        $this->currentPage = count($this->pages) - 1;
        $this->y = $this->pageH - $this->headerH - 10;
        $this->headerDrawn = false;
    }

    private function esc(string $s): string {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function out(string $code): void {
        $this->pages[$this->currentPage] .= $code . "\n";
    }

    private function text(string $str, float $sz, bool $bold, float $x, float $y): void {
        $f = $bold ? $this->boldFont : $this->fontName;
        $this->out("BT /$f $sz Tf $x $y Td (" . $this->esc($str) . ") Tj ET");
    }

    private function line(float $x1, float $y1, float $x2, float $y2): void {
        $this->out("$x1 $y1 m $x2 $y2 l S");
    }

    private function rect(float $x, float $y, float $w, float $h, bool $fill = false): void {
        $this->out("$x $y m " . ($x + $w) . " $y l " . ($x + $w) . " " . ($y + $h) . " l $x " . ($y + $h) . " l " . ($fill ? "f" : "S"));
    }

    private function ensureSpace(float $needed): void {
        $bottomLimit = $this->footerH + 10;
        if ($this->y - $needed < $bottomLimit) {
            $this->newPage();
        }
    }

    /** Draw header on current page if not yet drawn */
    private function drawHeader(): void {
        if ($this->headerDrawn) return;
        $this->headerDrawn = true;
        $mh = $this->margin;
        $right = $this->pageW - $mh;
        $top = $this->pageH;

        if ($this->isMinistry) {
            // Top border line
            $this->line($mh, $top - 12, $right, $top - 12);
            $this->text("FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA", 8, false, $mh, $top - 22);
            $this->text("MINISTRY OF EDUCATION", 9, true, $mh, $top - 33);
            $this->line($mh, $top - 40, $right, $top - 40);
            $this->text("EDUNEX LMS", 7, false, $mh, $top - 50);
            $this->text($this->docId, 7, false, $right - 110, $top - 22);
            $this->text("DOCUMENT ID", 5, false, $right - 110, $top - 32);
        } else {
            $this->line($mh, $top - 10, $right, $top - 10);
            $this->text("EDUNEX LMS", 10, true, $mh, $top - 22);
            $this->text("Education Management System", 7, false, $mh, $top - 32);
            $this->line($mh, $top - 38, $right, $top - 38);
            $this->text($this->docId, 7, false, $right - 110, $top - 22);
            $this->text("DOCUMENT ID", 5, false, $right - 110, $top - 32);
        }
    }

    /** Set watermark text shown on every page */
    public function setWatermark(string $text, string $subtext = ''): self {
        $this->watermarkText = $text;
        $this->watermarkSub = $subtext;
        return $this;
    }

    /** Set watermark as a JPEG image (centered, faded) */
    public function setWatermarkImage(string $jpegPath): self {
        $this->watermarkImage = $jpegPath;
        if (file_exists($jpegPath)) {
            $size = @getimagesize($jpegPath);
            $this->images['watermark'] = [
                'name' => 'WmLogo',
                'objId' => 0,
                'w' => $size[0] ?? 300,
                'h' => $size[1] ?? 167,
                'data' => file_get_contents($jpegPath),
            ];
        }
        return $this;
    }

    /** Set header images: flag (top-left) and ministry logo (top-right) */
    public function setHeaderImages(string $flagPath = '', string $ministryPath = ''): self {
        $this->headerImages['flag'] = $flagPath;
        $this->headerImages['ministry'] = $ministryPath;
        return $this;
    }

    /** Draw footer on current page */
    private function drawFooter(): void {
        // Watermark image is handled by Python pipeline (add_watermark.py)
        // Draw text watermark if no image watermark
        if ($this->watermarkText && empty($this->watermarkImage)) {
            $cx = $this->pageW / 2;
            $cy = $this->pageH / 2;
            $this->out("0.90 0.90 0.90 rg");
            $this->out("BT /Helvetica-Bold 42 Tf");
            $tw = strlen($this->watermarkText) * 14;
            $this->out(($cx - $tw/2) . " $cy Td (" . $this->esc($this->watermarkText) . ") Tj ET");
            if ($this->watermarkSub) {
                $this->out("BT /Helvetica 10 Tf");
                $sw = strlen($this->watermarkSub) * 3;
                $this->out(($cx - $sw/2) . " " . ($cy - 18) . " Td (" . $this->esc($this->watermarkSub) . ") Tj ET");
            }
            $this->out("0 0 0 rg");
        }

        $mh = $this->margin;
        $right = $this->pageW - $mh;
        $fy = $this->footerH;
        // Top line
        $this->line($mh, $fy, $right, $fy);
        // Branding left
        $this->text("EDUNEX LMS", 6.5, true, $mh, $fy - 12);
        $this->text("henockakriso.com  \xC2\xB7  GitHub @henokakriso", 5.5, false, $mh, $fy - 21);
        $this->text("ARWE-PL Licensed [" . date('Y') . "]", 5.5, false, $mh, $fy - 30);
        // Doc ID center
        $this->text($this->docId, 5, false, ($this->pageW / 2) - 50, $fy - 12);
        // Page number right
        $this->text("Page " . ($this->currentPage + 1) . " of {N}", 6, false, $right - 60, $fy - 12);
    }

    /** Start a new page */
    public function newPage(): void {
        $this->drawFooter();
        $this->startPage();
        $this->drawHeader();
    }

    /** Set document title (centered below header) */
    public function setTitle(string $title): self {
        $this->drawHeader();
        $this->ensureSpace(50);
        $cx = $this->pageW / 2;
        $this->text(strtoupper($title), 16, true, $cx - (strlen($title) * 4.2), $this->y);
        $this->y -= 18;
        // Accent line under title
        $lw = min(strlen($title) * 9 + 20, $this->pageW - $this->margin * 2);
        $lx = $cx - ($lw / 2);
        $this->line($lx, $this->y, $lx + $lw, $this->y);
        $this->y -= 12;
        return $this;
    }

    /** Set subtitle (academic year, date, etc.) */
    public function setSubtitle(string $text): self {
        $cx = $this->pageW / 2;
        $this->text($text, 10, false, $cx - (strlen($text) * 2.8), $this->y);
        $this->y -= 14;
        return $this;
    }

    /** Section header with accent lines */
    public function sectionHeader(string $text): void {
        $this->ensureSpace(28);
        $mh = $this->margin;
        $right = $this->pageW - $mh;
        $this->line($mh, $this->y + 2, $right, $this->y + 2);
        $this->text(strtoupper($text), 10, true, $mh, $this->y - 10);
        $this->y -= 22;
        $this->line($mh, $this->y + 6, $right, $this->y + 6);
        $this->y -= 6;
    }

    /** Key-value info block (2 columns) */
    public function infoBlock(array $pairs): void {
        $mh = $this->margin;
        $col2 = $this->pageW / 2 + 10;
        foreach ($pairs as $i => [$label, $value]) {
            $this->ensureSpace(14);
            if ($i % 2 === 0) {
                $this->text("$label:", 8.5, true, $mh, $this->y);
                $this->text((string)$value, 8.5, false, $mh + 80, $this->y);
            } else {
                $this->text("$label:", 8.5, true, $col2, $this->y);
                $this->text((string)$value, 8.5, false, $col2 + 80, $this->y);
            }
            $this->y -= 14;
        }
    }

    /** Plain text paragraph */
    public function paragraph(string $str, float $sz = 9.5): void {
        $this->ensureSpace($sz + 6);
        $this->text($str, $sz, false, $this->margin, $this->y);
        $this->y -= $sz + 5;
    }

    /** Bold text */
    public function bold(string $str, float $sz = 9.5): void {
        $this->ensureSpace($sz + 6);
        $this->text($str, $sz, true, $this->margin, $this->y);
        $this->y -= $sz + 5;
    }

    /** Horizontal rule */
    public function rule(): void {
        $this->ensureSpace(8);
        $this->line($this->margin, $this->y, $this->pageW - $this->margin, $this->y);
        $this->y -= 10;
    }

    /** Spacer */
    public function spacer(float $h = 8): void { $this->y -= $h; }

    /**
     * Official table with header row, alternating row shading, column alignment
     * @param array $aligns Optional per-column alignment: 'l' (left), 'c' (center), 'r' (right)
     */
    public function table(array $headers, array $rows, array $widths = [], array $aligns = []): void {
        $mh = $this->margin;
        $right = $this->pageW - $mh;
        $colCount = count($headers);
        if ($colCount === 0) return;
        $totalW = $right - $mh;
        $colW = $totalW / $colCount;
        $widths = $widths ?: array_fill(0, $colCount, $colW);
        $actualW = array_sum($widths);

        // Default alignment: left
        for ($i = count($aligns); $i < $colCount; $i++) { $aligns[$i] = 'l'; }

        $fontSize = 6.5;
        $headerSize = 6;

        // Header row
        $this->ensureSpace(20);
        $yy = $this->y;
        $this->out("0.88 0.88 0.88 rg");
        $this->rect($mh, $yy - 11, $actualW, 14, true);
        $this->out("0 0 0 rg");
        $x = $mh;
        for ($i = 0; $i < $colCount; $i++) {
            $h = (string)$headers[$i];
            $w = $widths[$i];
            if ($aligns[$i] === 'c') {
                $tx = $x + ($w - strlen($h) * $headerSize * 0.45) / 2;
            } elseif ($aligns[$i] === 'r') {
                $tx = $x + $w - strlen($h) * $headerSize * 0.45 - 2;
            } else {
                $tx = $x + 2;
            }
            $this->text(mb_substr($h, 0, 45), $headerSize, true, $tx, $yy);
            $x += $w;
        }
        $this->y -= 13;
        $this->line($mh, $this->y, $mh + $actualW, $this->y);
        $this->y -= 3;

        // Column separator lines
        $colLines = [];
        $cx = $mh;
        for ($i = 0; $i < $colCount - 1; $i++) {
            $cx += $widths[$i];
            $colLines[] = $cx;
        }

        // Data rows
        $rowNum = 0;
        foreach ($rows as $row) {
            $this->ensureSpace(12);
            $yy = $this->y;
            if ($rowNum % 2 === 1) {
                $this->out("0.96 0.96 0.97 rg");
                $this->rect($mh, $yy - 10, $actualW, 12, true);
                $this->out("0 0 0 rg");
            }
            $x = $mh;
            $cells = array_values($row);
            for ($i = 0; $i < $colCount; $i++) {
                $cell = $cells[$i] ?? '—';
                $w = $widths[$i];
                $cellStr = mb_substr((string)$cell, 0, 45);
                $tw = strlen($cellStr) * $fontSize * 0.45;
                if ($aligns[$i] === 'c') {
                    $tx = $x + ($w - $tw) / 2;
                } elseif ($aligns[$i] === 'r') {
                    $tx = $x + $w - $tw - 2;
                } else {
                    $tx = $x + 2;
                }
                $this->text($cellStr, $fontSize, false, $tx, $yy);
                $x += $w;
            }
            // Draw column separators (very light)
            $this->out("0.90 0.90 0.90 rg");
            foreach ($colLines as $lx) {
                $yBot = $yy - 10;
                $yTop = $yy + 1;
                $this->out("$lx $yBot m $lx $yTop l S");
            }
            $this->out("0 0 0 rg");
            $this->y -= 11;
            $rowNum++;
        }
        $this->line($mh, $this->y, $mh + $actualW, $this->y);
        $this->y -= 6;
    }

    /**
     * Summary stats block (key-value pairs in a box)
     */
    public function summaryBox(array $stats): void {
        $mh = $this->margin;
        $right = $this->pageW - $mh;
        $boxH = count($stats) * 16 + 10;
        $this->ensureSpace($boxH + 20);

        $boxTop = $this->y + 4;
        $boxBot = $boxTop - $boxH;
        $this->out("0.5 0.5 0.5 RG");
        $this->rect($mh, $boxBot, $right - $mh, $boxH, false);
        $this->out("0 0 0 RG");
        $this->y -= 2;
        foreach ($stats as [$label, $value]) {
            $this->text((string)$label, 9, true, $mh + 10, $this->y);
            $this->text((string)$value, 10, true, $right - 100, $this->y);
            $this->y -= 16;
        }
        $this->y -= 6;
    }

    /**
     * Bar chart (text-based, works in grayscale)
     */
    public function barChart(array $data, float $maxBarW = 300): void {
        $mh = $this->margin;
        $max = max(array_column($data, 1)) ?: 1;
        foreach ($data as [$label, $value, $extra]) {
            $this->ensureSpace(18);
            $barW = ($value / $max) * $maxBarW;
            $this->text(mb_substr($label, 0, 25), 8, false, $mh, $this->y);
            $barX = $mh + 140;
            $barY = $this->y - 1;
            $this->out("0.75 0.78 0.82 rg");
            $this->rect($barX, $barY - 8, $barW, 8, true);
            $this->out("0 0 0 rg");
            $valText = $extra ? "$value ($extra)" : (string)$value;
            $this->text($valText, 7.5, true, $barX + $barW + 6, $this->y);
            $this->y -= 16;
        }
        $this->y -= 4;
    }

    /** Draw watermark text on current page */
    public function watermark(string $text = 'EDUNEX', string $subtext = ''): void {
        $cx = $this->pageW / 2;
        $cy = $this->pageH / 2;
        // Large faded text
        $this->out("0.88 0.88 0.88 rg");
        $this->out("BT /Helvetica-Bold 48 Tf");
        $tw = strlen($text) * 16;
        $this->out(($cx - $tw/2) . " $cy Td (" . $this->esc($text) . ") Tj ET");
        if ($subtext) {
            $this->out("BT /Helvetica 10 Tf");
            $sw = strlen($subtext) * 3.2;
            $this->out(($cx - $sw/2) . " " . ($cy - 20) . " Td (" . $this->esc($subtext) . ") Tj ET");
        }
        $this->out("0 0 0 rg");
    }

    /**
     * Generate and output the PDF
     */
    public function output(string $filename = 'document.pdf', bool $inline = false, ?string $saveTo = null): void {
        $this->drawFooter();

        $totalPages = count($this->pages);
        foreach ($this->pages as &$page) {
            $page = str_replace('{N}', (string)$totalPages, $page);
        }
        unset($page);

        $hasImageWatermark = !empty($this->watermarkImage) && file_exists($this->watermarkImage);
        $hasHeaderImages = !empty($this->headerImages['flag']) || !empty($this->headerImages['ministry']);

        if ($hasImageWatermark || $hasHeaderImages) {
            $tmpBase = tempnam(sys_get_temp_dir(), 'edunex_base_') . '.pdf';
            $tmpFinal = tempnam(sys_get_temp_dir(), 'edunex_final_') . '.pdf';
            $this->writePdfFile($tmpBase);

            $script = __DIR__ . '/../scripts/add_watermark.py';

            if ($hasHeaderImages) {
                // Header mode: flag + ministry logo at top of each page
                $parts = ['python3', escapeshellarg($script), escapeshellarg($tmpBase), escapeshellarg($tmpFinal), '--header-left'];
                if (!empty($this->headerImages['flag']) && file_exists($this->headerImages['flag'])) {
                    $parts[] = escapeshellarg($this->headerImages['flag']);
                } else {
                    $parts[] = "''";
                }
                $parts[] = '--header-right';
                if (!empty($this->headerImages['ministry']) && file_exists($this->headerImages['ministry'])) {
                    $parts[] = escapeshellarg($this->headerImages['ministry']);
                } else {
                    $parts[] = "''";
                }
                // Optional center watermark
                if ($hasImageWatermark) {
                    $parts[] = '--center';
                    $parts[] = escapeshellarg($this->watermarkImage);
                    $parts[] = '--scale';
                    $parts[] = '30';
                }
                $cmd = implode(' ', $parts) . ' 2>&1';
            } else {
                // Legacy watermark mode
                $scale = 30;
                $cmd = sprintf('python3 %s %s %s %s %d 2>&1',
                    escapeshellarg($script),
                    escapeshellarg($tmpBase),
                    escapeshellarg($tmpFinal),
                    escapeshellarg($this->watermarkImage),
                    $scale
                );
            }

            exec($cmd, $output, $returnCode);

            @unlink($tmpBase);

            if ($returnCode === 0 && file_exists($tmpFinal) && filesize($tmpFinal) > 0) {
                if ($saveTo !== null) {
                    rename($tmpFinal, $saveTo);
                    return;
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
                readfile($tmpFinal);
                @unlink($tmpFinal);
                exit;
            }
            @unlink($tmpFinal);
        }

        if ($saveTo !== null) {
            $this->writePdfFile($saveTo);
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        $this->writePdfFile('php://output');
        exit;
    }

    public function getDocId(): string { return $this->docId; }

    /** Write PDF using fwrite() for binary-safe output */
    private function writePdfFile(string $dest): void {
        $fp = fopen($dest, 'wb');
        if (!$fp) {
            throw new \RuntimeException("Cannot open $dest for writing");
        }

        $offsets = [];
        fwrite($fp, "%PDF-1.4\n");

        // Obj 1: Catalog
        $offsets[1] = ftell($fp);
        fwrite($fp, "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n");

        // Obj 3-5: Fonts
        $offsets[3] = ftell($fp);
        fwrite($fp, "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n");
        $offsets[4] = ftell($fp);
        fwrite($fp, "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n");
        $offsets[5] = ftell($fp);
        fwrite($fp, "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >>\nendobj\n");

        // Page objects and content streams
        $nextObjId = 6;
        $pageStartObjId = $nextObjId;
        $contentStartObjId = $nextObjId + count($this->pages);
        $pageObjIds = [];
        for ($i = 0; $i < count($this->pages); $i++) {
            $pageObjIds[] = ($pageStartObjId + $i) . " 0 R";
        }
        $kids = implode(' ', $pageObjIds);
        $totalPages = count($this->pages);
        $pw = $this->pageW;
        $ph = $this->pageH;

        $offsets[2] = ftell($fp);
        fwrite($fp, "2 0 obj\n<< /Type /Pages /Kids [$kids] /Count $totalPages >>\nendobj\n");

        for ($i = 0; $i < $totalPages; $i++) {
            $pageObjId = $pageStartObjId + $i;
            $contentObjId = $contentStartObjId + $i;
            $offsets[$pageObjId] = ftell($fp);
            fwrite($fp, "{$pageObjId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pw $ph] /Resources << /Font << /Helvetica 3 0 R /Helvetica-Bold 4 0 R /Helvetica-Oblique 5 0 R >> /ProcSet [/PDF /Text] >> /Contents {$contentObjId} 0 R >>\nendobj\n");

            $pageContent = $this->pages[$i];
            $offsets[$contentObjId] = ftell($fp);
            fwrite($fp, "{$contentObjId} 0 obj\n<< /Length " . strlen($pageContent) . " >>\nstream\n");
            fwrite($fp, $pageContent);
            fwrite($fp, "\nendstream\nendobj\n");
        }

        // Cross-reference table
        $xrefOffset = ftell($fp);
        $totalObjs = count($offsets);
        fwrite($fp, "xref\n0 " . ($totalObjs + 1) . "\n0000000000 65535 f \n");
        for ($id = 1; $id <= $totalObjs; $id++) {
            fwrite($fp, sprintf("%010d 00000 n \n", $offsets[$id] ?? 0));
        }

        // Trailer
        fwrite($fp, "trailer\n<< /Size " . ($totalObjs + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF");

        fclose($fp);
    }
}
