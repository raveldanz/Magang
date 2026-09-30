<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Status keaktifan akun disederhanakan menjadi active / inactive.
 * Akun yang sebelumnya "on_leave" (cuti) menjadi inactive; admin mengaktifkannya kembali bila sudah bertugas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'status')) {
            DB::table('users')->where('status', 'on_leave')->update(['status' => 'inactive']);
        }
    }

    public function down(): void
    {
        // Tidak dapat dipulihkan: setelah digabung, akun cuti tidak lagi dibedakan dari akun nonaktif.
    }
};
