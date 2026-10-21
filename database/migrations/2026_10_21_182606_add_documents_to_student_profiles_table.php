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
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('cv_path')->nullable()->after('photo');
            $table->string('transcript_path')->nullable()->after('cv_path');
            $table->string('id_card_path')->nullable()->after('transcript_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['cv_path', 'transcript_path', 'id_card_path']);
        });
    }
};
