<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\SystemNotification;
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

        $this->activateStartedInternships($today);
        $this->handleEndedInternships();

        return Command::SUCCESS;
    }

    /**
     * ACCEPTED → ACTIVE saat tanggal mulai magang tiba (dan belum lewat end_date).
     */
    private function activateStartedInternships(string $today): void
    {
        $applications = Application::with('user.studentProfile', 'unit')
            ->where('status', ApplicationStatus::ACCEPTED)
            ->whereNotNull('start_date')
            ->whereDate('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>', $today);
            })
            ->get();

        if ($applications->isEmpty()) {
            $this->info('Tidak ada mahasiswa berstatus ACCEPTED yang siap diaktifkan hari ini.');

            return;
        }

        $this->info("Ditemukan {$applications->count()} mahasiswa yang siap bertransisi ke status ACTIVE.");

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
    }

    /**
     * Magang ACTIVE yang tanggal selesainya sudah lewat:
     *  - bila syarat administratif (laporan ACC & nilai lengkap) SUDAH LENGKAP → transisi COMPLETED;
     *  - bila belum → tetap ACTIVE, beri status/flag peringatan "Menunggu Evaluasi Mentor/Kelulusan".
     */
    private function handleEndedInternships(): void
    {
        $today = Carbon::today()->toDateString();
        $applications = Application::with(['user', 'placement.finalreport', 'placement.evaluation', 'placement.mentor', 'placement.pembimbing'])
            ->where('status', ApplicationStatus::ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        $completed = 0;
        $reminded = 0;

        foreach ($applications as $app) {
            $isComplete = $app->canBeCompleted()
                || ($app->placement && $app->placement->syncCompletionStatus() && $app->fresh()->statusValue() === ApplicationStatus::COMPLETED->value);

            if ($isComplete) {
                if ($app->statusValue() !== ApplicationStatus::COMPLETED->value) {
                    $app->update(['status' => ApplicationStatus::COMPLETED]);
                    AuditLog::record('AUTO_COMPLETE_INTERNSHIP', 'Application', $app->id, [
                        'student_name' => $app->user?->name,
                        'reason' => 'Masa magang berakhir dan syarat administratif lengkap (Auto-synced via scheduler)',
                    ]);
                }
                $completed++;
                $this->line(" - {$app->user?->name} (ID: {$app->id}) -> masa magang berakhir & syarat lengkap: COMPLETED.");

                continue;
            }

            // Jika syarat administratif belum lengkap: status tetap ACTIVE, beri flag peringatan "Menunggu Evaluasi Mentor/Kelulusan"
            $daysPast = $app->daysPastEndDate();
            $this->line(" - Mahasiswa: {$app->user?->name} (ID: {$app->id}) -> Lewat {$daysPast} hari [Menunggu Evaluasi Mentor/Kelulusan]. Status tetap ACTIVE.");

            if ($daysPast < 1 || ($daysPast - 1) % 7 !== 0) {
                continue;
            }

            $missing = [];
            if (! $app->has_approved_report) {
                $missing[] = 'laporan akhir belum disetujui';
            }
            if (! $app->has_complete_evaluation) {
                $missing[] = 'nilai evaluasi mentor lapangan belum lengkap';
            }
            if (! $app->has_filled_logbook) {
                $missing[] = 'logbook aktivitas belum diisi';
            }
            $missingText = $missing ? implode(' dan ', $missing) : 'kelengkapan kelulusan belum terpenuhi';

            SystemNotification::send(
                'Masa Magang Telah Berakhir',
                "Masa magang Anda telah berakhir {$daysPast} hari lalu [Menunggu Evaluasi Mentor/Kelulusan], karena {$missingText}. Segera lengkapi agar status kelulusan & sertifikat dapat diterbitkan.",
                $app->user_id,
                null,
                route('student.final_report.index', [], false),
                'Lengkapi Sekarang',
                'warning',
                'application',
                '⏰'
            );

            $mentor = $app->placement?->mentor ?? $app->placement?->pembimbing;
            if ($mentor) {
                SystemNotification::send(
                    'Mahasiswa Melewati Masa Magang',
                    "Masa magang {$app->user?->name} berakhir {$daysPast} hari lalu [Menunggu Evaluasi Mentor/Kelulusan], namun {$missingText}. Mohon tinjau laporan akhir & lengkapi evaluasi mentor lapangan.",
                    $mentor->id,
                    null,
                    route('mentor.dashboard', [], false),
                    'Buka Dasbor',
                    'warning',
                    'application',
                    '⏰'
                );
            }

            $reminded++;
        }

        $this->info("Masa magang berakhir: {$completed} diluluskan otomatis, {$reminded} pengingat dikirim.");
    }
}
