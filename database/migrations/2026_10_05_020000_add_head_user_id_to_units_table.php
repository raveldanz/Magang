<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kepala / Koordinator Unit (opsional): akun mentor instansi yang menjadi penanggung jawab bidang.
 * Dipakai sebagai verifikator logbook SEMENTARA untuk mahasiswa di unit tersebut selama
 * mentor teknis (placements.mentor_id) belum ditunjuk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'head_user_id')) {
                $table->foreignId('head_user_id')->nullable()->after('agency_profile_id')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'head_user_id')) {
                $table->dropForeign(['head_user_id']);
                $table->dropColumn('head_user_id');
            }
        });
    }
};
