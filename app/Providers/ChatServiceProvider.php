<?php

namespace App\Providers;

use App\Models\Placement;
use App\Observers\PlacementChatObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Grup Bimbingan otomatis mengikuti perubahan mentor/DPL pada penempatan
        Placement::observe(PlacementChatObserver::class);

        $key = fn (Request $request) => 'chat:' . ($request->user()?->id ?: $request->ip());

        RateLimiter::for('chat-send', fn (Request $request) => Limit::perMinute((int) config('chat.rate_limits.send', 40))
            ->by($key($request))
            ->response(fn () => response()->json(['message' => 'Terlalu banyak aksi dalam waktu singkat. Tunggu sebentar lalu coba lagi.'], 429)));

        // Polling dari beberapa tab sekaligus (halaman chat 3 detik + badge navbar 15 detik)
        RateLimiter::for('chat-poll', fn (Request $request) => Limit::perMinute((int) config('chat.rate_limits.poll', 300))->by($key($request)));
    }
}
