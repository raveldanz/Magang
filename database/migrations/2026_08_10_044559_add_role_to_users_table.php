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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan baris ini untuk membuat kolom role
            $table->enum('role', ['mahasiswa', 'admin', 'unit', 'pembimbing', 'mentor'])
                  ->default('mahasiswa')
                  ->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan baris ini untuk menghapus kolom jika migration di-rollback
            $table->dropColumn('role');
        });
    }
};