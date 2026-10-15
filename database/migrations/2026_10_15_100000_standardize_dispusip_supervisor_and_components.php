<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kolom unit_id pada tabel users untuk akun mentor & tim kerja
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'unit_id')) {
                $table->foreignId('unit_id')
                    ->nullable()
                    ->after('agency_profile_id')
                    ->constrained('units')
                    ->nullOnDelete();
            }
        });

        // 2. Dukungan role 'supervisor' pada users (PostgreSQL constraint guard)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        }

        // 3. Kolom approval_status, approved_by, approved_at, notes pada tabel certificates
        Schema::table('certificates', function (Blueprint $table) {
            if (! Schema::hasColumn('certificates', 'approval_status')) {
                $table->string('approval_status', 32)->default('waiting_approval')->after('certificate_number');
            }
            if (! Schema::hasColumn('certificates', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('certificates', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('certificates', 'notes')) {
                $table->text('notes')->nullable()->after('approved_at');
            }
        });

        // 4. Kolom custom_components pada tabel evaluations
        Schema::table('evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluations', 'custom_components')) {
                $table->json('custom_components')->nullable()->after('final_score');
            }
        });

        // 5. Standarisasi 4 Bidang Resmi Dispusip Kota Surabaya
        $dispusip = \App\Models\AgencyProfile::firstOrCreate(
            ['agency_name' => 'Dinas Perpustakaan dan Kearsipan'],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
                'phone' => '(031) 8707682',
                'email' => 'dispusip@surabaya.go.id',
                'website' => 'https://dispusip.surabaya.go.id',
                'logo' => 'images/logos/dispusip.png',
                'signee_name' => 'Ir. Mia Santi Dewi, M.Si',
                'signee_nip' => '19680812 199403 2 007',
                'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                'city' => 'Surabaya',
            ]
        );

        $officialUnits = [
            [
                'name' => 'Bidang Sekretariat',
                'description' => 'Pengelolaan administrasi umum, kepegawaian, keuangan, perlengkapan, dan tata kelola internal kedinasan.',
                'quota' => 10,
            ],
            [
                'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
                'description' => 'Pengembangan koleksi, otomasi perpustakaan digital, layanan pemustaka, dan pembinaan minat baca publik.',
                'quota' => 15,
            ],
            [
                'name' => 'Bidang Pembinaan dan Layanan Kearsipan',
                'description' => 'Pengelolaan arsip dinamis, bimbingan kearsipan perangkat daerah, dan layanan keterbukaan informasi kearsipan.',
                'quota' => 10,
            ],
            [
                'name' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan',
                'description' => 'Preservasi, digitalisasi arsip bersejarah, restorasi naskah kuno, dan penyelamatan memori kolektif daerah.',
                'quota' => 10,
            ],
        ];

        foreach ($officialUnits as $unitData) {
            \App\Models\Unit::updateOrCreate(
                [
                    'agency_profile_id' => $dispusip->id,
                    'name' => $unitData['name'],
                ],
                [
                    'description' => $unitData['description'],
                    'quota' => $unitData['quota'],
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('evaluations', 'custom_components')) {
                $table->dropColumn('custom_components');
            }
        });

        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('certificates', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('certificates', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('certificates', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });
    }
};
