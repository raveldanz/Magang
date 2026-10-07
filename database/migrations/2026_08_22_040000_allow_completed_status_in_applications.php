<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop Postgres check constraint if exists and change column to string
        try {
            DB::statement('ALTER TABLE applications DROP CONSTRAINT IF EXISTS applications_status_check;');
            DB::statement('ALTER TABLE applications ALTER COLUMN status TYPE VARCHAR(50);');
        } catch (Exception $e) {
            // fallback
        }
    }

    public function down(): void
    {
        //
    }
};
