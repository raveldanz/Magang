<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2.5">
                    
                    <span>Pusat Pemberitahuan & Tindakan Sistem</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Pantau seluruh pembaruan sistem, notifikasi aktivitas terkini, dan tugas yang memerlukan tindakan segera
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <form action="{{ route('notifications.mark_all_read') }}" method="POST" class="inline-flex items-center m-0 p-0">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold shadow-2xs transition cursor-pointer"
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; line-height: 1 !important; box-sizing: border-box !important;">
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>

                <a href="{{ route('feedbacks.create') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition cursor-pointer"
                   style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; line-height: 1 !important; box-sizing: border-box !important; background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <span>Kirim Laporan Kendala</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Filter Navigation Tabs -->
            <div class="bg-white rounded-2xl border border-slate-100 p-2 shadow-2xs flex items-center gap-1.5 overflow-x-auto">
                <a href="{{ route('notifications.index', ['category' => 'all']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ ($category ?? 'all') === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                    Semua Pemberitahuan
                </a>

                <a href="{{ route('notifications.index', ['category' => 'urgent']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ ($category ?? '') === 'urgent' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                    Perlu Tindakan Segera
                </a>

                <a href="{{ route('notifications.index', ['category' => 'application']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ ($category ?? '') === 'application' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                    Pendaftaran
                </a>

                <a href="{{ route('notifications.index', ['category' => 'university']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ ($category ?? '') === 'university' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                    Universitas
                </a>

                <a href="{{ route('notifications.index', ['category' => 'feedback']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ ($category ?? '') === 'feedback' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                    Feedback
                </a>

                <a href="{{ route('notifications.index', ['category' => 'logbook']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ ($category ?? '') === 'logbook' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                    Logbook
                </a>

                @if(Auth::user() && (Auth::user()->role === 'super_admin' || Auth::user()->role === 'admin'))
                    <a href="{{ route('notifications.index', ['category' => 'audit']) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ ($category ?? '') === 'audit' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                         Audit
                    </a>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="space-y-3.5">
                @forelse($notifications as $notif)
                    @php
                        $isUrgent = ($notif['type'] ?? '') === 'urgent';
                        $isWarning = ($notif['type'] ?? '') === 'warning';
                        $isSuccess = ($notif['type'] ?? '') === 'success';

                        $cardBorder = $isUrgent ? 'border-amber-300 bg-amber-50/40' : ($isWarning ? 'border-amber-200 bg-white' : ($isSuccess ? 'border-emerald-200 bg-white' : 'border-slate-100 bg-white'));
                    @endphp

                    <div class="rounded-3xl border {{ $cardBorder }} p-5 sm:p-6 shadow-xs hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            

                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-extrabold text-sm sm:text-base text-gray-900">
                                        {{ $notif['title'] }}
                                    </h4>

                                    @if(!empty($notif['is_action_required']))
                                        <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-[10px] font-black uppercase tracking-wider animate-pulse">
                                            Perlu Tindakan
                                        </span>
                                    @endif

                                    @if(isset($notif['is_read']) && $notif['is_read'])
                                        <span class="text-[10px] text-slate-400 font-semibold">● Sudah dibaca</span>
                                    @endif
                                </div>

                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    {{ $notif['message'] }}
                                </p>

                                <div class="text-[11px] text-slate-400 font-medium pt-0.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>{{ $notif['time'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        @if(!empty($notif['action_url']))
                            <div class="shrink-0 flex items-center gap-2 sm:self-center">
                                <a href="{{ $notif['action_url'] }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 {{ $isUrgent ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white' }} text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                                    <span>{{ $notif['action_label'] ?? 'Tindak Lanjuti' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-16 text-center bg-white rounded-3xl border border-slate-100 p-8 space-y-3">
                        <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center text-3xl mx-auto">
                            🐾
                        </div>
                        <h4 class="font-bold text-base text-gray-800">Tidak Ada Pemberitahuan</h4>
                        <p class="text-xs text-gray-400 max-w-md mx-auto">
                            Semua tugas dan pemberitahuan sistem telah ditindaklanjuti. Notifikasi baru akan muncul otomatis saat ada aktivitas sistem.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
