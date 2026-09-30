<?php

namespace App\Providers;

use App\Enums\AccountStatus;
use App\Enums\FeedbackStatus;
use App\Models\SystemFeedback;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useTailwind();
        // Login API: 5 percobaan/menit per (email + IP) agar brute force satu akun tertahan,
        // tapi ratusan mahasiswa di satu jaringan kampus (IP sama) tetap bisa login bersamaan.
        // Batas longgar per IP (300/menit) hanya untuk menahan serangan massal.
        RateLimiter::for('api-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('email:' . $email . '|' . $request->ip()),
                Limit::perMinute(300)->by('ip:' . $request->ip()),
            ];
        });

        // Halaman verifikasi QR publik: token sudah acak 32 karakter (tidak bisa ditebak),
        // jadi batas ini hanya pengaman beban. Dibuat longgar karena banyak orang bisa
        // scan dari satu jaringan (Wi-Fi kampus / kantor) pada saat yang sama.
        RateLimiter::for('verify-qr', fn (Request $request) => Limit::perMinute(600)->by($request->ip()));

        // Kolom status akun & tiket feedback berjenis teks biasa (tanpa CHECK constraint di database):
        // tolak nilai di luar enum saat disimpan, agar nilai hantu seperti "on_leave"/"submitted" tidak bisa masuk.
        // Status pengajuan dijaga cast enum di model; status logbook & laporan dijaga CHECK constraint.
        // Closure sengaja tanpa nilai kembali: listener saving yang mengembalikan false membatalkan penyimpanan.
        User::saving(function (User $user): void {
            if ($user->isDirty('status')) {
                AccountStatus::assertValid($user->status);
            }
        });
        SystemFeedback::saving(function (SystemFeedback $feedback): void {
            if ($feedback->isDirty('status')) {
                FeedbackStatus::assertValid($feedback->status);
            }
        });
    }
}
