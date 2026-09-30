{{-- Gaya daftar detail di halaman verifikasi publik (sertifikat & surat). Tanpa perlu build ulang CSS. --}}
@once
<style>
    .vlist { margin-top: .25rem; }
    .vrow { display: grid; grid-template-columns: 1fr; gap: 2px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
    .vrow:last-child { border-bottom: 0; }
    /* Kartu verifikasi sempit (max-w-md), jadi label selalu di atas nilai agar tidak terpotong-potong */
    .vlabel { font-size: 12.5px; font-weight: 500; color: #64748b; }
    .vvalue { font-size: 14px; font-weight: 600; color: #0f172a; line-height: 1.45; overflow-wrap: anywhere; }
    .vvalue.is-accent { color: #1d4ed8; }
    .vvalue.is-green { color: #047857; }
    .vvalue.is-score { color: #059669; font-size: 16px; font-weight: 800; }
    .vvalue.is-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .vsub { display: block; margin-top: 2px; font-size: 12px; font-weight: 500; color: #64748b; }
    .vcode { display: flex; flex-wrap: wrap; gap: 4px; }
    .vcode > span {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px; font-weight: 600; color: #334155;
        background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 1px 6px;
    }
    /* Kotak status verifikasi (SAH / TIDAK BERLAKU / BELUM TERBIT) */
    .vstatus { display: flex !important; align-items: flex-start !important; gap: 14px; }
    .vstatus > * + * { margin-left: 0 !important; }
    .vstatus-icon { flex: none; }
    .vbadge {
        display: inline-block !important; margin-bottom: 6px !important; padding: 3px 8px !important;
        border-radius: 6px !important; font-size: 10px !important; font-weight: 800 !important;
        letter-spacing: .05em; line-height: 1.35 !important;
    }
    .vstatus h3 { font-size: 15px !important; line-height: 1.3 !important; }
    .vstatus p { line-height: 1.5 !important; }
    @media (max-width: 480px) {
        .vstatus { padding: 14px !important; gap: 12px; }
        .vstatus-icon { padding: 10px !important; }
        .vstatus-icon svg { width: 22px; height: 22px; }
    }
</style>
@endonce
