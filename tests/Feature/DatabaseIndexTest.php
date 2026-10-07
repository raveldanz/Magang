<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Kolom foreign key yang dipakai join & urutan prioritas wajib ber-index
 * (PostgreSQL tidak membuatnya otomatis). Lihat migrasi 2026_09_30_030000.
 */
class DatabaseIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_foreign_key_columns_are_indexed(): void
    {
        $expected = [
            'applications' => [['user_id'], ['unit_id'], ['status']],
            'placements' => [['application_id'], ['mentor_id'], ['pembimbing_id'], ['academic_advisor_id']],
            'evaluations' => [['placement_id']],
            'final_reports' => [['placement_id']],
            'logbooks' => [['placement_id', 'status']],
            'users' => [['university_id'], ['agency_profile_id'], ['role']],
            'units' => [['agency_profile_id']],
            'student_profiles' => [['user_id'], ['university_id']],
        ];

        foreach ($expected as $table => $indexes) {
            foreach ($indexes as $columns) {
                $this->assertTrue(Schema::hasIndex($table, $columns), "Index {$table}(".implode(',', $columns).') belum ada');
            }
        }
    }
}
