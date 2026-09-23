<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Application;

$count = Application::where('status', 'verified')->update(['status' => 'pending']);

echo "Successfully reverted {$count} 'verified' applications to 'pending'.\n";
