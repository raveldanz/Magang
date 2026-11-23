<?php

use App\Models\AgencyProfile;
use App\Services\MasterUniversityService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbarui alamat kantor pusat Dispusip Surabaya ke Gedung Siola Lantai 4
        AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')
            ->orWhere('acronym', 'Dispusip')
            ->update([
                'address' => 'Jl. Tunjungan No. 1-3 (Gedung Siola) Lantai 4, Surabaya 60275',
                'phone' => '(031) 8707329',
            ]);

        // 2. Sinkronkan seluruh master 251+ perguruan tinggi se-Indonesia ke database (hanya di luar mode pengujian)
        if (! app()->environment('testing')) {
            try {
                app(MasterUniversityService::class)->syncColleges();
            } catch (Throwable $e) {
                // Toleran jika file dataset belum terbaca di environment tertentu
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu revert master kampus
    }
};
