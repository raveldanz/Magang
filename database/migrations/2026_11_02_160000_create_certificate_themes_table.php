<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_themes', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->default(2026)->index();
            $table->string('theme_name')->default('Tema Resmi Dispusip 2026 — Cyan & Orange');
            $table->string('primary_color', 20)->default('#00A3C4');
            $table->string('secondary_color', 20)->default('#F36F21');
            $table->string('accent_color', 20)->default('#D97706');
            $table->string('number_format_pattern')->default('400.14.5.4/{NUMBER}/436.7.17/{YEAR}');
            $table->string('signee_name')->default('Dra. NOVELIJA, SH');
            $table->string('signee_position')->default('Sekretaris');
            $table->string('signee_rank')->default('PEMBINA TK I');
            $table->string('signee_nip')->default('197111141992012001');
            $table->string('mandate_text')->default('a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya');
            $table->string('city')->default('Surabaya');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert initial default theme for 2026
        DB::table('certificate_themes')->insert([
            'year' => 2026,
            'theme_name' => 'Tema Resmi Dispusip 2026 — Cyan & Orange',
            'primary_color' => '#00A3C4',
            'secondary_color' => '#F36F21',
            'accent_color' => '#D97706',
            'number_format_pattern' => '400.14.5.4/{NUMBER}/436.7.17/{YEAR}',
            'signee_name' => 'Dra. NOVELIJA, SH',
            'signee_position' => 'Sekretaris',
            'signee_rank' => 'PEMBINA TK I',
            'signee_nip' => '197111141992012001',
            'mandate_text' => 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'city' => 'Surabaya',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_themes');
    }
};

