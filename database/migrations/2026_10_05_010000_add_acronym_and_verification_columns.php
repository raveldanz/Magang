<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom akronim dan status/verifikasi untuk standarisasi pencarian data.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel universities
        Schema::table('universities', function (Blueprint $table) {
            if (! Schema::hasColumn('universities', 'acronym')) {
                $table->string('acronym')->nullable()->after('name');
            }
            if (! Schema::hasColumn('universities', 'status')) {
                $table->string('status')->default('active')->after('is_verified');
            }
            if (! Schema::hasColumn('universities', 'is_verified')) {
                $table->boolean('is_verified')->default(true);
            }
        });

        // Backfill acronym pada tabel universities dari kolom code
        DB::table('universities')
            ->whereNull('acronym')
            ->whereNotNull('code')
            ->update([
                'acronym' => DB::raw('"code"'),
            ]);

        // 2. Modifikasi tabel agency_profiles
        Schema::table('agency_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('agency_profiles', 'acronym')) {
                $table->string('acronym')->nullable()->after('agency_name');
            }
        });

        // Backfill acronym pada agency_profiles berdasarkan nama dinas/badan
        $acronymMap = [
            'Komunikasi' => 'Diskominfo',
            'Perpustakaan' => 'Dispusip',
            'Kependudukan' => 'Dispendukcapil',
            'Pendidikan' => 'Dispendik',
            'Kesehatan' => 'Dinkes',
            'Sumber Daya Air' => 'DSDABM',
            'Perumahan Rakyat' => 'DPRKPP',
            'Lingkungan Hidup' => 'DLH',
            'Perhubungan' => 'Dishub',
            'Sosial' => 'Dinsos',
            'Tenaga Kerja' => 'Disnaker',
            'Pemberdayaan Perempuan' => 'DP3A-P2KB',
            'Ketahanan Pangan' => 'DKPP',
            'Koperasi' => 'Dinkopumdag',
            'Kebudayaan' => 'Disbudporapar',
            'Pemadam Kebakaran' => 'DPKP',
            'Satuan Polisi Pamong Praja' => 'Satpol PP',
            'Bappedalitbang' => 'Bappedalitbang',
            'Perencanaan Pembangunan' => 'Bappedalitbang',
            'Pengelolaan Keuangan' => 'BPKAD',
            'Pendapatan' => 'Bapenda',
            'Kepegawaian' => 'BKPSDM',
            'Penanggulangan Bencana' => 'BPBD',
            'Inspektorat' => 'Inspektorat',
        ];

        foreach ($acronymMap as $keyword => $acronym) {
            DB::table('agency_profiles')
                ->where('agency_name', 'like', "%{$keyword}%")
                ->whereNull('acronym')
                ->update(['acronym' => $acronym]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('agency_profiles', 'acronym')) {
                $table->dropColumn('acronym');
            }
        });

        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('universities', 'acronym')) {
                $table->dropColumn('acronym');
            }
        });
    }
};
