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
        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'lecturer_status')) {
                $table->enum('lecturer_status', ['pending', 'approved', 'rejected'])->default('pending')->after('feedback');
            }
            if (!Schema::hasColumn('logbooks', 'lecturer_feedback')) {
                $table->text('lecturer_feedback')->nullable()->after('lecturer_status');
            }
            if (!Schema::hasColumn('logbooks', 'lecturer_verified_at')) {
                $table->timestamp('lecturer_verified_at')->nullable()->after('lecturer_feedback');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['lecturer_status', 'lecturer_feedback', 'lecturer_verified_at']);
        });
    }
};
