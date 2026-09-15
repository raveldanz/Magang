<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $targetEmails = [
        'mhs.unesa.desktop@unesa.ac.id',
        'mhs.unitomo.mobile@unitomo.ac.id',
    ];

    echo "=== RESET STATE AKUN TESTING MAHASISWA (FRESH) ===" . PHP_EOL;

    foreach ($targetEmails as $email) {
        $user = User::where('email', $email)->first();
        if (!$user) {
            echo "User {$email} tidak ditemukan!\n";
            continue;
        }

        $appCount = $user->applications()->count();
        foreach ($user->applications as $application) {
            if ($application->placement) {
                $pId = $application->placement->id;
                Evaluation::where('placement_id', $pId)->delete();
                FinalReport::where('placement_id', $pId)->delete();
                Logbook::where('placement_id', $pId)->delete();
                $application->placement->delete();
            }
            $application->delete();
        }

        echo "Berhasil mereset {$email}: {$appCount} lamaran/placement dihapus bersih. Status sekarang: FRESH (0 Lamaran Aktif).\n";
    }
});
