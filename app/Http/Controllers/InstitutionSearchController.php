<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pencarian kampus/sekolah untuk dropdown async (searchable-select mode remote).
 *
 * Hanya membaca database, tanpa memanggil API luar, agar kecepatannya konsisten. Data diisi oleh sinkron
 * direktori (tombol Super Admin dan jadwal mingguan); yang belum ada dimasukkan manual oleh peserta.
 */
class InstitutionSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(50, (int) $request->query('limit', 25)));
        $type = $request->query('type');
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $escaped = addcslashes($q, '%_\\');

        $alias = preg_replace_callback('/\b(SMA|SMK)\s+(negeri|swasta)\b/i', fn ($m) => strtoupper($m[1].$m[2][0]), $escaped);
        if ($alias === $escaped) {
            $alias = preg_replace_callback('/\b(SMA|SMK)(N|S)\b/i', fn ($m) => $m[1].(strtoupper($m[2]) === 'N' ? ' Negeri' : ' Swasta'), $escaped);
        }

        $query = University::query()
            ->where('status', 'active')
            ->where(fn ($w) => $w->whereNull('code')->orWhere('code', '!=', 'LAINNYA'))
            // Pengguna non-admin hanya melihat institusi terverifikasi; admin juga melihat yang menunggu verifikasi.
            ->when(! in_array($request->user()?->role, ['super_admin', 'admin'], true), fn ($qb) => $qb->where('is_verified', true))
            ->when(in_array($type, [University::TYPE_COLLEGE, University::TYPE_SCHOOL], true), fn ($qb) => $qb->where('institution_type', $type))
            ->when($q !== '', function ($qb) use ($like, $escaped, $alias) {
                $qb->where(fn ($w) => $w->where('name', $like, "%{$escaped}%")
                    ->orWhere('acronym', $like, "%{$escaped}%")
                    ->orWhere('code', $like, "{$escaped}%")
                    ->when($alias !== $escaped, fn ($or) => $or->orWhere('name', $like, "%{$alias}%")->orWhere('acronym', $like, "%{$alias}%")));
            })
            ->when($q !== '', fn ($qb) => $qb->orderByRaw(
                'CASE WHEN LOWER(acronym) = ? OR LOWER(code) = ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 WHEN institution_type = ? THEN 2 ELSE 3 END',
                [mb_strtolower($q), mb_strtolower($q), mb_strtolower($escaped).'%', University::TYPE_COLLEGE]
            ))
            ->orderByRaw('CASE WHEN is_verified THEN 0 ELSE 1 END')
            ->orderBy('name');

        $data = $query->limit($limit)->get()->map(fn (University $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'acronym' => $u->acronym ?: $u->code,
            // Kota membedakan sekolah yang namanya sama.
            'meta' => implode(' · ', array_filter([$u->code, $u->cityLabel()])) ?: null,
            'institution_type' => $u->institution_type,
        ])->all();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
