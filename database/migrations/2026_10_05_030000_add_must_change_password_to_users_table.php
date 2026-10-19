<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda akun yang masih memakai password bawaan ('password').
 * Tidak memaksa ganti password — hanya memunculkan peringatan permanen di setiap halaman
 * sampai pemilik akun menggantinya (lihat User::booted & layouts/app.blade.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
        });

        // Tandai akun lama yang hash-nya masih password bawaan
        DB::table('users')->select(['id', 'password'])->orderBy('id')->chunkById(100, function ($users) {
            $flagged = [];
            foreach ($users as $user) {
                try {
                    if ($user->password && Hash::check(User::DEFAULT_PASSWORD, $user->password)) {
                        $flagged[] = $user->id;
                    }
                } catch (Throwable $e) {
                    // hash format lain: abaikan
                }
            }
            if ($flagged) {
                DB::table('users')->whereIn('id', $flagged)->update(['must_change_password' => true]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
        });
    }
};
