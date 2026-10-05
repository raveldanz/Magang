<?php
require __DIR__ . '/../vendor/autoload.php';
$a = require __DIR__ . '/../bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('role', 'mahasiswa')->latest()->first();
echo "CHECKING: " . ($u->name ?? '') . " | " . ($u->email ?? '') . "\n";
$app = App\Models\Application::where('user_id', $u->id)->first();
if (!$app) { echo "APP_NOT_FOUND\n"; exit; }

echo "STATUS: " . $app->status . "\n";
echo "LIFECYCLE: " . $app->lifecycle_status . "\n";
echo "IS_ACTIVE: " . ($app->is_active_internship ? 'YES' : 'NO') . "\n";
echo "PLACEMENT_ID: " . ($app->placement->id ?? 'NONE') . "\n";
echo "ADVISOR_ID: " . ($app->placement->academic_advisor_id ?? 'NONE') . "\n";
$lbs = App\Models\Logbook::where('placement_id', $app->placement->id ?? 0)->orderBy('date')->get();
echo "LOGBOOKS_COUNT: " . $lbs->count() . "\n";
foreach ($lbs as $l) {
    echo " - ID: " . $l->id . " | Tgl: " . $l->date . " | " . substr($l->activity, 0, 40) . "...\n";
}
