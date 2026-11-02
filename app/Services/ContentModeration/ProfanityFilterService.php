<?php

namespace App\Services\ContentModeration;

class ProfanityFilterService
{
    /**
     * Daftar kata kasar, kotor, vulgar, atau tidak pantas (Indonesian, Javanese & English).
     * Seluruh kata dicocokkan dengan batasan kata utuh (\b...\b) untuk mencegah
     * false positive (Scunthorpe problem).
     */
    protected static array $badWords = [
        // Kata Kasar Jawa (Surabaya / Jatim & Jateng)
        'jancok', 'jancuk', 'jancik', 'dancok', 'dancuk', 'damput', 'jancukan',
        'ancok', 'ancuk', 'cok', 'cuk', 'coeg',
        'taek', 'telek', 'tai', 'taik', 'silit',
        'matamu', 'ndasmu', 'cocotmu', 'lambemu', 'modar',
        'peli', 'gathel', 'gapleki', 'tempik', 'peler',
        'kenthu', 'kirik',

        // Organ intim & seksual eksplisit
        'kontol', 'kntl', 'memek', 'mmk', 'jembut', 'jmbt', 'ngentot', 'ngewe', 'ewe', 'pantek',
        'puki', 'pukimak', 'pepek', 'itil', 'tetek', 'titit', 'bocong', 'dildo', 'masturbasi',
        'porno', 'bokep', 'vagina', 'penis', 'seks',

        // Umpatan kasar / peyoratif Indonesia
        'anjing', 'anjir', 'anjrit', 'babi', 'bangsat', 'bajingan', 'brengsek', 'kampret', 'asu',
        'lonte', 'perek', 'jablay',

        // Hinaan kapasitas / kecerdasan yang merendahkan
        'goblok', 'goblog', 'tolol', 'idiot', 'bego', 'dungu', 'pekok', 'geblek',

        // Slang & English profanity
        'fuck', 'fucking', 'fucked', 'fucker', 'motherfucker', 'mf',
        'shit', 'bullshit', 'bitch', 'bitches', 'bastard', 'asshole', 'cunt',
        'dick', 'dicks', 'cock', 'pussy', 'whore', 'slut', 'prick', 'retard', 'nigger', 'fag', 'faggot',
    ];

    /**
     * Whitelist kata-kata aman yang secara kebetulan memuat substring mirip kata kotor.
     */
    protected static array $whitelist = [
        'pantai', 'partai', 'petai', 'rantai', 'intai', 'mengintai', 'terintai',
        'asuransi', 'asumsi', 'asuh', 'pengasuh', 'mengasuh', 'asuhan', 'masuk', 'termasuk',
        'pemerintah', 'memeriksa', 'analisis', 'analitika', 'analisa',
        'surabaya', 'pembimbing', 'bimbingan', 'perpustakaan', 'kearsipan', 'titik',
        'kontrak', 'konteks', 'konten', 'kontribusi', 'saluran', 'memperhatikan',
        'susu', 'satu', 'pasu', 'basuh', 'titipan', 'titik-titik', 'cocok', 'mencocokkan',
        'gatal', 'menggatal',
    ];

    /**
     * Periksa apakah sebuah teks mengandung kata kotor / jorok.
     *
     * @return array{is_clean: bool, detected: ?string, message: ?string}
     */
    public static function check(string $text): array
    {
        $normalized = self::normalizeText($text);

        // 1. Pemindaian langsung kata per kata
        preg_match_all('/[a-z0-9]+/i', $normalized, $matches);
        $words = $matches[0] ?? [];

        foreach ($words as $word) {
            $word = strtolower($word);

            // Jika kata persis ada dalam whitelist aman, lewati
            if (in_array($word, self::$whitelist, true)) {
                continue;
            }

            // Normalisasi duplikasi karakter berlebih (misal: "jaaaancooook" -> "jancok", "kooontooool" -> "kontol")
            $dedupWord = preg_replace('/(.)\\1{2,}/', '$1$1', $word);
            $singleDedup = preg_replace('/(.)\\1+/', '$1', $word);

            foreach (self::$badWords as $badWord) {
                if ($word === $badWord || $dedupWord === $badWord || $singleDedup === $badWord) {
                    return [
                        'is_clean' => false,
                        'detected' => $word,
                        'message' => "Komentar memuat kata yang tidak pantas (\"{$word}\"). Mohon gunakan bahasa yang sopan dan santun.",
                    ];
                }

                // Cek regex batas kata utuh (\b...\b)
                $pattern = '/\b' . preg_quote($badWord, '/') . '\b/i';
                if (preg_match($pattern, $word)) {
                    return [
                        'is_clean' => false,
                        'detected' => $badWord,
                        'message' => "Komentar memuat kata yang tidak pantas (\"{$badWord}\"). Mohon gunakan bahasa yang sopan dan santun.",
                    ];
                }
            }
        }

        // 2. Pemindaian teks tanpa spasi & pemisah untuk mendeteksi evasions (misal: "j.a.n.c.o.k", "a_s_u", "f-u-c-k")
        $stripped = preg_replace('/[^a-z0-9]/', '', $normalized);
        $strippedDedup = preg_replace('/(.)\\1+/', '$1', $stripped);

        foreach (self::$badWords as $badWord) {
            // Hanya periksa kata buruk yang panjangnya minimal 3 karakter untuk stripped matching agar tidak false positive
            if (strlen($badWord) >= 3 && !in_array($badWord, ['cok', 'cuk', 'tai', 'asu'])) {
                if (str_contains($stripped, $badWord) || str_contains($strippedDedup, $badWord)) {
                    // Pastikan bukan bagian dari whitelist
                    $isWhitelisted = false;
                    foreach (self::$whitelist as $wl) {
                        if (str_contains($stripped, str_replace([' ', '-'], '', $wl))) {
                            $isWhitelisted = true;
                            break;
                        }
                    }
                    if (!$isWhitelisted) {
                        return [
                            'is_clean' => false,
                            'detected' => $badWord,
                            'message' => "Komentar memuat kata yang tidak pantas (\"{$badWord}\"). Mohon gunakan bahasa yang sopan dan santun.",
                        ];
                    }
                }
            }
        }

        return [
            'is_clean' => true,
            'detected' => null,
            'message' => null,
        ];
    }

    /**
     * Sensor kata tidak pantas dengan bintang (***).
     */
    public static function mask(string $text): string
    {
        $result = $text;
        foreach (self::$badWords as $badWord) {
            $pattern = '/\b' . preg_quote($badWord, '/') . '\b/i';
            $result = preg_replace_callback($pattern, function ($matches) {
                $len = strlen($matches[0]);
                return str_repeat('*', max($len, 3));
            }, $result);
        }
        return $result;
    }

    /**
     * Normalisasi karakter leetspeak & pemisah simbol.
     */
    protected static function normalizeText(string $text): string
    {
        $str = strtolower($text);

        // Hanya ganti tanda seru '!' menjadi 'i' jika diapit karakter kata (misal: 'an!ing', 'f!ck')
        $str = preg_replace('/(?<=[a-z0-9])!(?=[a-z0-9])/i', 'i', $str);

        // Substitusi leetspeak umum
        $replacements = [
            '@' => 'a',
            '4' => 'a',
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '$' => 's',
            '5' => 's',
            '7' => 't',
            '+' => 't',
        ];

        return strtr($str, $replacements);
    }
}
