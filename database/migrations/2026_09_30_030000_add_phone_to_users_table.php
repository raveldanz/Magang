<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor telepon/WhatsApp akun staf (mentor, DPL, admin dinas, admin kampus, Super Admin).
 * Mahasiswa tetap memakai student_profiles.phone. Ditampilkan di Info Kontak chat hanya kepada
 * pihak yang memiliki hubungan magang langsung (ChatContactDirectory::personalDetailsVisibleTo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
