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
        Schema::table('evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluations', 'dosen_aspects')) {
                $table->json('dosen_aspects')->nullable()->after('score_attitude');
            }
            if (! Schema::hasColumn('evaluations', 'mentor_aspects')) {
                $table->json('mentor_aspects')->nullable()->after('nilai_laporan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('evaluations', 'dosen_aspects')) {
                $table->dropColumn('dosen_aspects');
            }
            if (Schema::hasColumn('evaluations', 'mentor_aspects')) {
                $table->dropColumn('mentor_aspects');
            }
        });
    }
};
