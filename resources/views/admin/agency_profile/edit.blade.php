<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900">
                    Pengaturan Profil Instansi
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kelola identitas resmi instansi, logo, kontak, dan penandatangan sertifikat magang
                </p>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-xs space-y-1">
                    <p class="font-bold">Terjadi kesalahan validasi:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 p-6 sm:p-8">
                <form action="{{ route('admin.agency_profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Informasi Dasar Instansi -->
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                            <span>🏛️</span> Informasi Umum Instansi
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nama Instansi / Dinas -->
                            <div class="md:col-span-2">
                                <label for="agency_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Nama Instansi / Organisasi Perangkat Daerah
                                </label>
                                <input type="text" id="agency_name" name="agency_name" 
                                       value="{{ old('agency_name', $agencyProfile->agency_name ?? '') }}" 
                                       placeholder="Contoh: Dinas Komunikasi dan Informatika Kota Surabaya"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200" required>
                            </div>

                            <!-- Alamat Kantor -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Alamat Lengkap Kantor
                                </label>
                                <textarea id="address" name="address" rows="3" 
                                          placeholder="Jl. Jimerto No. 25-27, Ketabang, Kec. Genteng, Surabaya..."
                                          class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm p-3.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">{{ old('address', $agencyProfile->address ?? '') }}</textarea>
                            </div>

                            <!-- Email Resmi -->
                            <div>
                                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Email Resmi Instansi
                                </label>
                                <input type="email" id="email" name="email" 
                                       value="{{ old('email', $agencyProfile->email ?? '') }}" 
                                       placeholder="diskominfo@surabaya.go.id"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            </div>

                            <!-- Nomor Telepon -->
                            <div>
                                <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Nomor Telepon / Hotline
                                </label>
                                <input type="text" id="phone" name="phone" 
                                       value="{{ old('phone', $agencyProfile->phone ?? '') }}" 
                                       placeholder="(031) 5312144"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Logo & Dokumen Branding -->
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                            <span>🖼️</span> Logo Instansi & Kop Surat
                        </h3>

                        <div class="flex flex-col sm:flex-row items-start gap-6">
                            <!-- Preview Logo Saat Ini -->
                            <div class="flex flex-col items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-400">Logo Saat Ini:</span>
                                <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center p-2">
                                    @if(isset($agencyProfile->logo) && $agencyProfile->logo)
                                        <img src="{{ asset('storage/' . $agencyProfile->logo) }}" alt="Logo Instansi" class="max-h-full max-w-full object-contain">
                                    @else
                                        <img src="{{ asset('images/logoPemkotSBY.png') }}" alt="Logo Default" class="max-h-full max-w-full object-contain">
                                    @endif
                                </div>
                            </div>

                            <!-- Input Upload File -->
                            <div class="flex-1 w-full">
                                <label for="logo" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Ganti Logo Instansi
                                </label>
                                <input type="file" id="logo" name="logo" accept=".png,.jpg,.jpeg"
                                       class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50">
                                <p class="text-[11px] text-slate-400 mt-1.5">Rekomendasi format PNG transparan, ukuran maks. 2MB. Logo ini akan dicetak pada E-Sertifikat & laporan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Pengesahan & Tanda Tangan Sertifikat -->
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                            <span>✍️</span> Pejabat Penandatangan Sertifikat
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nama Kepala Dinas / Pejabat -->
                            <div>
                                <label for="head_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Nama Lengkap Pejabat
                                </label>
                                <input type="text" id="head_name" name="head_name" 
                                       value="{{ old('head_name', $agencyProfile->head_name ?? '') }}" 
                                       placeholder="Nama beserta gelar akademik..."
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            </div>

                            <!-- NIP Pejabat -->
                            <div>
                                <label for="head_nip" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    NIP Pejabat
                                </label>
                                <input type="text" id="head_nip" name="head_nip" 
                                       value="{{ old('head_nip', $agencyProfile->head_nip ?? '') }}" 
                                       placeholder="19xxxxxxxxxxxxxx"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            </div>

                            <!-- Jabatan Resmi -->
                            <div class="md:col-span-2">
                                <label for="head_position" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Jabatan Resmi
                                </label>
                                <input type="text" id="head_position" name="head_position" 
                                       value="{{ old('head_position', $agencyProfile->head_position ?? '') }}" 
                                       placeholder="Contoh: Kepala Dinas Komunikasi dan Informatika Kota Surabaya"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Submit -->
                    <div class="flex items-center gap-3 pt-6 border-t border-slate-100">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-semibold uppercase tracking-wider shadow-sm shadow-blue-200 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 cursor-pointer">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>