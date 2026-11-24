<?php

namespace App\Http\Controllers;

use App\Models\InternshipProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicProjectController extends Controller
{
    /**
     * Tampilkan Galeri & Histori Proyek Dinas (Katalog Terbuka Perjalanan Inovasi Mahasiswa)
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));
        $year = trim((string) $request->input('year', ''));
        $sort = trim((string) $request->input('sort', 'latest'));

        $user = Auth::user();

        $query = InternshipProject::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ]);

        // Pengunjung umum hanya melihat proyek publik; staf dinas juga melihat proyek privat dinasnya sendiri
        $query->visibleTo($user);

        $driver = \DB::connection()->getDriverName();
        $like = $driver === 'pgsql' ? 'ilike' : 'like';

        // 1. Filter Pencarian Teks
        if (! empty($search)) {
            $query->where(function ($q) use ($search, $like) {
                $q->where('title', $like, "%{$search}%")
                    ->orWhere('description', $like, "%{$search}%")
                    ->orWhere('community_benefit', $like, "%{$search}%")
                    ->orWhereHas('placement.application.user', function ($uq) use ($search, $like) {
                        $uq->where('name', $like, "%{$search}%")
                            ->orWhereHas('studentProfile', function ($sq) use ($search, $like) {
                                $sq->where('universitas', $like, "%{$search}%")
                                    ->orWhere('nim', $like, "%{$search}%")
                                    ->orWhere('jurusan', $like, "%{$search}%");
                            });
                    })
                    ->orWhereHas('members.placement.application.user', function ($uq) use ($search, $like) {
                        $uq->where('name', $like, "%{$search}%")
                            ->orWhereHas('studentProfile', function ($sq) use ($search, $like) {
                                $sq->where('universitas', $like, "%{$search}%")
                                    ->orWhere('nim', $like, "%{$search}%")
                                    ->orWhere('jurusan', $like, "%{$search}%");
                            });
                    })
                    ->orWhereHas('placement.application.unit', function ($unitQ) use ($search, $like) {
                        $unitQ->where('name', $like, "%{$search}%");
                    });
            });
        }

        // 2. Filter Kategori
        if (! empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        // 3. Filter Tahun
        if (! empty($year) && $year !== 'all') {
            $query->whereYear('created_at', (int) $year);
        }

        // 4. Pengurutan Data
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'popular' => $query->orderBy('likes_count', 'desc')->orderBy('created_at', 'desc'),
            'discussed' => $query->orderBy('comments_count', 'desc')->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $projects = $query->paginate(6)->withQueryString();

        // 5. Kelompokkan Proyek Berdasarkan Tahun untuk Tampilan Timeline Estetik
        $groupedProjects = $projects->getCollection()->groupBy(function ($item) {
            return $item->created_at ? $item->created_at->format('Y') : date('Y');
        });

        // 6. Hitung Total Karya per Tahun (untuk Badge & Tombol "Lihat Semua Tahun X")
        $yearCol = $driver === 'sqlite'
            ? "strftime('%Y', created_at)"
            : 'EXTRACT(YEAR FROM created_at)';

        $yearlyCounts = InternshipProject::visibleTo($user)
            ->selectRaw("{$yearCol} as yr, COUNT(*) as count")
            ->groupByRaw($yearCol)
            ->pluck('count', 'yr')
            ->mapWithKeys(fn ($c, $y) => [(string) (int) $y => (int) $c]);

        $availableYears = $yearlyCounts->keys()
            ->map(fn ($y) => (int) $y)
            ->sortDesc()
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) date('Y')]);
        }

        // Metrik Statistik Ringkas Galeri
        $stats = [
            'total_projects' => InternshipProject::visibleTo($user)->count(),
            'total_likes' => (int) InternshipProject::visibleTo($user)->sum('likes_count'),
            'total_comments' => (int) InternshipProject::visibleTo($user)->sum('comments_count'),
            'total_categories' => count(InternshipProject::CATEGORIES),
        ];

        return view('projects.index', compact(
            'projects',
            'groupedProjects',
            'availableYears',
            'yearlyCounts',
            'stats',
            'search',
            'category',
            'year',
            'sort'
        ));
    }
}
