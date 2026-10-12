<?php
// Pack PNGs into a valid .ico file
$pngSizes = [16, 32, 48];
$pngData = [];
foreach ($pngSizes as $sz) {
    $pngData[$sz] = file_get_contents(__DIR__ . "/../public/images/favicon-{$sz}.png");
}

$numImages = count($pngSizes);
// ICO Header: 6 bytes
$icoHeader = pack('vvv', 0, 1, $numImages);

$dirEntries = '';
$offset = 6 + ($numImages * 16);
$imagesData = '';

foreach ($pngSizes as $sz) {
    $data = $pngData[$sz];
    $len = strlen($data);
    $w = ($sz >= 256) ? 0 : $sz;
    $h = ($sz >= 256) ? 0 : $sz;
    
    // Directory entry (16 bytes):
    // bWidth, bHeight, bColorCount, bReserved, wPlanes, wBitCount, dwBytesInRes, dwImageOffset
    $dirEntries .= pack('CCCCvvVV', $w, $h, 0, 0, 1, 32, $len, $offset);
    $imagesData .= $data;
    $offset += $len;
}

$icoContent = $icoHeader . $dirEntries . $imagesData;
file_put_contents(__DIR__ . '/../public/favicon.ico', $icoContent);

echo "favicon.ico created successfully (" . strlen($icoContent) . " bytes)\n";
