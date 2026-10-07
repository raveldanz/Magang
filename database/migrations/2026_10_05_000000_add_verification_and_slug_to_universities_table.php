<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Fase 1 — Dynamic University Onboarding.
 *
 * - is_verified: kampus yang dibuat mandiri oleh mahasiswa (opsi "Perguruan Tinggi Lainnya")
 *   disimpan dengan is_verified = false sampai Super Admin memvalidasinya. Seluruh data
 *   eksisting (seeder / input admin) otomatis bernilai true lewat default kolom.
 * - slug: penanda URL-friendly dari nama kampus (nullable, di-backfill untuk data lama).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (! Schema::hasColumn('universities', 'is_verified')) {
                $table->boolean('is_verified')->default(true)->after('code');
            }
            if (! Schema::hasColumn('universities', 'slug')) {
                $table->string('slug')->nullable()->after('name');
                $table->index('slug');
            }
        });

        // Data lama dianggap terverifikasi (berasal dari seeder / input admin)
        DB::table('universities')->whereNull('is_verified')->update(['is_verified' => true]);

        // Backfill slug untuk data eksisting
        DB::table('universities')
            ->select(['id', 'name'])
            ->whereNull('slug')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('universities')
                        ->where('id', $row->id)
                        ->update(['slug' => Str::slug((string) $row->name) ?: null]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'slug')) {
                $table->dropIndex(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('universities', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
        });
    }
};
