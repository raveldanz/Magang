<?php
// Load original high-res logo
$src = imagecreatefrompng(__DIR__ . '/../public/images/logos/surabaya.png');
$srcW = imagesx($src);
$srcH = imagesy($src);

echo "Source dimensions: {$srcW}x{$srcH}\n";

// Target square sizes
$sizes = [16, 32, 48, 64, 128, 192, 256, 512];

foreach ($sizes as $size) {
    // Canvas is size x size, transparent
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
    imagealphablending($canvas, true);

    // Leave a tiny 2% padding so edges don't touch tab boundaries
    $padding = (int) round($size * 0.04);
    $availH = $size - ($padding * 2);
    $availW = $size - ($padding * 2);

    // Shield is taller than wide (960x1234), so scale by height
    $targetH = $availH;
    $targetW = (int) round($targetH * ($srcW / $srcH));

    if ($targetW > $availW) {
        $targetW = $availW;
        $targetH = (int) round($targetW * ($srcH / $srcW));
    }

    $offsetX = (int) round(($size - $targetW) / 2);
    $offsetY = (int) round(($size - $targetH) / 2);

    imagecopyresampled($canvas, $src, $offsetX, $offsetY, 0, 0, $targetW, $targetH, $srcW, $srcH);

    imagepng($canvas, __DIR__ . "/../public/images/favicon-{$size}.png");
    
    if ($size === 512) {
        imagepng($canvas, __DIR__ . '/../public/images/favicon.png');
        imagepng($canvas, __DIR__ . '/../public/favicon.png');
    }
    if ($size === 32) {
        imagepng($canvas, __DIR__ . '/../public/favicon-32x32.png');
    }
    if ($size === 16) {
        imagepng($canvas, __DIR__ . '/../public/favicon-16x16.png');
    }
}

echo "Square favicons generated successfully!\n";
