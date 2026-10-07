<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Placement;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$dosens = User::where('role', 'dosen')->get();
echo "=== LIST DOSEN & MAHASISWA BIMBINGAN ===\n";
foreach ($dosens as $dosen) {
    $placements = Placement::with(['application.user.studentProfile', 'logbooks', 'finalreport', 'evaluation'])
        ->where('academic_advisor_id', $dosen->id)
        ->get();
    if ($placements->count() > 0) {
        echo "\nDosen: {$dosen->name} ({$dosen->email}) [ID: {$dosen->id}]\n";
        echo "Placements ({$placements->count()}):\n";
        foreach ($placements as $p) {
            $student = $p->application->user ?? null;
            $status = $p->application->status ?? 'none';
            $statusVal = $status instanceof BackedEnum ? $status->value : (string) $status;
            $lbCount = $p->logbooks->count();
            $pendingLb = $p->logbooks->where('lecturer_status', 'pending')->count();
            $approvedLb = $p->logbooks->where('lecturer_status', 'approved')->count();
            $repStatus = optional($p->finalreport)->status ?? 'none';
            $evalScore = optional($p->evaluation)->final_score ?? 'unassigned';
            $dosenScore = optional($p->evaluation)->nilai_dosen ?? 'none';
            echo "  * Placement #{$p->id} | Mhs: {$student->name} (App Status: {$statusVal}) | Logbook Total: {$lbCount} (Pending: {$pendingLb}, ACC: {$approvedLb}) | Laporan: {$repStatus} | Nilai Dosen: {$dosenScore} | Final: {$evalScore}\n";
        }
    }
}
