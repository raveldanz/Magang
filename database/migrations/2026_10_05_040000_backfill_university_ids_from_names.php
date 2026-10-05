<?php

use App\Services\UniversityResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pembersihan data kampus: isi university_id yang masih kosong pada users & student_profiles
 * berdasarkan kecocokan PERSIS (setelah normalisasi huruf/tanda baca) terhadap nama, akronim,
 * atau kode kampus. Nama yang ambigu (cocok ke >1 kampus) atau tidak cocok dibiarkan apa adanya
 * agar tidak salah tempel — sisanya bisa dirapikan admin lewat fitur "Gabungkan Kampus".
 *
 * Setelah ini, seluruh pencarian kampus di aplikasi cukup memakai university_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        $index = [];
        foreach (DB::table('universities')->get() as $u) {
            foreach ([$u->name ?? null, $u->code ?? null, $u->acronym ?? null] as $label) {
                $key = UniversityResolver::normalize($label);
                if ($key !== '') {
                    $index[$key][$u->id] = true;
                }
            }
        }

        $resolve = function (?string $text) use ($index): ?int {
            $ids = array_keys($index[UniversityResolver::normalize($text)] ?? []);

            return count($ids) === 1 ? (int) $ids[0] : null;
        };

        // 1. Profil mahasiswa: dari teks "universitas"
        DB::table('student_profiles')->whereNull('university_id')->whereNotNull('universitas')
            ->orderBy('id')->get(['id', 'universitas'])
            ->each(function ($p) use ($resolve) {
                if ($id = $resolve($p->universitas)) {
                    DB::table('student_profiles')->where('id', $p->id)->update(['university_id' => $id]);
                }
            });

        // 2. Akun (mahasiswa, dosen, admin kampus): dari profil, lalu dari teks users.university
        DB::table('users')->whereNull('university_id')
            ->whereIn('role', ['mahasiswa', 'dosen', 'academic_advisor', 'universitas'])
            ->orderBy('id')->get(['id', 'university'])
            ->each(function ($u) use ($resolve) {
                $id = DB::table('student_profiles')->where('user_id', $u->id)->value('university_id')
                    ?: $resolve($u->university);
                if ($id) {
                    DB::table('users')->where('id', $u->id)->update(['university_id' => $id]);
                }
            });

        // 3. Profil yang masih kosong tapi akunnya sudah punya university_id
        DB::table('student_profiles')->whereNull('university_id')->orderBy('id')->get(['id', 'user_id'])
            ->each(function ($p) {
                $id = DB::table('users')->where('id', $p->user_id)->value('university_id');
                if ($id) {
                    DB::table('student_profiles')->where('id', $p->id)->update(['university_id' => $id]);
                }
            });
    }

    public function down(): void
    {
        // Pengisian data (backfill) tidak dibatalkan.
    }
};
