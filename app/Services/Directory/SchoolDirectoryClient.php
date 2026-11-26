<?php

namespace App\Services\Directory;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
     * @param  array<string, mixed>  $row
     * @return array{npsn: string, name: string, form: string, status: string, address: string, city: string, province: string}
     */
    public function normalizeRow(array $row): array
    {
        $status = strtoupper(trim((string) ($row['status'] ?? '')));

        $normalized = [
            'npsn' => trim((string) ($row['npsn'] ?? '')),
            'name' => trim((string) ($row['sekolah'] ?? '')),
            'form' => strtoupper(trim((string) ($row['bentuk'] ?? ''))),
            'status' => $status === 'N' ? 'NEGERI' : ($status === 'S' ? 'SWASTA' : $status),
            'address' => trim((string) ($row['alamat_jalan'] ?? '')),
            'city' => trim(preg_replace('/^(Kota|Kab\.?)\s+/i', '', (string) ($row['kabupaten_kota'] ?? ''))),
            'province' => trim(preg_replace('/^Prov\.\s*/i', '', (string) ($row['propinsi'] ?? ''))),
        ];
        static $corrections;
        $corrections ??= json_decode(file_get_contents(database_path('data/school_directory_corrections.json')), true, flags: JSON_THROW_ON_ERROR);
        $verified = $corrections[$normalized['npsn']] ?? null;
        if ($verified && $verified['old_name'] === $normalized['name']) {
            foreach (['name', 'address', 'city', 'province'] as $field) {
                $normalized[$field] = $verified[$field];
            }
        }

        return $normalized;
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
            // Path di .env yang dibawa dari komputer lain (zip demo) tidak ada di sini: pakai bundel bawaan proyek.
            if ($ca && ! is_file($ca)) {
                $ca = is_file(resource_path('certs/cacert.pem')) ? resource_path('certs/cacert.pem') : null;
            }
            $response = Http::acceptJson()
                ->when($ca, fn ($http) => $http->withOptions(['verify' => $ca]))
                ->timeout((int) config('services.school_directory.timeout', 15))
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException || ($e instanceof RequestException && $e->response->serverError()), throw: false)
                ->get(rtrim((string) config('services.school_directory.base_url'), '/').$path, $query);

            // API sumber membalas 404 untuk pencarian tanpa hasil: itu hasil kosong, bukan kegagalan.
            if ($response->status() === 404) {
                return ['status' => 'success', 'dataSekolah' => [], 'total_data' => 0];
            }

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
