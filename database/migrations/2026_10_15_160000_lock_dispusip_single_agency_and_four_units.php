<?php

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengunci sistem secara total ke single-agency Dispusip dan tepat 4 Bidang Resmi Dispusip.
     */
    public function up(): void
    {
        // 1. Dapatkan atau Buat Master Dispusip
        $dispusipData = [
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'acronym' => 'Dispusip',
            'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
            'phone' => '(031) 8707682',
            'email' => 'dispusip@surabaya.go.id',
            'website' => 'https://dispusip.surabaya.go.id',
            'logo' => 'images/logos/dispusip.png',
            'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
            'signee_nip' => '19720315 199803 2 004',
            'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
            'city' => 'Surabaya',
        ];

        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::first();

        if ($dispusip) {
            $dispusip->update($dispusipData);
        } else {
            $dispusip = AgencyProfile::create($dispusipData);
        }

        // 2. Buat atau perbarui tepat 4 Bidang Resmi Dispusip
        $officialUnits = [
            'Bidang Sekretariat' => [
                'quota' => 10,
                'description' => 'Pengelolaan administrasi kepegawaian, keuangan, perlengkapan, tata usaha, serta koordinasi umum dinas.',
            ],
            'Bidang Pembinaan dan Pengelolaan Perpustakaan' => [
                'quota' => 15,
                'description' => 'Pengembangan sistem perpustakaan digital, kurasi koleksi literasi, layanan otomasi perpustakaan, dan pembinaan TBM.',
            ],
            'Bidang Pembinaan dan Layanan Kearsipan' => [
                'quota' => 10,
                'description' => 'Layanan peminjaman dan konsultasi arsip dinamis, pembinaan tata kelola arsip perangkat daerah, dan edukasi kearsipan.',
            ],
            'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan' => [
                'quota' => 10,
                'description' => 'Preservasi arsip statis, digitalisasi naskah kuno bersejarah, alih media dokumen berharga, dan restorasi arsip fisik.',
            ],
        ];

        $officialUnitIds = [];
        foreach ($officialUnits as $name => $attrs) {
            $unit = Unit::updateOrCreate(
                [
                    'agency_profile_id' => $dispusip->id,
                    'name' => $name,
                ],
                [
                    'quota' => $attrs['quota'],
                    'description' => $attrs['description'],
                ]
            );
            $officialUnitIds[$name] = $unit->id;
        }

        $fallbackUnitId = $officialUnitIds['Bidang Pembinaan dan Pengelolaan Perpustakaan'];

        // 3. Remap relasi sebelum menghapus unit lain
        if (Schema::hasColumn('applications', 'unit_id')) {
            Application::whereNotIn('unit_id', array_values($officialUnitIds))
                ->update(['unit_id' => $fallbackUnitId]);
        }

        if (Schema::hasColumn('users', 'unit_id')) {
            User::whereNotNull('unit_id')
                ->whereNotIn('unit_id', array_values($officialUnitIds))
                ->update(['unit_id' => $officialUnitIds['Bidang Sekretariat']]);
        }

        // 4. Remap relasi agency_profile_id ke Dispusip
        if (Schema::hasColumn('users', 'agency_profile_id')) {
            User::whereNotNull('agency_profile_id')
                ->where('agency_profile_id', '!=', $dispusip->id)
                ->update(['agency_profile_id' => $dispusip->id]);
        }

        if (Schema::hasTable('chat_conversations') && Schema::hasColumn('chat_conversations', 'agency_profile_id')) {
            DB::table('chat_conversations')
                ->whereNotNull('agency_profile_id')
                ->where('agency_profile_id', '!=', $dispusip->id)
                ->update(['agency_profile_id' => $dispusip->id]);
        }

        // 5. Hapus semua unit di luar 4 Bidang Resmi Dispusip
        Unit::whereNotIn('id', array_values($officialUnitIds))->delete();

        // 6. Hapus semua agency_profiles selain Dispusip
        AgencyProfile::where('id', '!=', $dispusip->id)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu me-rollback penghapusan data dinas luar
    }
};
