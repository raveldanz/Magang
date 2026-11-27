<?php

namespace App\Services;

use App\Models\University;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Master institusi asal peserta magang:
 *  - database/data/master_universities.json : perguruan tinggi (institution_type = perguruan_tinggi)
 *  - database/data/master_schools.json      : SMA/SMK/MA dari Data Referensi Kemendikdasmen
 *    (institution_type = sekolah; diperbarui manual lewat scripts/fetch_master_schools.php)
 */
class MasterUniversityService
{
    protected ?array $masterData = null;

    protected ?array $schoolData = null;

    public function __construct(private UniversityResolver $resolver) {}

    /**
     * Path ke dataset Master Perguruan Tinggi Indonesia
     */
    protected function getDatasetPath(): string
    {
        return database_path('data/master_universities.json');
    }

    protected function getSchoolDatasetPath(): string
    {
        return database_path('data/master_schools.json');
    }

    /**
     * Muat seluruh data master perguruan tinggi
     *
     * @return array<int, array<string, mixed>>
     */
    public function allMaster(): array
    {
        return $this->masterData ??= $this->loadJson($this->getDatasetPath());
    }

    /**
     * Muat seluruh data master sekolah menengah (SMA/SMK/MA).
     *
     * @return array<int, array<string, mixed>>
     */
    public function allSchools(): array
    {
        return $this->schoolData ??= $this->loadJson($this->getSchoolDatasetPath());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function loadJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Pencarian cerdas kampus dan sekolah berdasarkan nama, akronim/nama resmi, NPSN, atau kode.
     * Setiap hasil membawa 'institution_type' (perguruan_tinggi | sekolah).
     */
    public function search(string $keyword, int $limit = 25): Collection
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return collect($this->allMaster())
                ->map(fn ($item) => $item + ['institution_type' => University::TYPE_COLLEGE])
                ->take($limit);
        }

        $normKey = $this->normalize($keyword);
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // 1. Ambil institusi yang sudah terdaftar di database lokal terlebih dahulu
        $dbUnivs = University::query()
            ->where('is_verified', true)
            ->where(function ($q) use ($keyword, $like) {
                $q->where('name', $like, "%{$keyword}%")
                    ->orWhere('acronym', $like, "%{$keyword}%")
                    ->orWhere('code', $like, "%{$keyword}%");
            })
            ->take($limit)
            ->get();

        $results = collect();
        $seenKeys = [];

        foreach ($dbUnivs as $univ) {
            $seenKeys[$this->normalize($univ->name)] = true;
            $results->push([
                'id' => $univ->id,
                'name' => $univ->name,
                'code' => $univ->code,
                'acronym' => $univ->acronym ?: $univ->code,
                'city' => $univ->city ?? 'Indonesia',
                'province' => $univ->province ?? 'Indonesia',
                'type' => $univ->isSchool()
                    ? (str_contains(strtolower($univ->name), 'negeri') ? 'NEGERI' : 'SWASTA')
                    : (str_contains(strtolower($univ->name), 'negeri') ? 'PTN' : 'PTS'),
                'institution_type' => $univ->institution_type ?? University::TYPE_COLLEGE,
                'is_in_db' => true,
            ]);
        }

        // 2. Cari dari master dataset perguruan tinggi dan sekolah
        $scored = [];
        foreach ($this->allMaster() as $item) {
            $score = $this->score($normKey, [$item['name'] ?? ''], [$item['acronym'] ?? ($item['code'] ?? '')]);
            if ($score > 0) {
                $scored[] = ['score' => $score, 'item' => array_merge($item, [
                    'id' => null,
                    'institution_type' => University::TYPE_COLLEGE,
                    'is_in_db' => false,
                ])];
            }
        }
        foreach ($this->allSchools() as $school) {
            $score = $this->score($normKey, [$school['name'] ?? '', $school['official_name'] ?? ''], [$school['npsn'] ?? '']);
            if ($score > 0) {
                $scored[] = ['score' => $score, 'item' => $this->schoolItem($school)];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        foreach ($scored as $row) {
            if ($results->count() >= $limit) {
                break;
            }
            $cleanKey = $this->normalize($row['item']['name']);
            if (! isset($seenKeys[$cleanKey])) {
                $seenKeys[$cleanKey] = true;
                $results->push($row['item']);
            }
        }

        return $results->take($limit);
    }

    /**
     * Upsert satu perguruan tinggi dari direktori API eksternal.
     *
     * @param  array{name: string, key?: string, acronym?: string, domain?: string, web_page?: string, state_province?: string, country?: string}  $row
     * @return array{university: University|null, created: bool}
     */
    public function upsertDirectoryCollege(array $row): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return ['university' => null, 'created' => false];
        }

