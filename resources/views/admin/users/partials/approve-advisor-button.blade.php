{{-- Tombol Setujui akun Pembimbing daftar mandiri (Super Admin). Variabel: $advisor, $fullWidth (opsional). --}}
<button type="button"
        @click="$dispatch('open-confirm-modal', {
            action: @js(route('admin.users.approve_advisor', $advisor->id)),
            title: 'Setujui Akun Pembimbing',
            message: 'Setelah disetujui, peserta magang dari institusi ini dapat memilih akun ini sebagai pembimbing.',
            label: 'Pembimbing:',
            name: @js((string) $advisor->name),
            desc: @js(trim(($advisor->university ?: 'Institusi belum diisi').' · '.$advisor->email)),
            confirmText: 'Ya, Setujui',
            tone: 'emerald'
        })"
        class="{{ ($fullWidth ?? false) ? 'w-full min-h-[44px] py-2 px-3' : 'px-3 py-1.5' }} inline-flex items-center justify-center gap-1.5 text-xs font-bold rounded-lg border transition-all duration-150 bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-700 cursor-pointer"
        title="Setujui akun pembimbing ini">
    Setujui Pembimbing
</button>
