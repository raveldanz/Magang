<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluations', 'score_mastery')) {
                $table->decimal('score_mastery', 5, 2)->default(0)->after('nilai_akademik');
            }
            if (!Schema::hasColumn('evaluations', 'score_report')) {
                $table->decimal('score_report', 5, 2)->default(0)->after('score_mastery');
            }
            if (!Schema::hasColumn('evaluations', 'score_attitude')) {
                $table->decimal('score_attitude', 5, 2)->default(0)->after('score_report');
            }
            if (!Schema::hasColumn('evaluations', 'nilai_dosen')) {
                $table->decimal('nilai_dosen', 5, 2)->default(0)->after('score_attitude');
            }
            if (!Schema::hasColumn('evaluations', 'final_score')) {
                $table->decimal('final_score', 5, 2)->default(0)->after('nilai_dosen');
            }
            if (!Schema::hasColumn('evaluations', 'grade')) {
                $table->string('grade', 5)->nullable()->after('final_score');
            }
            if (!Schema::hasColumn('evaluations', 'feedback_dosen')) {
                $table->text('feedback_dosen')->nullable()->after('catatan_dosen');
            }
        });

        Schema::table('final_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('final_reports', 'title')) {
                $table->string('title')->nullable()->after('placement_id');
            }
            if (!Schema::hasColumn('final_reports', 'repository_url')) {
                $table->string('repository_url')->nullable()->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'score_mastery',
                'score_report',
                'score_attitude',
                'nilai_dosen',
                'final_score',
                'grade',
                'feedback_dosen',
            ]);
        });

        Schema::table('final_reports', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'repository_url',
            ]);
        });
    }
};
