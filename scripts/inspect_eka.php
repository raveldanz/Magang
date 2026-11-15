<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\InternshipProject;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$users = User::where('role', 'student')->get();
foreach ($users as $u) {
    $placement = Placement::where('user_id', $u->id)->first();
    $projCount = $placement ? InternshipProject::where('placement_id', $placement->id)->count() : 0;
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Placement ID: ".($placement ? $placement->id : 'none')." | Projects: {$projCount}\n";
}
