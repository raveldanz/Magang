<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membersihkan grup percakapan bimbingan lama (1 mahasiswa + 1 dosen + 1 mentor per penempatan)
     * yang digantikan oleh arsitektur baru: 1 Grup Bimbingan per Mentor dan 1 Grup Bimbingan per DPL.
     */
    public function up(): void
    {
        if (Schema::hasTable('chat_conversations')) {
            DB::table('chat_conversations')->where('type', 'placement')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy data tidak perlu dipulihkan
    }
};
