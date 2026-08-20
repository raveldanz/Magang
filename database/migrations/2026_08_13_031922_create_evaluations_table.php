<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained()->onDelete('cascade');
            $table->integer('nilai_disiplin')->default(0); // Penilaian Pembimbing Dinas
            $table->integer('nilai_kinerja')->default(0);  // Penilaian Pembimbing Dinas
            $table->integer('nilai_laporan')->default(0);  // Penilaian Pembimbing Dinas
            $table->integer('nilai_akademik')->default(0); // Penilaian Dosen Pembimbing Kampus (DPL)
            $table->text('catatan')->nullable();          // Catatan Pembimbing Dinas
            $table->text('catatan_dosen')->nullable();    // Catatan/Bimbingan Dosen Kampus
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
