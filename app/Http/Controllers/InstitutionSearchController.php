<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Services\Directory\SchoolDirectoryClient;
use App\Services\MasterUniversityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pencarian kampus/sekolah untuk dropdown async (searchable-select mode remote).
 *
 * search() hanya membaca (GET, tanpa efek samping). Sekolah dari direktori nasional yang belum ada di
 * database dikembalikan sebagai saran tanpa id; baru disimpan lewat resolve() (POST + CSRF) saat dipilih.
 */
class InstitutionSearchController extends Controller
{
    public function search(Request $request, SchoolDirectoryClient $directory): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(50, (int) $request->query('limit', 25)));
        $type = $request->query('type');
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $escaped = addcslashes($q, '%_\\');

        $query = University::query()
            ->where('status', 'active')
            ->where(fn ($w) => $w->whereNull('code')->orWhere('code', '!=', 'LAINNYA'))
            // Pengguna non-admin hanya melihat institusi terverifikasi; admin juga melihat yang menunggu verifikasi.
            ->when(! in_array($request->user()?->role, ['super_admin', 'admin'], true), fn ($qb) => $qb->where('is_verified', true))
            ->when(in_array($type, [University::TYPE_COLLEGE, University::TYPE_SCHOOL], true), fn ($qb) => $qb->where('institution_type', $type))
            ->when($q !== '', function ($qb) use ($like, $escaped) {
                $qb->where(fn ($w) => $w->where('name', $like, "%{$escaped}%")
                    ->orWhere('acronym', $like, "%{$escaped}%")
                    ->orWhere('code', $like, "{$escaped}%"));
            })
            ->when($q !== '', fn ($qb) => $qb->orderByRaw(
                'CASE WHEN LOWER(acronym) = ? OR LOWER(code) = ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 WHEN institution_type = ? THEN 2 ELSE 3 END',
                [mb_strtolower($q), mb_strtolower($q), mb_strtolower($escaped).'%', University::TYPE_COLLEGE]
            ))
            ->orderByRaw('CASE WHEN is_verified THEN 0 ELSE 1 END')
            ->orderBy('name');

        $rows = $query->limit($limit)->get();

        $data = $rows->map(fn (University $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'acronym' => $u->acronym ?: $u->code,
            'meta' => $u->code,
            'institution_type' => $u->institution_type,
        ])->all();

        // Hasil lokal sedikit: tambahkan saran dari direktori nasional (maks 5), belum disimpan.
        if ($q !== '' && mb_strlen($q) >= 3 && count($data) < 8 && $directory->enabled()) {
            $known = $rows->pluck('code')->filter()->all();
            $added = 0;
            foreach ($directory->search($q, 10) as $hit) {
                if ($added >= 5 || $hit['npsn'] === '' || in_array($hit['npsn'], $known, true)) {
                    continue;
                }
                $data[] = [
                    'id' => 'npsn:'.$hit['npsn'],
                    'name' => $hit['name'],
                    'acronym' => $hit['form'] ?: null,
                    'meta' => trim($hit['npsn'].' · '.$hit['city'], ' ·'),
                    'institution_type' => University::TYPE_SCHOOL,
                    'resolve_url' => route('institutions.resolve'),
                ];
                $added++;
            }
        }

        return response()->json(['status' => 'success', 'data' => array_slice($data, 0, $limit)]);
    }

    /**
     * Simpan sekolah dari saran direktori agar punya id. Hanya menerima NPSN: datanya diambil dari
     * hasil API yang tersimpan di cache server, bukan dari isian pengguna.
     */
    public function resolve(Request $request, SchoolDirectoryClient $directory, MasterUniversityService $master): JsonResponse
    {
        $validated = $request->validate(['npsn' => ['required', 'string', 'regex:/^\d{6,10}$/']]);

        $row = $directory->cached($validated['npsn']);
        if ($row === null) {
            return response()->json(['status' => 'error', 'message' => 'Data sekolah tidak ditemukan. Cari ulang nama sekolahnya.'], 404);
        }

        $university = $master->upsertDirectorySchool($row)['university'];
        if (! $university) {
            return response()->json(['status' => 'error', 'message' => 'Data sekolah tidak valid.'], 422);
        }

        return response()->json(['status' => 'success', 'data' => [
            'id' => $university->id,
            'name' => $university->name,
            'acronym' => $university->acronym ?: $university->code,
            'meta' => $university->code,
            'institution_type' => $university->institution_type,
        ]]);
    }
}