        $colleges = University::query()->where(fn ($query) => $query
            ->where('institution_type', University::TYPE_COLLEGE)->orWhereNull('institution_type'));
        $existing = empty($row['official_code']) ? null : (clone $colleges)->where('code', $row['official_code'])->first();
        if (empty($row['official_code'])) {
            $existing = $colleges->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        }

        if ($existing) {
            $changes = [];
            if (empty($existing->address) && ! empty($row['state_province'])) {
                $changes['address'] = $row['state_province'].', Indonesia';
            }
            if ($changes !== []) {
                $existing->update($changes);
            }

            return ['university' => $existing, 'created' => false];
        }

        $acronym = trim((string) ($row['acronym'] ?? ''));
        if ($acronym === '') {
            $words = preg_split('/\s+/', $name);
            $letters = '';
            foreach ($words as $w) {
                $first = mb_substr($w, 0, 1);
                if (ctype_alnum($first)) {
                    $letters .= strtoupper($first);
                }
            }
            $acronym = mb_substr($letters, 0, 6) ?: 'UNIV';
        }

        $code = $row['official_code'] ?? $acronym;
        if (University::where('code', $code)->exists()) {
            $code = $acronym.'-'.rand(100, 999);
        }

        $address = (! empty($row['state_province']) ? $row['state_province'].', ' : '').'Indonesia';

        $university = University::create([
            'name' => $name,
            'code' => $code,
            'acronym' => $acronym,
            'address' => $address,
            'logo' => null,
            'is_verified' => true,
            'status' => 'active',
            'institution_type' => University::TYPE_COLLEGE,
            'evaluation_scheme' => 'both',
            'weight_mentor' => 40,
            'weight_lecturer' => 60,
            'require_dpl' => true,
        ]);

