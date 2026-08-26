<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    /**
     * Dashboard Portal Resmi Universitas
     * Menampilkan total mahasiswa magang asal kampus, status, dan sebaran dinas penempatan
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cari master universitas terkait
        $university = $user->university_id 
            ? University::find($user->university_id) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();

        // 1. Query Mahasiswa yang Memiliki Penempatan (Placement)
        $placementsQuery = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'academicAdvisor',
            'logbooks',
            'finalreport',
            'evaluation'
        ])->whereHas('application.user', function ($uq) use ($user, $university) {
            if ($user->university_id) {
                $uq->where('university_id', $user->university_id);
            } elseif ($university) {
                $uq->where('university', $university->name);
            }
        });

        // 2. Query Semua Pengajuan Mahasiswa Asal Kampus Ini
        $applicationsQuery = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
            'placement.evaluation'
        ])->whereHas('user', function ($uq) use ($user, $university) {
            if ($user->university_id) {
                $uq->where('university_id', $user->university_id);
            } elseif ($university) {
                $uq->where('university', $university->name);
            }
        });

        // Filter Search & Agency
        if ($request->filled('search')) {
            $search = $request->search;
            $applicationsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->where('nim', 'like', "%{$search}%")
                         ->orWhere('jurusan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('agency_id')) {
            $applicationsQuery->whereHas('unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $allApplications = $applicationsQuery->latest()->get();
        $allPlacements = $placementsQuery->latest()->get();

        // Metrik Statistik Kampus
        $totalStudents = $allApplications->count();
        $totalAccepted = $allApplications->where('status', 'accepted')->count();
        $totalCompleted = $allPlacements->filter(function ($p) {
            return optional($p->finalreport)->status === 'approved' && optional($p->evaluation)->nilai_akademik > 0;
        })->count();
        $totalPending = $allApplications->where('status', 'pending')->count();

        // Sebaran Dinas / Instansi Penempatan
        $agencies = AgencyProfile::all();
        $agencyDistribution = [];

        foreach ($agencies as $agency) {
            $count = $allApplications->filter(function ($app) use ($agency) {
                return optional($app->unit)->agency_profile_id === $agency->id;
            })->count();

            $agencyDistribution[] = [
                'id' => $agency->id,
                'name' => $agency->agency_name,
                'count' => $count,
                'percentage' => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0,
            ];
        }

        // Daftar Dosen Aktif Kampus untuk Plotting DPL (Hanya dosen dengan status aktif)
        $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->where(function ($q) use ($user, $university) {
                if ($user->university_id) {
                    $q->where('university_id', $user->university_id);
                } elseif ($university) {
                    $q->where('university', $university->name);
                }
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->orderBy('name')
            ->get();

        $stats = [
            'total_students' => $totalStudents,
            'total_accepted' => $totalAccepted,
            'total_completed' => $totalCompleted,
            'total_pending' => $totalPending,
        ];

        return view('university.dashboard', compact(
            'user',
            'university',
            'allApplications',
            'stats',
            'agencyDistribution',
            'agencies',
            'availableDosens'
        ));
    }

    /**
     * Plotting / Penugasan Dosen Pembimbing oleh Universitas
     */
    public function assignAdvisor(Request $request, $applicationId)
    {
        $user = Auth::user();
        $universityId = $user->university_id;
        $university = $universityId 
            ? University::find($universityId) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();

        $request->validate([
            'academic_advisor_id' => 'required|exists:users,id',
        ], [
            'academic_advisor_id.required' => 'Silakan pilih Dosen Pembimbing Lapangan.',
        ]);

        $application = Application::with('user.studentProfile')->findOrFail($applicationId);
        $student = $application->user;

        // Pastikan mahasiswa berasal dari universitas yang sama
        $isSameUniv = ($universityId && $student->university_id === $universityId);
        if (!$isSameUniv && $university) {
            $isSameUniv = ($student->university === $university->name || optional($student->studentProfile)->universitas === $university->name);
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk memplot dosen mahasiswa kampus lain.');
        }

        // Pastikan dosen yang dipilih berasal dari universitas yang sama
        $advisor = User::whereIn('role', ['dosen', 'academic_advisor'])->findOrFail($request->academic_advisor_id);
        $isAdvisorSameUniv = ($universityId && $advisor->university_id === $universityId);
        if (!$isAdvisorSameUniv && $university) {
            $isAdvisorSameUniv = ($advisor->university === $university->name);
        }

        if (!$isAdvisorSameUniv) {
            return redirect()->back()->with('error', 'Dosen yang dipilih harus terdaftar di perguruan tinggi Anda.');
        }

        $placement = Placement::updateOrCreate(
            ['application_id' => $application->id],
            ['academic_advisor_id' => $advisor->id]
        );

        // Hapus penempatan duplikat jika ada
        Placement::where('application_id', $application->id)
            ->where('id', '!=', $placement->id)
            ->delete();

        return redirect()->back()->with('success', "Dosen Pembimbing Lapangan ({$advisor->name}) berhasil ditugaskan untuk mahasiswa {$student->name}!");
    }

    /**
     * Export Rekapitulasi Data & Nilai Mahasiswa Magang ke CSV/Excel
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $university = $user->university_id 
            ? University::find($user->university_id) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();
        $univName = $university?->name ?? $user->university ?? 'Universitas';

        // Query Semua Pengajuan Mahasiswa Asal Kampus Ini
        $applications = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
            'placement.evaluation'
        ])->whereHas('user', function ($uq) use ($user, $university) {
            if ($user->university_id) {
                $uq->where('university_id', $user->university_id);
            } elseif ($university) {
                $uq->where('university', $university->name);
            }
        })
        ->latest()
        ->get();

        $cleanUnivName = preg_replace('/[^A-Za-z0-9_]/', '_', $univName);
        $filename = 'Rekap_Mahasiswa_Magang_' . $cleanUnivName . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($applications) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Kolom
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Program Studi',
                'Instansi Magang',
                'Unit / Bidang Kerja',
                'Dosen Pembimbing (DPL)',
                'Mentor Dinas',
                'Periode Magang',
                'Status Magang',
                'Nilai Mentor',
                'Nilai Dosen',
                'Nilai Akhir',
            ]);

            $no = 1;
            foreach ($applications as $app) {
                $student = $app->user;
                $profile = $student?->studentProfile;
                $placement = $app->placement;
                $dosen = $placement?->academicAdvisor;
                $mentor = $placement?->mentor ?? $placement?->pembimbing;
                $eval = $placement?->evaluation;

                $mentorScore = ($eval && $eval->nilai_pembimbing) ? number_format($eval->nilai_pembimbing, 2) : '-';
                $dosenScore = ($eval && $eval->nilai_akademik) ? number_format($eval->nilai_akademik, 2) : '-';
                
                $finalScore = '-';
                if ($eval && $eval->nilai_pembimbing > 0 && $eval->nilai_akademik > 0) {
                    $weighted = ($eval->nilai_pembimbing * 0.4) + ($eval->nilai_akademik * 0.6);
                    $finalScore = number_format($weighted, 2);
                } elseif ($eval && $eval->nilai_akademik > 0) {
                    $finalScore = number_format($eval->nilai_akademik, 2);
                }

                $periode = ($app->start_date && $app->end_date)
                    ? date('d/m/Y', strtotime($app->start_date)) . ' s.d. ' . date('d/m/Y', strtotime($app->end_date))
                    : '-';

                fputcsv($handle, [
                    $no++,
                    $profile?->nim ?? '-',
                    $student?->name ?? '-',
                    $profile?->jurusan ?? '-',
                    $app->unit?->agencyProfile?->agency_name ?? '-',
                    $app->unit?->name ?? '-',
                    $dosen?->name ?? 'Belum Ditentukan',
                    $mentor?->name ?? 'Belum Diplot',
                    $periode,
                    strtoupper($app->lifecycle_status ?? $app->status),
                    $mentorScore,
                    $dosenScore,
                    $finalScore,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Detail Mahasiswa Kampus untuk Pemantauan Universitas (View Khusus Universitas)
     */
    public function showStudent($id)
    {
        $user = Auth::user();
        $university = $user->university_id 
            ? University::find($user->university_id) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();

        // 1. Prioritaskan mencari Application berdasarkan ID pengajuan
        $application = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
            'placement.logbooks' => function ($q) {
                $q->orderBy('date', 'desc');
            },
            'placement.finalreport',
            'placement.evaluation'
        ])->find($id);

        if ($application) {
            $student = $application->user;
            $placement = $application->placement;
        } else {
            // 2. Fallback: Cari jika ID yang dikirim adalah ID penempatan (placement_id)
            $placement = Placement::with([
                'application.user.studentProfile',
                'application.unit.agencyProfile',
                'mentor',
                'pembimbing',
                'academicAdvisor',
                'logbooks' => function ($q) {
                    $q->orderBy('date', 'desc');
                },
                'finalreport',
                'evaluation'
            ])->findOrFail($id);

            $application = $placement->application;
            $student = $application->user;
        }

        // Otorisasi: Pastikan mahasiswa berasal dari universitas yang sama
        $isSameUniv = false;

        if ($user->university_id && $student->university_id && (int)$user->university_id === (int)$student->university_id) {
            $isSameUniv = true;
        }

        if (!$isSameUniv && $university) {
            $studentUniv = strtolower(trim($student->university ?? ''));
            $studentProfileUniv = strtolower(trim(optional($student->studentProfile)->universitas ?? ''));
            $targetUnivName = strtolower(trim($university->name ?? ''));
            $targetUnivCode = strtolower(trim($university->code ?? ''));

            if (
                ($studentUniv && ($studentUniv === $targetUnivName || $studentUniv === $targetUnivCode)) ||
                ($studentProfileUniv && ($studentProfileUniv === $targetUnivName || $studentProfileUniv === $targetUnivCode)) ||
                ($targetUnivName && (str_contains($studentUniv, $targetUnivName) || str_contains($targetUnivName, $studentUniv))) ||
                ($targetUnivCode && (str_contains($studentUniv, $targetUnivCode) || str_contains($targetUnivCode, $studentUniv)))
            ) {
                $isSameUniv = true;
                if (!$student->university_id && $university->id) {
                    $student->update(['university_id' => $university->id]);
                }
            }
        }

        if (!$isSameUniv && $user->university) {
            $userUniv = strtolower(trim($user->university));
            $studentUniv = strtolower(trim($student->university ?? ''));
            $studentProfileUniv = strtolower(trim(optional($student->studentProfile)->universitas ?? ''));

            if ($studentUniv === $userUniv || $studentProfileUniv === $userUniv || str_contains($studentUniv, $userUniv) || str_contains($userUniv, $studentUniv)) {
                $isSameUniv = true;
            }
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data mahasiswa kampus lain.');
        }

        $profile = $student->studentProfile;
        $unit = $application->unit;
        $agencyProfile = $unit?->agencyProfile;
        $mentor = $placement?->mentor ?? $placement?->pembimbing;
        $dosen = $placement?->academicAdvisor;
        $logbooks = $placement ? $placement->logbooks : collect();
        $finalReport = $placement?->finalreport;
        $evaluation = $placement?->evaluation;

        // Daftar Dosen untuk modal Plotting (Hanya dosen aktif)
        $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->where(function ($q) use ($user, $university) {
                if ($user->university_id) {
                    $q->where('university_id', $user->university_id);
                } elseif ($university) {
                    $q->where('university', $university->name);
                }
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->orderBy('name')
            ->get();

        return view('university.students.show', compact(
            'application',
            'placement',
            'student',
            'profile',
            'unit',
            'agencyProfile',
            'mentor',
            'dosen',
            'logbooks',
            'finalReport',
            'evaluation',
            'availableDosens',
            'university'
        ));
    }
}
