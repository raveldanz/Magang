<!-- Internship Dashboard -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Internship Hub - Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "text-muted": "#64748B",
                        "on-tertiary-fixed-variant": "#5a4224",
                        "inverse-primary": "#b7c8e1",
                        "primary-fixed-dim": "#b7c8e1",
                        "surface-dim": "#d8dadc",
                        "primary-container": "#64748b",
                        "surface-variant": "#e0e3e5",
                        "input-bg": "#F1F5F9",
                        "surface-container-highest": "#e0e3e5",
                        "on-tertiary-container": "#fff9f5",
                        "on-secondary": "#ffffff",
                        "surface-container-low": "#f2f4f6",
                        "on-surface-variant": "#44474c",
                        "surface-bright": "#f7f9fb",
                        "on-secondary-container": "#00714d",
                        "primary": "#4c5b71",
                        "surface-container-high": "#e6e8ea",
                        "on-primary": "#ffffff",
                        "on-secondary-fixed": "#002113",
                        "on-error-container": "#93000a",
                        "border-subtle": "#E2E8F0",
                        "on-secondary-fixed-variant": "#005236",
                        "outline-variant": "#c4c6cd",
                        "on-surface": "#191c1e",
                        "error": "#ba1a1a",
                        "secondary-fixed-dim": "#4edea3",
                        "secondary-container": "#6cf8bb",
                        "surface": "#FFFFFF",
                        "inverse-surface": "#2d3133",
                        "surface-container": "#eceef0",
                        "inverse-on-surface": "#eff1f3",
                        "on-tertiary-fixed": "#2a1801",
                        "text-main": "#0F172A",
                        "tertiary-fixed": "#ffddb6",
                        "background": "#f7f9fb",
                        "on-primary-fixed": "#0b1c30",
                        "surface-tint": "#505f76",
                        "on-background": "#191c1e",
                        "error-container": "#ffdad6",
                        "secondary-fixed": "#6ffbbe",
                        "on-error": "#ffffff",
                        "primary-fixed": "#d3e4fe",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed-variant": "#38485d",
                        "surface-container-lowest": "#ffffff",
                        "outline": "#74777d",
                        "on-primary-container": "#f9f9ff",
                        "tertiary": "#6f5636",
                        "tertiary-fixed-dim": "#e3c199",
                        "tertiary-container": "#8a6e4c",
                        "secondary": "#006c49"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "base": "4px",
                        "md": "24px",
                        "xl": "48px",
                        "lg": "32px",
                        "margin-desktop": "40px",
                        "sm": "16px",
                        "xs": "8px",
                        "margin-mobile": "16px",
                        "gutter": "24px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "headline-lg": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "label-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "label-caps": ["Inter"]
                    },
                    "fontSize": {
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "label-md": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-caps": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen font-body-md text-body-md">
<!-- TopAppBar -->
<header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-md h-16 bg-surface dark:bg-inverse-surface border-b border-border-subtle dark:border-outline-variant shadow-sm docked full-width top-0">
    <div class="flex items-center gap-sm">
        <button class="md:hidden text-primary dark:text-inverse-primary hover:bg-surface-container-low dark:hover:bg-surface-variant transition-colors p-xs rounded-full">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h1 class="text-headline-sm font-headline-sm font-bold text-primary dark:text-inverse-primary">Internship Hub</h1>
    </div>
    <div class="flex items-center gap-md">
        <div class="hidden sm:flex items-center gap-xs">
            <button aria-label="notifications" class="text-text-muted dark:text-surface-variant hover:bg-surface-container-low dark:hover:bg-surface-variant transition-colors active:scale-95 duration-150 p-xs rounded-full">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <button aria-label="settings" class="text-text-muted dark:text-surface-variant hover:bg-surface-container-low dark:hover:bg-surface-variant transition-colors active:scale-95 duration-150 p-xs rounded-full">
                <span class="material-symbols-outlined">settings</span>
            </button>
        </div>
        <img alt="Student avatar" class="w-10 h-10 rounded-full object-cover border border-border-subtle" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAmPpJso5WVHM2AANBb2cr4eUDqVZhtBiVXl4ctZevMdz9QUSHjZl_gdC6CjvySDBCE0Fa4e13Dl0lUYHjvlLlNsZfM4Ah5PKdPddTS_kJcTDvwXdNoe-fn9eiyk_SWdzZ4zKtnyRqYHL5hGY9Bfg3ChWKCcjKXIBd2UeLmP5tzwmqehFAzJUIVAT0By2r7pNAfAEKyTg3RCNUXBhZ38OX6xa-UTVAFXisCSMaSEPQu1ar9Pph8uXaFiQ"/>
    </div>
</header>

<!-- SideNavBar & Main Content Wrapper -->
<div class="flex pt-16 h-screen">
    <!-- SideNavBar (Desktop) -->
    <nav class="hidden md:flex fixed left-0 top-16 h-[calc(100vh-64px)] flex-col p-sm bg-surface dark:bg-inverse-surface border-r border-border-subtle dark:border-outline-variant docked left-0 h-full w-64 z-40 overflow-y-auto">
        <div class="mb-lg px-sm pt-md">
            <h2 class="font-headline-md text-headline-md text-primary truncate">{{ Auth::user()->name }}</h2>
            <p class="font-body-sm text-body-sm text-text-muted mt-base truncate">{{ $studentProfile->jurusan ?? 'Belum melengkapi profil' }}</p>
            <p class="font-body-sm text-body-sm text-text-muted truncate">NIM: {{ $studentProfile->nim ?? '-' }}</p>
            
            <div class="mt-sm">
                @if($application)
                    @if(strtolower($application->status) === 'accepted')
                        <span class="inline-flex items-center gap-xs bg-secondary-container/30 text-secondary px-sm py-xs rounded-full font-label-md text-label-md">
                            <span class="w-2 h-2 rounded-full bg-secondary"></span>
                            Magang Aktif
                        </span>
                    @elseif(strtolower($application->status) === 'pending')
                        <span class="inline-flex items-center gap-xs bg-amber-100 text-amber-700 px-sm py-xs rounded-full font-label-md text-label-md">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Menunggu Review
                        </span>
                    @elseif(strtolower($application->status) === 'verified')
                        <span class="inline-flex items-center gap-xs bg-blue-100 text-blue-700 px-sm py-xs rounded-full font-label-md text-label-md">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Terverifikasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-xs bg-red-100 text-red-700 px-sm py-xs rounded-full font-label-md text-label-md">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Pengajuan Ditolak
                        </span>
                    @endif
                @else
                    <span class="inline-flex items-center gap-xs bg-slate-100 text-slate-600 px-sm py-xs rounded-full font-label-md text-label-md">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        Belum Mengajukan
                    </span>
                @endif
            </div>
        </div>
        
        <div class="flex-1 space-y-xs">
            <a class="flex items-center gap-sm px-sm py-sm bg-secondary-container dark:bg-secondary text-on-secondary-container dark:text-on-secondary rounded-xl font-semibold hover:translate-x-1 duration-200" href="{{ route('student.dashboard') }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Overview</span>
            </a>
            <a class="flex items-center gap-sm px-sm py-sm text-text-muted dark:text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-surface-variant transition-all rounded-xl hover:translate-x-1 duration-200" href="{{ route('student.logbook.index') }}">
                <span class="material-symbols-outlined">auto_stories</span>
                <span>Logbooks</span>
            </a>
            <a class="flex items-center gap-sm px-sm py-sm text-text-muted dark:text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-surface-variant transition-all rounded-xl hover:translate-x-1 duration-200" href="{{ route('student.final_report.index') }}">
                <span class="material-symbols-outlined">description</span>
                <span>Reports</span>
            </a>
            <a class="flex items-center gap-sm px-sm py-sm text-text-muted dark:text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-surface-variant transition-all rounded-xl hover:translate-x-1 duration-200" 
               @if($certificate) href="{{ route('student.certificate.download', $placement->id) }}" @else href="#" onclick="alert('Sertifikat belum dapat diunduh. Laporan akhir harus disetujui dan nilai evaluasi telah diberikan.'); return false;" @endif>
                <span class="material-symbols-outlined">workspace_premium</span>
                <span>Certificates</span>
            </a>
        </div>
        
        <div class="mt-auto space-y-sm pt-md border-t border-border-subtle">
            <a href="@if($placement) {{ route('student.logbook.create') }} @else # @endif" 
               @if(!$placement) onclick="alert('Menu pengisian logbook hanya tersedia setelah pengajuan magang disetujui.'); return false;" @endif
               class="w-full bg-primary text-on-primary py-sm rounded-xl font-label-md text-label-md hover:bg-surface-tint transition-colors shadow-sm flex items-center justify-center">
                Submit Daily Log
            </a>
            <div class="space-y-xs mt-sm">
                <a class="flex items-center gap-sm px-sm py-xs text-text-muted dark:text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-surface-variant transition-all rounded-xl" href="#">
                    <span class="material-symbols-outlined">help</span>
                    <span class="font-label-md text-label-md">Help Center</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form-desktop" style="display: none;">
                    @csrf
                </form>
                <a class="flex items-center gap-sm px-sm py-xs text-error hover:bg-error-container/20 transition-all rounded-xl" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-desktop').submit();">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-label-md text-label-md">Sign Out</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Canvas -->
    <main class="flex-1 md:ml-64 p-margin-mobile md:p-margin-desktop overflow-y-auto w-full">
        <div class="max-w-7xl mx-auto space-y-lg">
            <!-- Alert Session Notification -->
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 mb-4 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-4 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Page Header -->
            <div class="mb-lg">
                <h2 class="font-headline-lg text-headline-lg font-bold text-text-main">Welcome back, {{ Auth::user()->name }}</h2>
                <p class="text-text-muted font-body-sm text-body-sm">Here's your internship progress dashboard.</p>
            </div>

            <!-- Bento Grid: Metrics & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
                <!-- Metrics Cluster (Spans 8 cols on desktop) -->
                <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-sm">
                    <!-- Metric 1: Total Logbooks -->
                    <div class="bg-surface rounded-[24px] p-md border border-border-subtle shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between min-h-[140px]">
                        <div class="flex justify-between items-start">
                            <p class="font-label-caps text-label-caps text-text-muted uppercase tracking-wider">Total Logbooks</p>
                            <div class="w-8 h-8 rounded-full bg-surface-container-low flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-sm">book</span>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <h3 class="font-headline-lg text-headline-lg text-text-main">{{ $logbooks->count() }}</h3>
                            <p class="font-label-md text-label-md text-secondary mt-xs flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">trending_up</span> On track
                            </p>
                        </div>
                    </div>
                    
                    <!-- Metric 2: Approved Logs -->
                    <div class="bg-surface rounded-[24px] p-md border border-border-subtle shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between min-h-[140px]">
                        <div class="flex justify-between items-start">
                            <p class="font-label-caps text-label-caps text-text-muted uppercase tracking-wider">Approved Logs</p>
                            <div class="w-8 h-8 rounded-full bg-secondary-container/20 flex items-center justify-center text-secondary">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <h3 class="font-headline-lg text-headline-lg text-text-main">{{ $logbooks->where('status', 'approved')->count() }}</h3>
                            <div class="w-full bg-surface-container-low rounded-full h-1.5 mt-sm overflow-hidden">
                                <div class="bg-secondary h-1.5 rounded-full" style="width: {{ $logbooks->count() > 0 ? ($logbooks->where('status', 'approved')->count() / $logbooks->count()) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Metric 3: Final Report -->
                    <div class="bg-surface rounded-[24px] p-md border border-border-subtle shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between min-h-[140px]">
                        <div class="flex justify-between items-start">
                            <p class="font-label-caps text-label-caps text-text-muted uppercase tracking-wider">Final Report</p>
                            <div class="w-8 h-8 rounded-full bg-surface-container-low flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-sm">assignment</span>
                            </div>
                        </div>
                        <div class="mt-auto">
                            @if($finalReport)
                                <span class="inline-flex items-center px-sm py-xs rounded-full font-label-md text-label-md
                                    @if(strtolower($finalReport->status) === 'approved') bg-emerald-100 text-emerald-800
                                    @elseif(strtolower($finalReport->status) === 'rejected') bg-red-100 text-red-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ ucfirst($finalReport->status) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-sm py-xs rounded-full bg-surface-container-high text-on-surface-variant font-label-md text-label-md">
                                    Belum Diunggah
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Metric 4: Certificate -->
                    <div class="bg-surface rounded-[24px] p-md border border-border-subtle shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between min-h-[140px]">
                        <div class="flex justify-between items-start">
                            <p class="font-label-caps text-label-caps text-text-muted uppercase tracking-wider">Certificate</p>
                            <div class="w-8 h-8 rounded-full bg-surface-container-low flex items-center justify-center text-text-muted">
                                <span class="material-symbols-outlined text-sm">workspace_premium</span>
                            </div>
                        </div>
                        <div class="mt-auto">
                            @if($certificate)
                                <span class="inline-flex items-center px-sm py-xs rounded-full bg-emerald-100 text-emerald-800 font-label-md text-label-md">
                                    Siap Diunduh
                                </span>
                            @else
                                <span class="inline-flex items-center px-sm py-xs rounded-full bg-surface-container-highest text-text-muted font-label-md text-label-md opacity-70">
                                    Belum Tersedia
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions Panel (Spans 4 cols on desktop) -->
                <div class="lg:col-span-4 bg-surface rounded-[24px] p-md border border-border-subtle shadow-[0_4px_12px_rgba(100,116,139,0.08)] flex flex-col gap-sm">
                    <h3 class="font-headline-sm text-headline-sm text-text-main mb-xs">Quick Actions</h3>
                    
                    @if($placement)
                        <a href="{{ route('student.logbook.create') }}" class="w-full flex items-center justify-between bg-primary text-on-primary py-sm px-md rounded-xl font-body-md text-body-md hover:bg-surface-tint transition-colors shadow-sm group">
                            <span>Fill Daily Logbook</span>
                            <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    @else
                        <button onclick="alert('Menu pengisian logbook hanya tersedia setelah pengajuan magang disetujui.');" class="w-full flex items-center justify-between bg-slate-200 text-slate-500 py-sm px-md rounded-xl font-body-md text-body-md cursor-not-allowed">
                            <span>Fill Daily Logbook</span>
                            <span class="material-symbols-outlined opacity-50">arrow_forward</span>
                        </button>
                    @endif

                    <a href="{{ route('student.final_report.index') }}" class="w-full flex items-center justify-between bg-surface border border-border-subtle text-primary py-sm px-md rounded-xl font-body-md text-body-md hover:bg-surface-container-low transition-colors group">
                        <span>Upload Final Report</span>
                        <span class="material-symbols-outlined">upload_file</span>
                    </a>

                    @if($certificate)
                        <a href="{{ route('student.certificate.download', $placement->id) }}" class="w-full flex items-center justify-between bg-emerald-600 text-white py-sm px-md rounded-xl font-body-md text-body-md hover:bg-emerald-700 transition-colors shadow-sm group">
                            <span>Download Certificate</span>
                            <span class="material-symbols-outlined">download</span>
                        </a>
                    @else
                        <button disabled class="w-full flex items-center justify-between bg-surface-container-low text-text-muted py-sm px-md rounded-xl font-body-md text-body-md cursor-not-allowed opacity-60">
                            <span>Download Certificate</span>
                            <span class="material-symbols-outlined">download</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Recent Activity Table Section -->
            <div class="bg-surface rounded-[24px] border border-border-subtle shadow-[0_4px_12px_rgba(100,116,139,0.08)] overflow-hidden">
                <div class="p-md border-b border-border-subtle flex justify-between items-center bg-surface-bright">
                    <h3 class="font-headline-sm text-headline-sm text-text-main">Recent Logbook Activity</h3>
                    <a href="{{ route('student.logbook.index') }}" class="text-primary font-label-md text-label-md hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-subtle bg-surface-container-lowest">
                                <th class="py-sm px-md font-label-caps text-label-caps text-text-muted uppercase font-medium">Date</th>
                                <th class="py-sm px-md font-label-caps text-label-caps text-text-muted uppercase font-medium">Activity Summary</th>
                                <th class="py-sm px-md font-label-caps text-label-caps text-text-muted uppercase font-medium">Status</th>
                                <th class="py-sm px-md font-label-caps text-label-caps text-text-muted uppercase font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-sm text-body-sm text-on-surface">
                            @forelse($logbooks->take(5) as $logbook)
                                <tr class="border-b border-border-subtle hover:bg-surface-container-lowest transition-colors group">
                                    <td class="py-sm px-md whitespace-nowrap text-text-muted">
                                        {{ \Carbon\Carbon::parse($logbook->date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-sm px-md font-medium">{{ Str::limit($logbook->activity, 80) }}</td>
                                    <td class="py-sm px-md">
                                        @if(strtolower($logbook->status) === 'approved')
                                            <span class="inline-flex items-center px-xs py-0.5 rounded-full bg-secondary-container/30 text-secondary text-xs font-semibold">
                                                Approved
                                            </span>
                                        @elseif(strtolower($logbook->status) === 'rejected')
                                            <span class="inline-flex items-center px-xs py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-xs py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs font-semibold">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-sm px-md text-right">
                                        @if(strtolower($logbook->status) !== 'approved')
                                            <a href="{{ route('student.logbook.edit', $logbook->id) }}" class="text-text-muted hover:text-primary transition-colors p-xs rounded-full hover:bg-surface-container-low">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </a>
                                        @else
                                            <span class="text-text-muted p-xs">
                                                <span class="material-symbols-outlined text-[20px] opacity-50">visibility</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-sm px-md text-center text-text-muted py-8">
                                        No recent logbook entries found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Mobile Bottom Navigation (Visible only on md:hidden) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full bg-surface border-t border-border-subtle flex justify-around items-center h-16 z-50 pb-safe">
    <a class="flex flex-col items-center justify-center w-full h-full text-secondary" href="{{ route('student.dashboard') }}">
        <div class="px-4 py-1 bg-secondary-container rounded-full mb-1">
            <span class="material-symbols-outlined text-on-secondary-container">dashboard</span>
        </div>
        <span class="text-[10px] font-bold text-on-surface">Overview</span>
    </a>
    <a class="flex flex-col items-center justify-center w-full h-full text-text-muted hover:text-primary transition-colors" href="{{ route('student.logbook.index') }}">
        <span class="material-symbols-outlined">auto_stories</span>
        <span class="text-[10px] font-medium mt-1">Logbooks</span>
    </a>
    <a class="flex flex-col items-center justify-center w-full h-full text-text-muted hover:text-primary transition-colors" href="{{ route('student.final_report.index') }}">
        <span class="material-symbols-outlined">description</span>
        <span class="text-[10px] font-medium mt-1">Reports</span>
    </a>
    <a class="flex flex-col items-center justify-center w-full h-full text-text-muted hover:text-primary transition-colors" 
       @if($certificate) href="{{ route('student.certificate.download', $placement->id) }}" @else href="#" onclick="alert('Sertifikat belum tersedia.'); return false;" @endif>
        <span class="material-symbols-outlined">workspace_premium</span>
        <span class="text-[10px] font-medium mt-1">Certificates</span>
    </a>
</nav>

<style>
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom);
    }
</style>
</body>
</html>
