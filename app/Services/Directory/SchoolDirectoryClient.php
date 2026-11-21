<?php

namespace App\Services\Directory;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien direktori sekolah nasional (NPSN) dari API publik pihak ketiga.
 *
 * Sumber bukan layanan resmi Kemendikdasmen, jadi semua pemanggilan dibuat toleran gagal:
 * timeout pendek, hasil kosong saat error, dan data lokal tetap menjadi sumber utama.
 * Ganti base URL lewat SCHOOL_DIRECTORY_URL, nonaktifkan lewat SCHOOL_DIRECTORY_ENABLED=false.
 */
class SchoolDirectoryClient
{
    /** Bentuk sekolah yang relevan untuk magang (SMA, SMK, MA, dan SLB). */
    public const FORMS = ['SMA', 'SMK', 'MA', 'SMLB'];

    public function enabled(): bool
    {
        return (bool) config('services.school_directory.enabled', true)
            && (string) config('services.school_directory.base_url') !== '';
    }

    /**
     * Satu halaman sekolah per bentuk. Mengembalikan null bila API gagal (bukan sama dengan halaman kosong).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int}|null
     */
    public function page(string $form, int $page, int $perPage = 1000): ?array
    {
        $payload = $this->get('/sekolah/'.rawurlencode($form), ['page' => $page, 'perPage' => $perPage]);
        if ($payload === null) {
            return null;
        }

        return [
            'rows' => array_map(fn (array $row) => $this->normalizeRow($row), $payload['dataSekolah'] ?? []),
            'total' => (int) ($payload['total_data'] ?? 0),
        ];
    }

    /**
     * Pencarian langsung berdasarkan nama (dicache singkat agar tidak membebani API sumber).
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $keyword, int $limit = 15): array
    {
        $keyword = trim($keyword);
        if (! $this->enabled() || mb_strlen($keyword) < 3) {
            return [];
        }

        $key = 'school-directory:search:'.md5(mb_strtolower($keyword));
        $rows = Cache::get($key);

        if ($rows === null) {
            $payload = $this->get('/sekolah/s', ['sekolah' => $keyword, 'perPage' => 30]);
            if ($payload === null) {
                return []; // gagal: jangan di-cache agar percobaan berikutnya tetap ke API
            }
            $rows = array_map(fn (array $row) => $this->normalizeRow($row), $payload['dataSekolah'] ?? []);
            Cache::put($key, $rows, now()->addHours(6));
        }

        return array_slice($rows, 0, $limit);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{npsn: string, name: string, form: string, status: string, address: string, city: string, province: string}
     */
    public function normalizeRow(array $row): array
    {
        $status = strtoupper(trim((string) ($row['status'] ?? '')));

        return [
            'npsn' => trim((string) ($row['npsn'] ?? '')),
            'name' => trim((string) ($row['sekolah'] ?? '')),
            'form' => strtoupper(trim((string) ($row['bentuk'] ?? ''))),
            'status' => $status === 'N' ? 'NEGERI' : ($status === 'S' ? 'SWASTA' : $status),
            'address' => trim((string) ($row['alamat_jalan'] ?? '')),
            'city' => trim(preg_replace('/^(Kota|Kab\.?)\s+/i', '', (string) ($row['kabupaten_kota'] ?? ''))),
            'province' => trim(preg_replace('/^Prov\.\s*/i', '', (string) ($row['propinsi'] ?? ''))),
        ];
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>|null
     */
    protected function get(string $path, array $query): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $ca = config('services.school_directory.ca_bundle');
            $response = Http::acceptJson()
                ->when($ca, fn ($http) => $http->withOptions(['verify' => $ca]))
                ->timeout((int) config('services.school_directory.timeout', 15))
                ->retry(2, 500, throw: false)
                ->get(rtrim((string) config('services.school_directory.base_url'), '/').$path, $query);

            if (! $response->successful()) {
                Log::warning('Direktori sekolah: HTTP '.$response->status().' untuk '.$path);

                return null;
            }

            $json = $response->json();

            return is_array($json) && ($json['status'] ?? 'success') === 'success' ? $json : null;
        } catch (\Throwable $e) {
            Log::warning('Direktori sekolah tidak terjangkau: '.$e->getMessage());

            return null;
        }
    }
}
