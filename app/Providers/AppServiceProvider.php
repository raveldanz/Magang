<?php

namespace App\Providers;

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
    }
}
