<?php

namespace Tests\Unit;

use App\Models\SystemNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regresi demo WiFi: notifikasi yang dibuat dari laptop server (127.0.0.1:8000) tidak bisa dibuka
 * dari HP karena tautannya tersimpan lengkap dengan alamat laptop.
 */
class NotificationActionUrlTest extends TestCase
{
    #[DataProvider('tautan')]
    public function test_tautan_notifikasi_selalu_relatif(?string $tersimpan, ?string $diharapkan): void
    {
        $notif = new SystemNotification;
        $notif->setRawAttributes(['action_url' => $tersimpan]);

        $this->assertSame($diharapkan, $notif->action_url);
        $this->assertSame($diharapkan, $notif->toArray()['action_url']);
    }

    public static function tautan(): array
    {
        return [
            'loopback dengan port' => ['http://127.0.0.1:8000/supervisor/applications/4', '/supervisor/applications/4'],
            'alamat IP WiFi' => ['http://192.168.100.23:8080/admin/feedbacks/7', '/admin/feedbacks/7'],
            'dengan query dan jangkar' => ['https://magang.test/chat?c=3#pesan-9', '/chat?c=3#pesan-9'],
            'hanya host' => ['http://localhost:8000', '/'],
            'sudah relatif' => ['/notifications', '/notifications'],
            'kosong' => [null, null],
        ];
    }
}
