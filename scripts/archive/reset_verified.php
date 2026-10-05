<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Application;
use Illuminate\Contracts\Console\Kernel;

$count = Application::where('status', 'verified')->update(['status' => 'pending']);

echo "Successfully reverted {$count} 'verified' applications to 'pending'.\n";
