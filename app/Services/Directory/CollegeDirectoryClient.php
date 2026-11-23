<?php

namespace App\Services\Directory;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien direktori perguruan tinggi nasional/internasional (Hipo / Higher Education Directory).
 *
 * Dirancang dengan arsitektur toleran gagal (graceful degradation):
 * - Database lokal (PostgreSQL) dan master dataset (253+ kampus) tetap menjadi sumber data utama.
 * - API dipanggil saat pengguna mencari kampus yang belum ada di database lokal.
 * - Hasil API yang dipilih pengguna otomatis disimpan/direkam (upsert) ke tabel universities.
 * - Bila API eksternal timeout atau down, sistem otomatis melayani dari database lokal tanpa error.
 */
class CollegeDirectoryClient
{
    public function enabled(): bool
    {
        return (bool) config('services.college_directory.enabled', true)
            && (string) config('services.college_directory.base_url') !== '';
    }

    /**
     * Pencarian kampus berbasis API dengan cache memori.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $keyword, int $limit = 15): array
    {
        $keyword = trim($keyword);
        if (! $this->enabled() || mb_strlen($keyword) < 3) {
            return [];
        }

        $cacheKey = 'college-directory:search:'.md5(mb_strtolower($keyword));
        $rows = Cache::get($cacheKey);

        if ($rows === null) {
            $payload = $this->get('/search', [
                'country' => 'Indonesia',
                'name' => $keyword,
            ]);

            if ($payload === null) {
                return []; // Jika API gagal, jangan cache agar percobaan berikutnya bisa mencoba lagi
            }

            $rows = [];
            foreach ($payload as $item) {
                if (! is_array($item) || empty($item['name'])) {
                    continue;
                }
                $rows[] = $this->normalizeRow($item);
            }

            Cache::put($cacheKey, $rows, now()->addHours(6));
        }

        // Cache setiap item per key unik agar endpoint resolve hanya menyimpan data yang valid dari API
        foreach ($rows as $row) {
            if (! empty($row['key'])) {
                Cache::put(self::itemCacheKey($row['key']), $row, now()->addDay());
            }
        }

        return array_slice($rows, 0, $limit);
    }

    public static function itemCacheKey(string $key): string
    {
        return 'college-directory:item:'.$key;
    }

    /**
     * Ambil data kampus dari cache API berdasarkan key unik.
     *
     * @return array<string, mixed>|null
     */
    public function cached(string $key): ?array
    {
        $row = Cache::get(self::itemCacheKey($key));

        return is_array($row) ? $row : null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{key: string, name: string, acronym: string, country: string, state_province: ?string, domain: ?string, web_page: ?string}
     */
    public function normalizeRow(array $row): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        $key = 'col_'.substr(md5(mb_strtolower($name)), 0, 10);
        $domains = (array) ($row['domains'] ?? []);
        $webPages = (array) ($row['web_pages'] ?? []);

        return [
            'key' => $key,
            'name' => $name,
            'acronym' => $this->inferAcronym($name),
            'country' => trim((string) ($row['country'] ?? 'Indonesia')),
            'state_province' => isset($row['state-province']) && trim((string) $row['state-province']) !== ''
                ? trim((string) $row['state-province'])
                : null,
            'domain' => isset($domains[0]) ? trim((string) $domains[0]) : null,
            'web_page' => isset($webPages[0]) ? trim((string) $webPages[0]) : null,
        ];
    }

    /**
     * Buat akronim singkat dari nama kampus (mis. "Universitas Surabaya" -> "UBAYA" / "US").
     */
    protected function inferAcronym(string $name): string
    {
        $words = preg_split('/\s+/', $name);
        $letters = '';
        foreach ($words as $w) {
            $first = mb_substr($w, 0, 1);
            if (ctype_alnum($first)) {
                $letters .= strtoupper($first);
            }
        }

        return mb_substr($letters, 0, 6) ?: 'UNIV';
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<int, mixed>|null
     */
    protected function get(string $path, array $query): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $ca = config('services.college_directory.ca_bundle');
            if ($ca && ! is_file($ca)) {
                $ca = is_file(resource_path('certs/cacert.pem')) ? resource_path('certs/cacert.pem') : null;
            }

            $response = Http::acceptJson()
                ->when($ca, fn ($http) => $http->withOptions(['verify' => $ca]))
                ->timeout((int) config('services.college_directory.timeout', 5))
                ->retry(2, 300, fn ($e) => $e instanceof ConnectionException || ($e instanceof RequestException && $e->response->serverError()), throw: false)
                ->get(rtrim((string) config('services.college_directory.base_url'), '/').$path, $query);

            if (! $response->successful()) {
                Log::warning('Direktori kampus: HTTP '.$response->status().' untuk '.$path);

                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::warning('Direktori kampus tidak terjangkau: '.$e->getMessage());

            return null;
        }
    }
}
