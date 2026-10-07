<?php

namespace App\Services;

use App\Models\University;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Satu-satunya tempat mencocokkan "nama kampus" → record universities.
 *
 * Aturan:
 *  - Utamakan university_id (users / student_profiles). Pencocokan nama hanya fallback untuk data lama.
 *  - Fallback memakai pencocokan EXACT (tidak peka huruf besar/kecil & tanda baca) terhadap
 *    nama, akronim, kode, atau slug — BUKAN LIKE '%nama%' yang bisa salah pilih kampus
 *    (mis. "UPN" cocok ke dua kampus berbeda).
 *  - suggest() dipakai saat mahasiswa mengetik kampus baru, agar kampus yang sebenarnya
 *    sudah terdaftar (beda ejaan/singkatan) ditawarkan dulu sebelum membuat entri baru.
 */
class UniversityResolver
{
    /** Kata generik yang tidak membedakan kampus satu dengan lainnya. */
    private const GENERIC_WORDS = [
        'universitas', 'university', 'univ', 'institut', 'institute', 'politeknik', 'poltek',
        'sekolah', 'tinggi', 'akademi', 'stie', 'stikes', 'stmik', 'kota', 'kab', 'kabupaten',
        'the', 'of', 'dan', 'and',
    ];

    /** Kemiripan minimum agar sebuah kampus ditawarkan sebagai saran. */
    public const SUGGEST_THRESHOLD = 0.55;

    /** Kemiripan minimum yang dianggap "hampir pasti kampus yang sama" (wajib konfirmasi). */
    public const STRONG_MATCH_THRESHOLD = 0.8;

    /**
     * Normalisasi teks untuk perbandingan: huruf kecil, tanpa tanda baca/kutip, spasi rapi.
     */
    public static function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Inti nama kampus tanpa kata generik ("Universitas Negeri Surabaya" → "negeri surabaya").
     */
    public static function core(?string $text): string
    {
        $words = array_filter(
            explode(' ', self::normalize($text)),
            fn ($w) => $w !== '' && ! in_array($w, self::GENERIC_WORDS, true)
        );

        return implode(' ', $words);
    }

    /**
     * Kata kunci pencarian sebuah kampus (dipakai combobox & pencocokan).
     *
     * @return array<int, string>
     */
    public static function keywordsFor(University $university): array
    {
        return array_values(array_unique(array_filter([
            self::normalize($university->name),
            self::core($university->name),
            self::normalize($university->acronym ?? null),
            self::normalize($university->code ?? null),
            self::normalize(str_replace('-', ' ', (string) ($university->slug ?? ''))),
        ])));
    }

    /**
     * Cari kampus yang namanya/akronim/kode/slug-nya SAMA PERSIS (setelah normalisasi).
     * Mengembalikan null bila tidak ada atau ambigu (lebih dari satu kampus berbeda cocok).
     */
    public function findExact(?string $text): ?University
    {
        $needle = self::normalize($text);
        if ($needle === '') {
            return null;
        }

        $matches = University::query()
            ->get(['id', 'name', 'acronym', 'code', 'slug', 'is_verified'])
            ->filter(function (University $u) use ($needle) {
                return in_array($needle, [
                    self::normalize($u->name),
                    self::normalize($u->acronym ?? null),
                    self::normalize($u->code ?? null),
                    self::normalize(str_replace('-', ' ', (string) ($u->slug ?? ''))),
                ], true);
            });

        if ($matches->count() === 1) {
            return University::find($matches->first()->id);
        }

        // Beberapa cocok: utamakan yang namanya persis sama & terverifikasi
        $byName = $matches->filter(fn ($u) => self::normalize($u->name) === $needle)->sortByDesc('is_verified');

        return $byName->isNotEmpty() ? University::find($byName->first()->id) : null;
    }

    /**
     * Kampus untuk seorang user: university_id → profil mahasiswa → nama persis (data lama).
     */
    public function forUser(?User $user): ?University
    {
        if (! $user) {
            return null;
        }

        $id = $user->university_id ?: $user->studentProfile?->university_id;
        if ($id) {
            return University::find($id);
        }

        return $this->findExact($user->studentProfile?->universitas ?? $user->university);
    }

    /**
     * Kampus terdaftar yang mirip dengan teks input (urut paling mirip).
     *
     * @return Collection<int, array{university: University, score: float}>
     */
    public function suggest(?string $text, int $limit = 5, bool $verifiedOnly = true): Collection
    {
        $input = self::normalize($text);
        $inputCore = self::core($text);
        if (mb_strlen($input) < 2) {
            return collect();
        }

        return University::query()
            ->when($verifiedOnly, fn ($q) => $q->where('is_verified', true))
            ->get()
            ->map(fn (University $u) => ['university' => $u, 'score' => $this->score($input, $inputCore, $u)])
            ->filter(fn ($row) => $row['score'] >= self::SUGGEST_THRESHOLD)
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    private function score(string $input, string $inputCore, University $u): float
    {
        $best = 0.0;

        foreach (self::keywordsFor($u) as $keyword) {
            if ($keyword === $input) {
                return 1.0;
            }
            $best = max($best, self::similarity($input, $keyword));
            if ($inputCore !== '') {
                $best = max($best, self::similarity($inputCore, $keyword));
            }
        }

        // Semua kata inti input terdapat di nama kampus (mis. "airlangga" ⊂ "universitas airlangga")
        $coreName = self::core($u->name);
        if ($inputCore !== '' && $coreName !== '') {
            $tokens = explode(' ', $inputCore);
            $nameTokens = explode(' ', $coreName);
            $hits = count(array_filter($tokens, fn ($t) => in_array($t, $nameTokens, true)));
            if ($hits === count($tokens)) {
                $best = max($best, 0.6 + 0.4 * ($hits / max(1, count($nameTokens))));
            }
        }

        return round($best, 3);
    }

    /**
     * Koefisien Dice berbasis bigram huruf (tahan salah ketik: "airlanga" ≈ "airlangga").
     */
    public static function similarity(string $a, string $b): float
    {
        $a = str_replace(' ', '', $a);
        $b = str_replace(' ', '', $b);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if ($a === $b) {
            return 1.0;
        }
        if (mb_strlen($a) < 2 || mb_strlen($b) < 2) {
            return 0.0;
        }

        $bigrams = function (string $s): array {
            $out = [];
            $len = mb_strlen($s);
            for ($i = 0; $i < $len - 1; $i++) {
                $bg = mb_substr($s, $i, 2);
                $out[$bg] = ($out[$bg] ?? 0) + 1;
            }

            return $out;
        };

        $x = $bigrams($a);
        $y = $bigrams($b);
        $intersection = 0;
        foreach ($x as $bg => $count) {
            $intersection += min($count, $y[$bg] ?? 0);
        }

        return (2 * $intersection) / (array_sum($x) + array_sum($y));
    }
}
