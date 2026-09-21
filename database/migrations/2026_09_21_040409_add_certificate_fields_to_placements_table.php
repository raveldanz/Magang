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
        Schema::table('placements', function (Blueprint $table) {
            if (!Schema::hasColumn('placements', 'certificate_hash')) {
                $table->string('certificate_hash')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            if (Schema::hasColumn('placements', 'certificate_hash')) {
                $table->dropColumn('certificate_hash');
            }
        });
    }
};