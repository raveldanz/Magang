<?php

/**
 * Blade View & Asset Integrity Inspector
 * Run via: php scripts/finish_check.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=======================================================" . PHP_EOL;
echo "  BLADE ASSET & ROUTE LINK FINISHING INSPECTOR" . PHP_EOL;
echo "=======================================================" . PHP_EOL;

$viewsDir = resource_path('views');
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$missingAssets = [];
$hardcodedUrls = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

        // 1. Check asset() calls for existing files
        if (preg_match_all('/asset\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*\)/i', $content, $matches)) {
            foreach ($matches[1] as $assetPath) {
                // Ignore dynamic variables or storage links
                if (str_contains($assetPath, '$') || str_starts_with($assetPath, 'storage/')) {
                    continue;
                }
                $fullPath = public_path($assetPath);
                if (!file_exists($fullPath)) {
                    $missingAssets[] = "File: {$relativePath} -> asset('{$assetPath}') not found in public/";
                }
            }
        }

        // 2. Scan hardcoded /admin or /student hrefs (prefer route() name)
        if (preg_match_all('/href=[\'"](\/(admin|student|mentor|lecturer|university)\/[^\'"]+)[\'"]/i', $content, $matches)) {
            foreach ($matches[1] as $url) {
                $hardcodedUrls[] = "File: {$relativePath} -> Hardcoded URL '{$url}' (Consider using route() name)";
            }
        }
    }
}

echo PHP_EOL . "--- 1. Missing Public Assets in asset() ---" . PHP_EOL;
if (empty($missingAssets)) {
    echo "[OK] All static asset('...') file paths exist in public/!" . PHP_EOL;
} else {
    foreach (array_unique($missingAssets) as $item) {
        echo "  [MISSING ASSET] {$item}" . PHP_EOL;
    }
}

echo PHP_EOL . "--- 2. Hardcoded Admin/Portal URLs (Recommendation) ---" . PHP_EOL;
if (empty($hardcodedUrls)) {
    echo "[OK] No raw hardcoded portal URLs found." . PHP_EOL;
} else {
    $limited = array_slice(array_unique($hardcodedUrls), 0, 10);
    foreach ($limited as $item) {
        echo "  [INFO] {$item}" . PHP_EOL;
    }
    if (count($hardcodedUrls) > 10) {
        echo "  ... and " . (count($hardcodedUrls) - 10) . " more." . PHP_EOL;
    }
}

echo PHP_EOL . "=======================================================" . PHP_EOL;
echo "   INSPECTION COMPLETE." . PHP_EOL;
echo "=======================================================" . PHP_EOL;
