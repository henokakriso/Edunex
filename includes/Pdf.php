<?php
/**
 * EDUNEX Minimal PDF writer (pure PHP, no dependencies)
 * Supports: title, headings, paragraphs, tables, lines, images (GD PNG)
 */
class Pdf {
    private string $content = '';
    private array $objects = [];
    private int $objectId = 0;
    private float $y;
    private float $margin = 45;
    private float $maxY = 760;
    private int $pageCount = 1;
    private array $fontIds = ['F1' => 4, 'F2' => 5];
    private array $pageRefs = [3];
    private int $contentObj = 6;
    private float $pageW = 612;
    private float $pageH = 792;

    public function __construct(string $orientation = 'portrait', string $pageSize = 'letter') {
        $w = $h = 612;
        if ($pageSize === 'A4') { $w = 595.28; $h = 841.89; }
        if ($orientation === 'landscape') [$w, $h] = [$h, $w];
        $this->pageW = $w;
        $this->pageH = $h;
        $this->maxY = $h - $this->margin - 24;
        $this->y = $this->maxY;
        $this->objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $this->objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $this->objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $w $h] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> /ProcSet [/PDF /Text /ImageB /ImageC] >> /Contents 6 0 R >>";
        $this->objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $this->objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $this->objects[6] = ""; // page-1 content stream placeholder
    }

    private function esc(string $s): string {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function addText(string $text, float $size, string $font, float $x, float $y): void {
        $this->content .= "BT /$font $size Tf $x $y Td (" . $this->esc($text) . ") Tj ET\n";
    }

    private function ensureSpace(float $needed): void {
        if ($this->y - $needed < $this->margin) $this->newPage();
    }

    public function newPage(): void {
        $this->contentObj = count($this->objects) + 2;
        $this->pageRefs[] = count($this->objects) + 1;
        $this->objects[count($this->objects) + 1] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->pageW} {$this->pageH}] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> /ProcSet [/PDF /Text /ImageB /ImageC] >> /Contents " . $this->contentObj . " 0 R >>";
        $this->objects[$this->contentObj] = "";
        $kids = implode(' ', array_map(fn($r) => "$r 0 R", $this->pageRefs));
        $this->objects[2] = "<< /Type /Pages /Kids [$kids] /Count " . count($this->pageRefs) . " >>";
        $this->pageCount++;
        $this->y = $this->maxY;
    }

    public function text(string $text, float $size = 11, string $style = 'normal'): void {
        $this->ensureSpace($size + 6);
        $this->addText($text, $size, $style === 'bold' ? 'F2' : 'F1', $this->margin, $this->y);
        $this->y -= $size + 6;
    }

    public function title(string $text): void { $this->text($text, 22, 'bold'); $this->y -= 4; }

    public function heading(string $text, int $lvl = 1): void {
        $sizes = [15, 12.5, 11];
        $this->text($text, $sizes[min($lvl, 3) - 1], 'bold');
        $this->y -= 3;
    }

    public function paragraph(string $text, float $size = 10.5): void { $this->text($text, $size); $this->y -= 3; }

    public function spacer(float $h = 8): void { $this->y -= $h; }

    public function line(): void {
        $this->ensureSpace(10);
        $this->content .= "$this->margin $this->y m " . (612 - $this->margin) . " $this->y l S\n";
        $this->y -= 12;
    }

    public function table(array $headers, array $rows, array $widths = []): void {
        $colW = count($headers) ? (612 - $this->margin * 2) / count($headers) : 120;
        foreach ($rows as $row) {
            $this->ensureSpace(16);
            $x = $this->margin;
            $yy = $this->y;
            if (count($headers)) {
                foreach ($headers as $i => $h) {
                    $this->addText(mb_substr((string)$h, 0, 50), 9.5, 'F2', $x + 3, $yy);
                    $x += $widths[$i] ?? $colW;
                }
                $this->content .= "$this->margin $yy m " . (612 - $this->margin) . " $yy l S\n";
                $headers = [];
                $this->y -= 15;
                continue;
            }
            foreach ($row as $i => $cell) {
                $this->addText(mb_substr((string)$cell, 0, 60), 9, 'F1', $x + 3, $yy);
                $x += $widths[$i] ?? $colW;
            }
            $this->content .= "$this->margin " . ($yy - 3) . " m " . (612 - $this->margin) . " " . ($yy - 3) . " l S\n";
            $this->y -= 15;
        }
        $this->y -= 5;
    }

    /** embed PNG (GD) */
    public function image(string $pngData, float $wPt = 100, float $hPt = 100): void {
        $img = imagecreatefromstring($pngData);
        if (!$img) return;
        $wpx = imagesx($img);
        $hpx = imagesy($img);
        if ($hPt > $this->y - $this->margin) { $this->newPage(); }
        $x = $this->margin;
        $y = $this->y - $hPt;
        ob_start();
        imagepng($img);
        $raw = ob_get_clean();
        imagedestroy($img);
        $comp = gzcompress($raw);
        $id = count($this->objects) + 1;
        $this->objects[$id] = "<< /Type /XObject /Subtype /Image /Width $wpx /Height $hpx /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /Length " . strlen($comp) . " >>\nstream\n" . $comp . "\nendstream";
        $this->content .= "q $wPt 0 0 $hPt $x $y cm /Im$id Do Q\n";
        $this->y -= $hPt + 6;
    }

    public function output(string $filename = 'document.pdf', bool $inline = false, ?string $saveTo = null): void {
        $this->objects[$this->contentObj] = "<< /Length " . strlen($this->content) . " >>\nstream\n" . $this->content . "\nendstream";
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        ksort($this->objects);
        foreach ($this->objects as $id => $obj) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$obj\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($this->objects) + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($this->objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size " . (count($this->objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        if ($saveTo !== null) {
            file_put_contents($saveTo, $pdf);
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }
}
