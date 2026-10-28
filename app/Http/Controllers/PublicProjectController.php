<?php

namespace App\Http\Controllers;

use App\Models\InternshipProject;
use App\Models\Unit;
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
        $isPrivileged = $user && ($user->isSuperAdmin() || in_array($user->role, ['admin', 'supervisor']));

        $query = InternshipProject::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ]);

        // Pengunjung umum hanya melihat proyek bertanda public (is_public = true)
        if (! $isPrivileged) {
            $query->where('is_public', true);
        }

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

        $projects = $query->get();

        // 5. Kelompokkan Proyek Berdasarkan Tahun untuk Tampilan Timeline Estetik
        $groupedProjects = $projects->groupBy(function ($item) {
            return $item->created_at ? $item->created_at->format('Y') : date('Y');
        });

        // 6. Ambil Daftar Tahun Unik untuk Pilihan Filter
        $yearExpression = $driver === 'sqlite' 
            ? "strftime('%Y', created_at) as project_year" 
            : 'EXTRACT(YEAR FROM created_at) as project_year';

        $availableYears = InternshipProject::selectRaw($yearExpression)
            ->distinct()
            ->orderBy('project_year', 'desc')
            ->pluck('project_year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) date('Y')]);
        }

        // Metrik Statistik Ringkas Galeri
        $stats = [
            'total_projects' => InternshipProject::where('is_public', true)->count(),
            'total_likes' => (int) InternshipProject::where('is_public', true)->sum('likes_count'),
            'total_comments' => (int) InternshipProject::where('is_public', true)->sum('comments_count'),
            'total_categories' => count(InternshipProject::CATEGORIES),
        ];

        return view('projects.index', compact(
            'groupedProjects',
            'availableYears',
            'stats',
            'search',
            'category',
            'year',
            'sort'
        ));
    }
}
