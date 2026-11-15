<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Selaraskan CHECK constraint PostgreSQL pada tabel logbooks dan final_reports
     * agar keduanya mendukung seluruh nilai App\Enums\ReviewStatus:
     * ('pending', 'approved', 'rejected', 'revision'), serta pastikan users_role_check
     * yang lama tidak memblokir role baru pada PostgreSQL.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (Schema::hasTable('users')) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }

        if (Schema::hasTable('logbooks')) {
            DB::statement('ALTER TABLE logbooks DROP CONSTRAINT IF EXISTS logbooks_status_check');
            DB::statement(
                "ALTER TABLE logbooks ADD CONSTRAINT logbooks_status_check CHECK ((status)::text = ANY (ARRAY['pending'::text, 'approved'::text, 'rejected'::text, 'revision'::text]))"
            );

            if (Schema::hasColumn('logbooks', 'lecturer_status')) {
                DB::statement('ALTER TABLE logbooks DROP CONSTRAINT IF EXISTS logbooks_lecturer_status_check');
                DB::statement(
                    "ALTER TABLE logbooks ADD CONSTRAINT logbooks_lecturer_status_check CHECK ((lecturer_status)::text = ANY (ARRAY['pending'::text, 'approved'::text, 'rejected'::text, 'revision'::text]))"
                );
            }
        }

        if (Schema::hasTable('final_reports')) {
            DB::statement('ALTER TABLE final_reports DROP CONSTRAINT IF EXISTS final_reports_status_check');
            DB::statement(
                "ALTER TABLE final_reports ADD CONSTRAINT final_reports_status_check CHECK ((status)::text = ANY (ARRAY['pending'::text, 'approved'::text, 'rejected'::text, 'revision'::text]))"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Biarkan constraint yang sudah diperluas tetap kompatibel
    }
};
