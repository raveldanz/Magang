<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

foreach (User::whereIn('role', ['admin', 'mentor', 'supervisor'])->with('unit')->get() as $u) {
    echo "ID {$u->id} | {$u->role} | {$u->name} | {$u->email} | Unit: " . ($u->unit?->name ?? 'NULL') . " (unit_id: " . ($u->unit_id ?? 'null') . ")\n";
}
