<?php

namespace App\Services;

use App\Models\University;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MasterUniversityService
{
    protected ?array $masterData = null;

    /**
     * Path ke dataset Master Perguruan Tinggi Indonesia
     */
    protected function getDatasetPath(): string
    {
        return database_path('data/master_universities.json');
    }

    /**
     * Muat seluruh data master perguruan tinggi
     *
     * @return array<int, array<string, mixed>>
     */
    public function allMaster(): array
    {
        if ($this->masterData !== null) {
            return $this->masterData;
        }

        $path = $this->getDatasetPath();
        if (File::exists($path)) {
            $json = File::get($path);
            $decoded = json_decode($json, true);
            $this->masterData = is_array($decoded) ? $decoded : [];
        } else {
            $this->masterData = [];
        }

        return $this->masterData;
    }

    /**
     * Pencarian cerdas Master Perguruan Tinggi berdasarkan nama, akronim, atau kota
     */
    public function search(string $keyword, int $limit = 25): Collection
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return collect($this->allMaster())->take($limit);
        }

        $normKey = $this->normalize($keyword);
        $masterList = $this->allMaster();
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // 1. Ambil universitas yang sudah terdaftar di database lokal terlebih dahulu
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
            $key = $this->normalize($univ->name);
            $seenKeys[$key] = true;
            $results->push([
                'id' => $univ->id,
                'name' => $univ->name,
                'code' => $univ->code,
                'acronym' => $univ->acronym ?: $univ->code,
                'city' => $univ->city ?? 'Indonesia',
                'province' => $univ->province ?? 'Indonesia',
                'type' => str_contains(strtolower($univ->name), 'negeri') ? 'PTN' : 'PTS',
                'is_in_db' => true,
            ]);
        }

        // 2. Cari dari Master Dataset PDDikti / Kemdikbud
        $scored = [];
        foreach ($masterList as $item) {
            $name = $item['name'] ?? '';
            $acronym = $item['acronym'] ?? ($item['code'] ?? '');
            $cleanKey = $this->normalize($name);

            if (isset($seenKeys[$cleanKey])) {
                continue;
            }

            $score = 0;
            $normName = $this->normalize($name);
            $normAcr = $this->normalize($acronym);

            // Exact acronym match (misal cari "UNESA" atau "ITS")
            if ($normAcr === $normKey) {
                $score = 100;
            } elseif (str_starts_with($normAcr, $normKey)) {
                $score = 80;
            } elseif ($normName === $normKey) {
                $score = 90;
            } elseif (str_contains($normName, $normKey)) {
                $score = 70;
            } else {
                // Token matching
                $tokens = explode(' ', $normKey);
                $allMatched = true;
                foreach ($tokens as $t) {
                    if (! str_contains($normName, $t) && ! str_contains($normAcr, $t)) {
                        $allMatched = false;
                        break;
                    }
                }
                if ($allMatched) {
                    $score = 60;
                }
            }

            if ($score > 0) {
                $scored[] = [
                    'score' => $score,
                    'item' => array_merge($item, [
                        'id' => null,
                        'is_in_db' => false,
                    ]),
                ];
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
     * Resolve data master terpilih ke Model University resmi di database
     *
     * @param  array<string, mixed>  $masterItem
     */
    public function resolveToUniversity(array $masterItem): University
    {
        $name = trim($masterItem['name'] ?? '');
        $code = trim($masterItem['code'] ?? ($masterItem['acronym'] ?? 'UNIV'));
        $acronym = trim($masterItem['acronym'] ?? $code);
        $city = trim($masterItem['city'] ?? 'Indonesia');
        $province = trim($masterItem['province'] ?? 'Indonesia');

        // Cari apakah sudah ada di database
        $existing = University::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->orWhere(function ($q) use ($code) {
                $q->whereNotNull('code')->where('code', $code);
            })
            ->first();

        if ($existing) {
            if (! $existing->is_verified) {
                $existing->update(['is_verified' => true, 'status' => 'active']);
            }

            return $existing;
        }

        return University::create([
            'name' => $name,
            'code' => $code,
            'acronym' => $acronym,
            'address' => $city.', '.$province,
            'city' => $city,
            'province' => $province,
            'is_verified' => true,
            'status' => 'active',
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
            'require_dpl' => false,
        ]);
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
