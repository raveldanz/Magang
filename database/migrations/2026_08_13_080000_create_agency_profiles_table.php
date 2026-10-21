<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('government_name')->default('Pemerintah Kota Surabaya');
            $table->string('agency_name')->default('Dinas Perpustakaan dan Kearsipan');
            $table->string('acronym', 32)->default('Dispusip');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('signee_name')->default('Mia Santi Dewi, S.H., M.Si.');
            $table->string('signee_nip')->default('19720315 199803 2 004');
            $table->string('signee_position')->default('Kepala Dinas Perpustakaan dan Kearsipan');
            $table->string('city')->default('Surabaya');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_profiles');
    }
};
