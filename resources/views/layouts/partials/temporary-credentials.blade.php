{{-- Password sementara hasil buat/reset akun oleh staf. Hanya tampil sekali (session flash). --}}
@if (session('temporary_credentials'))
    <aside aria-label="Password Sementara" class="bg-sky-50 border-b border-sky-300 w-full" x-data="{ open: true }" x-show="open">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 space-y-2">
            <div class="flex items-start justify-between gap-3">
                <p class="text-xs sm:text-sm text-sky-900">
                    <strong>Password sementara</strong> — catat dan serahkan langsung kepada pemilik akun.
                    Password ini hanya ditampilkan <strong>sekali</strong> dan wajib diganti pemilik saat login pertama.
                </p>
                <button type="button" @click="open = false" class="text-sky-700 hover:text-sky-900 text-sm px-1" aria-label="Tutup">✕</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-left text-sky-800">
                            <th class="py-1 pr-4 font-semibold">Nama</th>
                            <th class="py-1 pr-4 font-semibold">Email</th>
                            <th class="py-1 font-semibold">Password Sementara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (session('temporary_credentials') as $credential)
                            <tr class="border-t border-sky-200">
                                <td class="py-1 pr-4 text-slate-800">{{ $credential['name'] }}</td>
                                <td class="py-1 pr-4 text-slate-800 select-all">{{ $credential['email'] }}</td>
                                <td class="py-1 font-mono font-bold text-slate-900 select-all">{{ $credential['password'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </aside>
@endif
