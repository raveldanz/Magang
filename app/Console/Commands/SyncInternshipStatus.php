<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncInternshipStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-internship-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatisasi sinkronisasi status magang mahasiswa dari ACCEPTED ke ACTIVE saat tanggal mulai magang tiba';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        $this->info("Memulai sinkronisasi status magang untuk tanggal: {$today}");

        // Ambil pengajuan berstatus 'accepted' dengan start_date <= hari ini
        $applications = Application::with('user.studentProfile', 'unit')
            ->where('status', ApplicationStatus::ACCEPTED)
            ->whereNotNull('start_date')
            ->whereDate('start_date', '<=', $today)
            ->get();

        $count = $applications->count();
        if ($count === 0) {
            $this->info("Tidak ada mahasiswa berstatus ACCEPTED yang siap diaktifkan hari ini.");
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$count} mahasiswa yang siap bertransisi ke status ACTIVE.");

        $updatedCount = 0;
        foreach ($applications as $app) {
            DB::transaction(function () use ($app, &$updatedCount) {
                $app->update([
                    'status' => ApplicationStatus::ACTIVE,
                ]);

                AuditLog::record('AUTO_ACTIVATE_INTERNSHIP', 'Application', $app->id, [
                    'student_name' => $app->user?->name,
                    'nim' => $app->user?->studentProfile?->nim,
                    'unit_name' => $app->unit?->name,
                    'start_date' => $app->start_date,
                    'activated_at' => now()->toDateTimeString(),
                    'reason' => 'Tanggal mulai magang telah tiba (Auto-synced via scheduler)',
                ]);

                $updatedCount++;
            });

            $this->line(" - Mahasiswa: {$app->user?->name} (ID: {$app->id}) -> Status diubah ke ACTIVE.");
        }

        $this->info("Sukses mengaktifkan {$updatedCount} peserta magang.");
        return Command::SUCCESS;
    }
}
