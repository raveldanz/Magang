<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Membuat akun Super Admin dengan password sementara acak (wajib diganti saat login pertama).
 * Pengganti aman untuk akun Super Admin bawaan seeder di production.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin {email : Email akun Super Admin} {--name=Super Admin : Nama tampilan}';

    protected $description = 'Buat akun Super Admin baru dengan password sementara acak';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $validator = Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email']);
        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return self::FAILURE;
        }

        $user = User::create([
            'name' => (string) $this->option('name'),
            'email' => $email,
            'password' => Str::random(40),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $plain = $user->issueTemporaryPassword();

        $this->info("Super Admin {$email} berhasil dibuat.");
        $this->line("Password sementara (hanya ditampilkan sekali, wajib diganti saat login pertama): {$plain}");

        return self::SUCCESS;
    }
}
