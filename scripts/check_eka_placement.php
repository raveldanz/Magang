<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Application;
use App\Models\InternshipProject;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$eka = User::where('email', 'eka.aktif@unesa.ac.id')->first();
echo "Eka User ID: {$eka->id}\n";
$placement = Placement::where('user_id', $eka->id)->first();
if (! $placement) {
    echo "No direct placement with user_id, checking application...\n";
    $app = Application::where('user_id', $eka->id)->first();
    if ($app) {
        $placement = Placement::where('application_id', $app->id)->first();
    }
}
if ($placement) {
    echo "Placement ID: {$placement->id}\n";
    $projects = InternshipProject::where('placement_id', $placement->id)->get();
    echo 'Projects count: '.$projects->count()."\n";
    foreach ($projects as $p) {
        echo "- [{$p->id}] {$p->title}\n";
    }
} else {
    echo "No placement found for Eka!\n";
}
