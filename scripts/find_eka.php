<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();
foreach ($users as $u) {
    $roleStr = $u->role instanceof \BackedEnum ? $u->role->value : (string) $u->role;
    echo "Found Eka: ID {$u->id}, Name: {$u->name}, Role: {$roleStr}, Email: {$u->email}\n";
}
echo "Total users: " . $users->count() . "\n";
