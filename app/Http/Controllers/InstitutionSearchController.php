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
 * Mengembalikan baris `universities` sungguhan (ber-id) agar bisa langsung dipakai sebagai university_id.
 */
class InstitutionSearchController extends Controller
{
    public function __invoke(Request $request, SchoolDirectoryClient $directory, MasterUniversityService $master): JsonResponse
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
                "CASE WHEN LOWER(acronym) = ? OR LOWER(code) = ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 WHEN institution_type = ? THEN 2 ELSE 3 END",
                [mb_strtolower($q), mb_strtolower($q), mb_strtolower($escaped).'%', University::TYPE_COLLEGE]
            ))
            ->orderByRaw('CASE WHEN is_verified THEN 0 ELSE 1 END')
            ->orderBy('name');

        $rows = $query->limit($limit)->get();

        // Hasil lokal sedikit: lengkapi dari direktori nasional (maks 5 sekolah), disimpan agar punya id.
        if ($q !== '' && mb_strlen($q) >= 3 && $rows->count() < 8 && $directory->enabled()) {
            $known = $rows->pluck('code')->filter()->all();
            $added = 0;
            foreach ($directory->search($q, 10) as $hit) {
                if ($added >= 5 || $hit['npsn'] === '' || in_array($hit['npsn'], $known, true)) {
                    continue;
                }
                $rows->push($master->upsertDirectorySchool($hit)['university']);
                $added++;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $rows->unique('id')->take($limit)->map(fn (University $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'acronym' => $u->acronym ?: $u->code,
                'meta' => $u->code,
                'institution_type' => $u->institution_type,
            ])->values(),
        ]);
    }
}
