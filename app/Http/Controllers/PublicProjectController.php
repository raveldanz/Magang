<?php

namespace App\Http\Controllers;

use App\Models\InternshipProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicProjectController extends Controller
{
    private const PER_YEAR_LIMIT = 3;

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
        $driver = \DB::connection()->getDriverName();
        $like = $driver === 'pgsql' ? 'ilike' : 'like';
        $yearCol = $driver === 'sqlite'
            ? "strftime('%Y', created_at)"
            : 'EXTRACT(YEAR FROM created_at)';

        $baseQuery = InternshipProject::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ])->visibleTo($user);

        // 1. Filter Pencarian Teks
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search, $like) {
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
        if ($category !== '' && $category !== 'all') {
            $baseQuery->where('category', $category);
        }

        $applySort = static function ($query, string $sortMode): void {
            match ($sortMode) {
                'oldest' => $query->orderBy('created_at', 'asc')->orderBy('id', 'asc'),
                'popular' => $query->orderBy('likes_count', 'desc')->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
                'discussed' => $query->orderBy('comments_count', 'desc')->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
                default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
            };
        };

        $isSingleYearFilter = ($year !== '' && $year !== 'all');
        $yearSections = collect();
        $projects = null;

        if ($isSingleYearFilter) {
            $yearInt = (int) $year;
            $singleYearQuery = (clone $baseQuery)->whereYear('created_at', $yearInt);
            $applySort($singleYearQuery, $sort);
            $projects = $singleYearQuery->paginate(self::PER_YEAR_LIMIT)->withQueryString();

            if ($projects->isNotEmpty()) {
                $yearSections->put((string) $yearInt, [
                    'year' => $yearInt,
                    'paginator' => $projects,
                    'items' => $projects->getCollection(),
                ]);
            }
        } else {
            // Semua tahun (mis. 2026 s.d. 2022) tampil dalam satu halaman tanpa paginasi global;
            // masing-masing tahun menampilkan maksimal 3 proyek dengan paginasi per tahun bila > 3.
            $matchedYears = (clone $baseQuery)
                ->selectRaw("{$yearCol} as yr")
                ->whereNotNull('created_at')
                ->groupByRaw($yearCol)
                ->pluck('yr')
                ->map(fn ($y) => (int) $y)
                ->filter(fn ($y) => $y > 0)
                ->unique()
                ->values();

            $matchedYears = $sort === 'oldest'
                ? $matchedYears->sort()->values()
                : $matchedYears->sortDesc()->values();

            foreach ($matchedYears as $yr) {
                $yearQuery = (clone $baseQuery)->whereYear('created_at', $yr);
                $applySort($yearQuery, $sort);
                $yearPaginator = $yearQuery
                    ->paginate(self::PER_YEAR_LIMIT, ['*'], "page_{$yr}")
                    ->appends($request->except('page'))
                    ->fragment("tahun-{$yr}");

                if ($yearPaginator->isNotEmpty()) {
                    $yearSections->put((string) $yr, [
                        'year' => $yr,
                        'paginator' => $yearPaginator,
                        'items' => $yearPaginator->getCollection(),
                    ]);
                }
            }
        }

        // Kelompokkan proyek per tahun agar kompatibel dengan layout timeline
        $groupedProjects = $yearSections->mapWithKeys(fn ($section, $yr) => [$yr => $section['items']]);

        // Hitung Total Karya per Tahun (untuk Dropdown Filter & Badge)
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
            'yearSections',
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
