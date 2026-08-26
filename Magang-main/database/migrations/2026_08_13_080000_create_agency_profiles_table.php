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
            $table->string('agency_name')->default('Dinas Komunikasi Dan Informatika');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('signee_name')->default('Drs. H. M. NASER, M.Si');
            $table->string('signee_nip')->default('19700101 199503 1 002');
            $table->string('signee_position')->default('Kepala Dinas Komunikasi dan Informatika');
            $table->string('city')->default('Surabaya');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_profiles');
    }
};
