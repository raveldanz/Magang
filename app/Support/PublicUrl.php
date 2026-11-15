<?php

namespace App\Support;

/**
 * URL untuk tautan yang dibuka dari perangkat LAIN (QR verifikasi surat/sertifikat).
 *
 * Saat aplikasi dibuka lewat localhost/127.0.0.1 di laptop server, route() menghasilkan alamat
 * loopback yang tidak bisa dibuka dari HP pemindai QR. Jika APP_PUBLIC_URL diisi (mis. alamat
 * IP WiFi laptop, diisi otomatis oleh start_demo.bat), alamat loopback diganti dengan alamat itu.
 * Bila aplikasi sudah dibuka lewat alamat non-loopback, URL dibiarkan apa adanya.
 */
class PublicUrl
{
    private const LOOPBACK_HOSTS = ['localhost', '127.0.0.1', '::1', '[::1]'];

    public static function route(string $name, mixed $parameters = []): string
    {
        $url = route($name, $parameters);
        $publicBase = rtrim((string) config('app.public_url'), '/');

        if ($publicBase === '' || ! in_array(parse_url($url, PHP_URL_HOST), self::LOOPBACK_HOSTS, true)) {
            return $url;
        }

        return $publicBase.route($name, $parameters, false);
    }
}
