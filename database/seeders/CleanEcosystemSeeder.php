<?php

namespace Database\Seeders;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Models\SystemNotification;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanEcosystemSeeder extends Seeder
{
    /**
     * Bersihkan akun sampah dan duplikat serta isolasi sistem secara murni ke Dinas Perpustakaan dan Kearsipan (Dispusip).
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command?->info('Memulai pembersihan akun sampah & duplikat non-Dispusip...');

            // 1. DAFTAR EMAIL SAMPAH, JUNK, DAN DUPLIKAT
            $emailsToDelete = [
                'bimoli@gmail.com',
                'evan@gmail.com',
                'budi@gmail.com',
                'testuniv@testuniv.ac.id',
                'sunarto@gmail.com',
                'universi@universi.ac.id',
                'eva@gmail.com',
                'dinkes@gmail.com',
                'admin.kominfo@surabaya.go.id',
                'admin.dispendukcapil@surabaya.go.id',
                'admin.dinkes@surabaya.go.id',
                'mentor.kominfo@surabaya.go.id',
                'mentor.dukcapil1@surabaya.go.id',
                'mhs.draft@unitomo.ac.id',
                'mhs.pending1@unesa.ac.id',
                'mhs.pending2@unair.ac.id',
                'mhs.accepted1@unitomo.ac.id',
                'mhs.accepted2@its.ac.id',
                'mhs.aktif1@unitomo.ac.id',
                'mhs.aktif2@unesa.ac.id',
                'mhs.aktif3@upnjatim.ac.id',
                'mhs.lulus1@unitomo.ac.id',
                'mhs.lulus2@unair.ac.id',
                'mahasiswa@gmail.com',
            ];

            // Dapatkan user target berdasarkan daftar email dan pola regex Hermes dimas.e2e.%
            $targetUsers = User::whereIn('email', $emailsToDelete)
                ->orWhere('email', 'like', 'dimas.e2e.%')
                ->get();

            $deletedUserCount = 0;
            foreach ($targetUsers as $u) {
                foreach ($u->applications as $app) {
                    if ($app->placement) {
                        Evaluation::where('placement_id', $app->placement->id)->delete();
                        FinalReport::where('placement_id', $app->placement->id)->delete();
                        Logbook::where('placement_id', $app->placement->id)->delete();
                        $app->placement->delete();
                    }
                    $app->delete();
                }

                Placement::where('mentor_id', $u->id)
                    ->orWhere('academic_advisor_id', $u->id)
                    ->orWhere('pembimbing_id', $u->id)
                    ->update(['mentor_id' => null, 'academic_advisor_id' => null, 'pembimbing_id' => null]);

                StudentProfile::where('user_id', $u->id)->delete();
                AuditLog::where('user_id', $u->id)->delete();
                SystemFeedback::where('user_id', $u->id)->orWhere('responded_by', $u->id)->delete();
                SystemNotification::where('user_id', $u->id)->delete();

                $u->delete();
                $deletedUserCount++;
            }
            $this->command?->info("Selesai membersihkan {$deletedUserCount} user sampah dan dependensinya.");

            // Bersihkan universitas palsu jika ada
            University::whereIn('id', [7, 9])->orWhere('name', 'like', '%Test%')->delete();
        });

        // 2. Jalankan seeder resmi Dispusip & Universitas mitra
        $this->call(SurabayaAndTopUniversitiesSeeder::class);
        $this->call(SurabayaAgenciesSeeder::class);
        $this->call(DemoE2ESeeder::class);

        $this->command?->info('Ekosistem Dispusip berhasil dipulihkan secara bersih dan terstandarisasi.');
    }
}
