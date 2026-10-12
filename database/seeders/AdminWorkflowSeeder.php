<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminWorkflowSeeder extends Seeder
{
    /**
     * Jalankan seeder fixture untuk pengujian alur admin QA.
     * Kompatibel dengan PostgreSQL dan Laravel 11, beroperasi secara idempoten dan terisolasi.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing', 'development'])) {
            $this->command?->warn('AdminWorkflowSeeder dilewati karena bukan environment local/testing/development.');

            return;
        }

        DB::transaction(function (): void {
            $defaultPassword = Hash::make('password');

            // 1. Pastikan Instansi Dinas QA / Target Tersedia
            $agency = AgencyProfile::where('agency_name', 'like', '%Komunikasi%')->first()
                ?? AgencyProfile::first();
            if (! $agency) {
                $agency = AgencyProfile::create([
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'agency_name' => 'Dinas Komunikasi dan Informatika',
                    'address' => 'Jl. Jimerto No. 25-27, Surabaya',
                    'phone' => '(031) 5312144',
                    'email' => 'diskominfo@surabaya.go.id',
                    'website' => 'https://diskominfo.surabaya.go.id',
                    'signee_name' => 'Drs. H. M. NASER, M.Si',
                    'signee_nip' => '19700101 199503 1 002',
                    'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                    'city' => 'Surabaya',
                ]);
            }

            // 2. Pastikan Unit Kerja Penempatan QA Tersedia
            $unit = Unit::firstOrCreate(
                [
                    'agency_profile_id' => $agency->id,
                    'name' => 'Bidang Tata Kelola & Aplikasi Informatika QA',
                ],
                [
                    'description' => 'Unit kerja khusus pengujian otomatisasi alur seleksi dan verifikasi admin QA.',
                    'quota' => 15,
                ]
            );

            // 3. Pastikan Universitas QA Tersedia
            $university = University::firstOrCreate(
                ['code' => 'UNIVQA'],
                [
                    'name' => 'Universitas QA Indonesia',
                    'email' => 'humas@univqa.ac.id',
                    'logo' => 'images/logos/unesa.png',
                ]
            );

            // 4. Buat Akun Admin QA (Role: admin, email: admin.qa@test.local)
            $adminQa = User::updateOrCreate(
                ['email' => 'admin.qa@test.local'],
                [
                    'name' => 'Administrator QA Testing',
                    'password' => $defaultPassword,
                    'role' => 'admin',
                    'status' => 'active',
                    'agency_profile_id' => $agency->id,
                    'email_verified_at' => now(),
                ]
            );

            // 5. Data Pengajuan 1: Status PENDING (Target pengujian aksi verifikasi & persetujuan admin)
            $mhsPending = User::updateOrCreate(
                ['email' => 'mhs.qa.pending@test.local'],
                [
                    'name' => 'Ahmad Pending QA',
                    'password' => $defaultPassword,
                    'role' => 'mahasiswa',
                    'status' => 'active',
                    'university_id' => $university->id,
                    'university' => $university->name,
                    'email_verified_at' => now(),
                ]
            );

            StudentProfile::updateOrCreate(
                ['user_id' => $mhsPending->id],
                [
                    'nim' => '22051290001',
                    'universitas' => $university->name,
                    'university_id' => $university->id,
                    'faculty' => 'Fakultas Ilmu Komputer',
                    'fakultas' => 'Fakultas Ilmu Komputer',
                    'jurusan' => 'Teknik Informatika',
                    'major' => 'Teknik Informatika',
                    'semester' => '6',
                    'phone' => '081299990001',
                    'alamat' => 'Jl. Ketabang Kali No. 10, Surabaya',
                    'address' => 'Jl. Ketabang Kali No. 10, Surabaya',
                    'emergency_contact_name' => 'Keluarga Ahmad',
                    'emergency_contact_phone' => '081299990010',
                ]
            );

            $appPending = Application::updateOrCreate(
                ['user_id' => $mhsPending->id],
                [
                    'unit_id' => $unit->id,
                    'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(3)->addDays(7)->format('Y-m-d'),
                    'status' => ApplicationStatus::PENDING->value,
                    'rejection_note' => null,
                    'proposal_letter_path' => 'documents/applications/sample_proposal.pdf',
                    'cv_path' => 'documents/applications/sample_cv.pdf',
                    'transcript_path' => 'documents/applications/sample_transcript.pdf',
                    'id_card_path' => 'documents/applications/sample_ktp.pdf',
                ]
            );

            // 6. Data Pengajuan 2: Status APPROVED / ACCEPTED (Target pengujian status penerimaan & dokumen balasan)
            $mhsApproved = User::updateOrCreate(
                ['email' => 'mhs.qa.approved@test.local'],
                [
                    'name' => 'Budi Approved QA',
                    'password' => $defaultPassword,
                    'role' => 'mahasiswa',
                    'status' => 'active',
                    'university_id' => $university->id,
                    'university' => $university->name,
                    'email_verified_at' => now(),
                ]
            );

            StudentProfile::updateOrCreate(
                ['user_id' => $mhsApproved->id],
                [
                    'nim' => '22051290002',
                    'universitas' => $university->name,
                    'university_id' => $university->id,
                    'faculty' => 'Fakultas Ilmu Komputer',
                    'fakultas' => 'Fakultas Ilmu Komputer',
                    'jurusan' => 'Sistem Informasi',
                    'major' => 'Sistem Informasi',
                    'semester' => '6',
                    'phone' => '081299990002',
                    'alamat' => 'Jl. Pemuda No. 45, Surabaya',
                    'address' => 'Jl. Pemuda No. 45, Surabaya',
                    'emergency_contact_name' => 'Keluarga Budi',
                    'emergency_contact_phone' => '081299990020',
                ]
            );

            // Catatan arsitektur: Domain status persetujuan resmi sistem menggunakan BackedEnum ApplicationStatus::ACCEPTED ('accepted')
            $appApproved = Application::updateOrCreate(
                ['user_id' => $mhsApproved->id],
                [
                    'unit_id' => $unit->id,
                    'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(3)->addDays(7)->format('Y-m-d'),
                    'status' => ApplicationStatus::ACCEPTED->value,
                    'letter_number' => '500.12.2/QA-APP-002/436.7.14/2026',
                    'letter_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                    'rejection_note' => null,
                    'proposal_letter_path' => 'documents/applications/sample_proposal.pdf',
                    'cv_path' => 'documents/applications/sample_cv.pdf',
                    'transcript_path' => 'documents/applications/sample_transcript.pdf',
                    'id_card_path' => 'documents/applications/sample_ktp.pdf',
                ]
            );
            $appApproved->ensureLetterToken();

            // Placement untuk pengajuan diterima
            Placement::updateOrCreate(
                ['application_id' => $appApproved->id],
                [
                    'mentor_id' => null,
                    'pembimbing_id' => null,
                    'academic_advisor_id' => null,
                ]
            );

            // 7. Data Pengajuan 3: Status REJECTED (Target pengujian penolakan dan feedback alasan)
            $mhsRejected = User::updateOrCreate(
                ['email' => 'mhs.qa.rejected@test.local'],
                [
                    'name' => 'Citra Rejected QA',
                    'password' => $defaultPassword,
                    'role' => 'mahasiswa',
                    'status' => 'active',
                    'university_id' => $university->id,
                    'university' => $university->name,
                    'email_verified_at' => now(),
                ]
            );

            StudentProfile::updateOrCreate(
                ['user_id' => $mhsRejected->id],
                [
                    'nim' => '22051290003',
                    'universitas' => $university->name,
                    'university_id' => $university->id,
                    'faculty' => 'Fakultas Teknologi Rekayasa',
                    'fakultas' => 'Fakultas Teknologi Rekayasa',
                    'jurusan' => 'Teknik Telekomunikasi',
                    'major' => 'Teknik Telekomunikasi',
                    'semester' => '6',
                    'phone' => '081299990003',
                    'alamat' => 'Jl. Darmo No. 88, Surabaya',
                    'address' => 'Jl. Darmo No. 88, Surabaya',
                    'emergency_contact_name' => 'Keluarga Citra',
                    'emergency_contact_phone' => '081299990030',
                ]
            );

            $appRejected = Application::updateOrCreate(
                ['user_id' => $mhsRejected->id],
                [
                    'unit_id' => $unit->id,
                    'start_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(3)->addDays(14)->format('Y-m-d'),
                    'status' => ApplicationStatus::REJECTED->value,
                    'rejection_note' => 'Kualifikasi portofolio teknis dan kapasitas kuota unit kerja belum sesuai untuk periode ini.',
                    'proposal_letter_path' => 'documents/applications/sample_proposal.pdf',
                    'cv_path' => 'documents/applications/sample_cv.pdf',
                    'transcript_path' => 'documents/applications/sample_transcript.pdf',
                    'id_card_path' => 'documents/applications/sample_ktp.pdf',
                ]
            );
        });

        $this->command?->info('AdminWorkflowSeeder berhasil dieksekusi: 1 Admin QA (admin.qa@test.local) & 3 dummy pengajuan magang (pending, accepted/approved, rejected).');
    }
}
