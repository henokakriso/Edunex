#!/usr/bin/env php
<?php
/**
 * Generate composite watermark image: Edunex logo (center), flag (top-left), ministry logo (top-right)
 * All with transparent/faded effect on white background.
 */
$logoPath = __DIR__ . '/../public/images/logo-black.jpeg';
$flagPath = __DIR__ . '/../public/images/ethiopian-flag.jpeg';
$ministryPath = __DIR__ . '/../public/images/ministry-logo.png';
$outPath = '/tmp/watermark_composite.jpg';

if (!file_exists($logoPath)) {
    fwrite(STDERR, "Logo not found: $logoPath\n");
    exit(1);
}

$W = 900;
$H = 650;
$canvas = imagecreatetruecolor($W, $H);
$white = imagecolorallocate($canvas, 255, 255, 255);
imagefill($canvas, 0, 0, $white);

// Helper: merge with opacity
function mergeImage($canvas, $srcPath, $maxW, $maxH, $opacity, $x, $y) {
    if (!file_exists($srcPath)) return;
    $info = @getimagesize($srcPath);
    if (!$info) return;
    $src = @imagecreatefromjpeg($srcPath);
    if (!$src) $src = @imagecreatefrompng($srcPath);
    if (!$src) return;

    $sw = imagesx($src);
    $sh = imagesy($src);
    $scale = min($maxW / $sw, $maxH / $sh);
    $dw = (int)($sw * $scale);
    $dh = (int)($sh * $scale);

    // Create temp canvas for the scaled image
    $tmp = imagecreatetruecolor($dw, $dh);
    imagesavealpha($tmp, true);
    $trans = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
    imagefill($tmp, 0, 0, $trans);

    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $dw, $dh, $sw, $sh);

    // Apply opacity
    for ($py = 0; $py < $dh; $py++) {
        for ($px = 0; $px < $dw; $px++) {
            $rgb = imagecolorat($tmp, $px, $py);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $a = ($rgb >> 24) & 0x7F; // 0=opaque, 127=transparent

            // Skip fully transparent pixels
            if ($a >= 126) continue;

            // Calculate effective opacity
            $effectiveAlpha = (127 - $a) * $opacity / 100;
            if ($effectiveAlpha < 1) continue;

            // Blend with white background
            $blend = $effectiveAlpha / 127;
            $fr = (int)($r * $blend + 255 * (1 - $blend));
            $fg = (int)($g * $blend + 255 * (1 - $blend));
            $fb = (int)($b * $blend + 255 * (1 - $blend));

            $dstRgb = imagecolorallocate($canvas, $fr, $fg, $fb);
            imagesetpixel($canvas, $x + $px, $y + $py, $dstRgb);
        }
    }

    imagedestroy($tmp);
    imagedestroy($src);
}

// Flag (top-left, 25% opacity)
mergeImage($canvas, $flagPath, 110, 70, 25, 15, 15);

// Ministry logo (top-right, 25% opacity)
$minInfo = @getimagesize($ministryPath);
if ($minInfo) {
    $mw = (int)(110 * $minInfo[0] / max($minInfo[1], 1));
    mergeImage($canvas, $ministryPath, 110, 110, 25, $W - $mw - 15, 15);
}

// Edunex logo (center, 15% opacity)
$logoInfo = @getimagesize($logoPath);
if ($logoInfo) {
    $lw = (int)(380 * $logoInfo[0] / max($logoInfo[1], 1));
    $lh = (int)(380 * $logoInfo[1] / max($logoInfo[0], 1));
    mergeImage($canvas, $logoPath, 380, 220, 15, ($W - $lw) / 2, ($H - $lh) / 2);
}

imagejpeg($canvas, $outPath, 90);
imagedestroy($canvas);
echo "Watermark saved to $outPath\n";