        return ['university' => $university, 'created' => true];
    }

    /** Alamat berformat "jalan, kota, provinsi" dari satu baris direktori. */
    public static function cleanDirectoryAddress(?string $address): ?string
    {
        return $address === null ? null : trim((string) preg_replace('/\s*\b(?:no\.?\s*)?(?:telp|telepon|hp|wa)\.?\s*:?\s*\+?\d(?:[ ()-]*\d){7,14}/iu', '', $address));
    }

    public static function directoryAddress(array $row): string
    {
        return collect([self::cleanDirectoryAddress($row['address'] ?? null), $row['city'] ?? null, $row['province'] ?? null])
            ->filter(fn ($part) => trim((string) $part) !== '')->implode(', ');
    }

    /**
     * Upsert satu sekolah dari direktori nasional (data pihak ketiga, bukan input moderator).
     * Kuncinya NPSN (kolom code): baris yang sudah ada hanya dilengkapi di kolom kosong dan TIDAK PERNAH
     * diubah is_verified/status/institution_type-nya, sehingga keputusan moderasi admin tidak tertimpa.
     * Saat NPSN tersedia, hanya kecocokan kode yang membuktikan identitas sekolah. Tanpa NPSN, pencocokan hanya lewat nama dan kode dibiarkan kosong.
     *
     * @param  array{npsn?: string, name: string, form?: string, address?: string, city?: string, province?: string, status?: string}  $row
     * @return array{university: University|null, created: bool, filled: bool, matched_by: 'npsn'|'name'|null}
     */
    public function upsertDirectorySchool(array $row): array
    {
        $npsn = trim((string) ($row['npsn'] ?? ''));
        $name = trim((string) $row['name']);
        if ($name === '' || ($npsn !== '' && DB::table('excluded_school_codes')->where('code', $npsn)->exists())) {
            return ['university' => null, 'created' => false, 'filled' => false, 'matched_by' => null];
        }
        $address = self::directoryAddress($row);

        $existing = $npsn !== '' ? University::where('code', $npsn)->first() : null;
        $matchedBy = $existing ? 'npsn' : null;

        if (! $existing && $npsn === '') {
            $existing = University::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->orderBy('id')
                ->first();
            $matchedBy = $existing ? 'name' : null;
        }

        if ($existing) {
            $changes = [];
            if ($matchedBy === 'npsn' && ! $existing->address && $address !== '') {
                $changes['address'] = $address;
            }
            if ($changes !== []) {
                $existing->update($changes);
            }

            return ['university' => $existing, 'created' => false, 'filled' => $changes !== [], 'matched_by' => $matchedBy];
        }

        return ['university' => University::create([
            'name' => $name,
            'code' => $npsn ?: null,
            'address' => $address ?: null,
            'is_verified' => true, // NPSN = identitas resmi satuan pendidikan di registri nasional
            'status' => 'active',
            'institution_type' => University::TYPE_SCHOOL,
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
            'require_dpl' => false,
        ]), 'created' => true, 'filled' => false, 'matched_by' => null];
    }

    /**
     * Skor kecocokan kata kunci terhadap nama-nama dan kode-kode sebuah entri master (0 = tidak cocok).
     *
     * @param  array<int, string>  $names
     * @param  array<int, string>  $codes
     */
    protected function score(string $normKey, array $names, array $codes): int
    {
        $normNames = array_values(array_filter(array_map(fn ($n) => $this->normalize($n), $names)));
        $normCodes = array_values(array_filter(array_map(fn ($c) => $this->normalize($c), $codes)));

        foreach ($normCodes as $code) {
            if ($code === $normKey) {
                return 100;
            }
        }
        if (in_array($normKey, $normNames, true)) {
            return 90;
        }
        foreach ($normCodes as $code) {
            if (str_starts_with($code, $normKey)) {
                return 80;
            }
        }
        foreach ($normNames as $name) {
            if (str_contains($name, $normKey)) {
                return 70;
            }
        }

        // Semua kata kunci muncul di salah satu nama/kode
        $haystack = implode(' ', array_merge($normNames, $normCodes));
        foreach (explode(' ', $normKey) as $token) {
            if ($token !== '' && ! str_contains($haystack, $token)) {
                return 0;
            }
        }

        return 60;
    }

    /**
     * @param  array<string, mixed>  $school
     * @return array<string, mixed>
     */
    protected function schoolItem(array $school): array
    {
        return [
            'id' => null,
            'name' => $school['name'] ?? '',
            'code' => $school['npsn'] ?? null,
            'acronym' => $school['official_name'] ?? null,
            'npsn' => $school['npsn'] ?? null,
            'official_name' => $school['official_name'] ?? null,
            'form' => $school['form'] ?? null,
            'address' => $school['address'] ?? null,
            'city' => $school['city'] ?? 'Indonesia',
            'province' => $school['province'] ?? 'Indonesia',
            'type' => $school['status'] ?? null,
            'institution_type' => University::TYPE_SCHOOL,
            'is_in_db' => false,
        ];
    }

    /**
     * Resolve data master terpilih (kampus atau sekolah) ke Model University resmi di database
     *
     * @param  array<string, mixed>  $masterItem
     */
    public function resolveToUniversity(array $masterItem): University
    {
        if (($masterItem['institution_type'] ?? null) === University::TYPE_SCHOOL || ! empty($masterItem['npsn'])) {
            return $this->upsertSchool($masterItem)['university'];
        }

        return $this->upsertCollege($masterItem, false)['university'];
    }

    /**
     * Impor seluruh master perguruan tinggi.
     *
     * @return array{created: int, updated: int}
     */
    public function syncColleges(): array
    {
        $counts = ['created' => 0, 'updated' => 0];
        DB::transaction(function () use (&$counts) {
            foreach ($this->allMaster() as $item) {
                if (trim($item['name'] ?? '') === '') {
                    continue;
                }
                $result = $this->upsertCollege($item, true);
                $counts[$result['created'] ? 'created' : 'updated']++;
            }
        });

        return $counts;
    }

    /**
     * Impor seluruh master sekolah (aman dijalankan berulang: dicocokkan lewat NPSN dan nama).
     *
     * @return array{created: int, updated: int}
     */
    public function syncSchools(): array
    {
        $counts = ['created' => 0, 'updated' => 0];
        DB::transaction(function () use (&$counts) {
            $excluded = array_fill_keys(DB::table('excluded_school_codes')->pluck('code')->all(), true);
            foreach ($this->allSchools() as $school) {
                if (isset($excluded[$school['npsn']])) {
                    continue;
                }
                if (trim($school['name'] ?? '') === '' || trim($school['npsn'] ?? '') === '') {
                    continue;
                }
                $result = $this->upsertSchool($this->schoolItem($school));
                $counts[$result['created'] ? 'created' : 'updated']++;
            }
        });

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{university: University, created: bool}
     */
    protected function upsertCollege(array $item, bool $fillAcronym): array
    {
        $name = trim($item['name'] ?? '');
        $code = trim($item['code'] ?? ($item['acronym'] ?? 'UNIV'));
        $acronym = trim($item['acronym'] ?? $code);
        $city = trim($item['city'] ?? 'Indonesia');
        $province = trim($item['province'] ?? 'Indonesia');
        $logo = trim($item['logo'] ?? '');

        $existing = University::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->orWhere(fn ($q) => $q->whereNotNull('code')->where('code', $code))
            ->first();

        if ($existing) {
            $changes = ['is_verified' => true, 'status' => 'active'];
            if ($fillAcronym && ! $existing->acronym) {
                $changes['acronym'] = $acronym;
            }
            if ($logo !== '' && empty($existing->logo)) {
                $changes['logo'] = $logo;
            }
            $existing->update($changes);

            return ['university' => $existing, 'created' => false];
        }

        return ['university' => University::create([
            'name' => $name,
            'code' => $code,
            'acronym' => $acronym,
            'address' => $city.', '.$province,
            'logo' => $logo !== '' ? $logo : null,
            'is_verified' => true,
            'status' => 'active',
            'institution_type' => University::TYPE_COLLEGE,
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
            'require_dpl' => false,
        ]), 'created' => true];
    }

    /**
     * Data sekolah bawaan memakai NPSN untuk mengenali institusi.
     *
     * @param  array<string, mixed>  $item
     * @return array{university: University, created: bool}
     */
    protected function upsertSchool(array $item): array
    {
        $name = trim($item['name'] ?? '');
        $npsn = trim((string) ($item['npsn'] ?? $item['code'] ?? ''));
        $official = trim((string) ($item['official_name'] ?? $item['acronym'] ?? ''));
        $address = collect([$item['address'] ?? null, $item['city'] ?? null, $item['province'] ?? null])
            ->filter(fn ($part) => trim((string) $part) !== '')
            ->implode(', ');

        $existing = $npsn !== '' ? University::where('code', $npsn)->first() : null;

        if ($existing) {
            $existing->update(array_filter([
                'institution_type' => University::TYPE_SCHOOL,
                'is_verified' => true,
                'status' => 'active',
                'code' => $existing->code ?: ($npsn ?: null),
                'acronym' => $existing->acronym ?: ($official ?: null),
                'address' => $existing->address ?: ($address ?: null),
            ], fn ($value) => $value !== null));

            return ['university' => $existing, 'created' => false];
        }

        return ['university' => University::create([
            'name' => $name,
            'code' => $npsn ?: null,
            'acronym' => $official ?: null,
            'address' => $address ?: null,
            'is_verified' => true,
            'status' => 'active',
            'institution_type' => University::TYPE_SCHOOL,
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
            'require_dpl' => false,
        ]), 'created' => true];
    }

    /**
     * Normalisasi string untuk pencarian & pencocokan
     */
    protected function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
