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

    /** Draw footer on current page */
    private function drawFooter(): void {
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
     * Official table with header row, alternating row shading
     */
    public function table(array $headers, array $rows, array $widths = []): void {
        $mh = $this->margin;
        $right = $this->pageW - $mh;
        $colCount = count($headers);
        if ($colCount === 0) return;
        $totalW = $right - $mh;
        $colW = $totalW / $colCount;
        $widths = $widths ?: array_fill(0, $colCount, $colW);

        // Header row
        $this->ensureSpace(20);
        $yy = $this->y;
        $this->out("0.92 0.92 0.92 rg");
        $this->rect($mh, $yy - 12, $totalW, 15, true);
        $this->out("0 0 0 rg");
        $x = $mh;
        foreach ($headers as $i => $h) {
            $this->text(mb_substr((string)$h, 0, 45), 8, true, $x + 4, $yy);
            $x += $widths[$i];
        }
        $this->y -= 14;
        $this->line($mh, $this->y, $right, $this->y);
        $this->y -= 4;

        // Data rows
        $rowNum = 0;
        foreach ($rows as $row) {
            $this->ensureSpace(14);
            $yy = $this->y;
            if ($rowNum % 2 === 1) {
                $this->out("0.96 0.96 0.98 rg");
                $this->rect($mh, $yy - 11, $totalW, 14, true);
                $this->out("0 0 0 rg");
            }
            $x = $mh;
            $cells = array_values($row);
            foreach ($cells as $i => $cell) {
                if ($i >= $colCount) break;
                $this->text(mb_substr((string)($cell ?? '—'), 0, 50), 8, false, $x + 4, $yy);
                $x += $widths[$i];
            }
            $this->y -= 13;
            $rowNum++;
        }
        $this->line($mh, $this->y, $right, $this->y);
        $this->y -= 8;
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

    /**
     * Generate and output the PDF
     */
    public function output(string $filename = 'document.pdf', bool $inline = false, ?string $saveTo = null): void {
        // Draw footer on last page
        $this->drawFooter();

        // Replace page count placeholder
        $totalPages = count($this->pages);
        foreach ($this->pages as &$page) {
            $page = str_replace('{N}', (string)$totalPages, $page);
        }
        unset($page);

        // Build PDF objects
        $w = $this->pageW;
        $h = $this->pageH;
        $objs = [];

        // Obj 1: Catalog
        $objs[1] = "<< /Type /Catalog /Pages 2 0 R >>";

        // Obj 2: Pages (placeholder, updated after fonts)
        // Fonts go first: 3, 4, 5
        $objs[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objs[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $objs[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >>";

        // Page objects start at 6, content streams at 6+totalPages
        $pageObjIds = [];
        for ($i = 0; $i < $totalPages; $i++) {
            $pageObjIds[] = (6 + $i) . " 0 R";
        }
        $kids = implode(' ', $pageObjIds);
        $objs[2] = "<< /Type /Pages /Kids [$kids] /Count $totalPages >>";

        for ($i = 0; $i < $totalPages; $i++) {
            $pageObjId = 6 + $i;
            $contentObjId = 6 + $totalPages + $i;
            $objs[$pageObjId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $w $h] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> /ProcSet [/PDF /Text] >> /Contents $contentObjId 0 R >>";
            $objs[$contentObjId] = "<< /Length " . strlen($this->pages[$i]) . " >>\nstream\n" . $this->pages[$i] . "\nendstream";
        }

        // Sort objects
        ksort($objs);

        // Build PDF string
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $id => $obj) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$obj\nendobj\n";
        }

        // Cross-reference table
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objs); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }

        // Trailer
        $pdf .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

        if ($saveTo !== null) {
            file_put_contents($saveTo, $pdf);
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }

    public function getDocId(): string { return $this->docId; }
}
