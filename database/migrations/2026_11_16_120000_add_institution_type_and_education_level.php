<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi 16 Nov 2026: peserta magang bisa berasal dari perguruan tinggi atau sekolah (SMK/SMA/MA).
 *  - universities.institution_type : perguruan_tinggi | sekolah (tabel universities menampung keduanya)
 *  - student_profiles.education_level : jenjang yang dipilih peserta di profil (perguruan_tinggi | sekolah)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('universities', 'institution_type')) {
            Schema::table('universities', function (Blueprint $table) {
                $table->string('institution_type', 30)->default('perguruan_tinggi');
            });
        }

        if (! Schema::hasColumn('student_profiles', 'education_level')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->string('education_level', 30)->default('perguruan_tinggi');
            });
        }

        // Data lama: institusi bernama SMK/SMA/MA/SLB dianggap sekolah, beserta pesertanya.
        $schoolIds = DB::table('universities')
            ->select(['id', 'name'])
            ->get()
            ->filter(fn ($row) => preg_match('/^\s*(SMKN?|SMAN?|SMKS|SMAS|MAN?|MAS|SLB)\b|sekolah menengah/i', (string) $row->name) === 1)
            ->pluck('id');

        if ($schoolIds->isNotEmpty()) {
            DB::table('universities')->whereIn('id', $schoolIds)->update(['institution_type' => 'sekolah']);
            DB::table('student_profiles')->whereIn('university_id', $schoolIds)->update(['education_level' => 'sekolah']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_profiles', 'education_level')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->dropColumn('education_level');
            });
        }

        if (Schema::hasColumn('universities', 'institution_type')) {
            Schema::table('universities', function (Blueprint $table) {
                $table->dropColumn('institution_type');
            });
        }
    }
};
