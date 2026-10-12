<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PostgreSQL tidak membuat index otomatis untuk kolom foreign key. Tanpa index ini setiap join
 * (penempatan, nilai, laporan, logbook) dan subquery urutan prioritas (Application::actionPrioritySql)
 * membaca seluruh tabel. Idempoten: index yang sudah ada dilewati.
 */
return new class extends Migration
{
    private const INDEXES = [
        'applications' => [['user_id'], ['unit_id'], ['status']],
        'placements' => [['application_id'], ['mentor_id'], ['pembimbing_id'], ['academic_advisor_id']],
        'evaluations' => [['placement_id']],
        'final_reports' => [['placement_id']],
        'logbooks' => [['placement_id', 'status']],
        'users' => [['university_id'], ['agency_profile_id'], ['role']],
        'units' => [['agency_profile_id']],
        'student_profiles' => [['user_id'], ['university_id']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $columns) {
                if (! Schema::hasTable($table) || ! Schema::hasColumns($table, $columns) || Schema::hasIndex($table, $columns)) {
                    continue;
                }
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $columns) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $columns)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($columns));
                }
            }
        }
    }
};
