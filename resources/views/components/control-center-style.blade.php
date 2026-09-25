{{--
    Gaya tambahan halaman "Pusat Kendali" (Instansi & Perguruan Tinggi).
    Aturan desktop tidak diubah; hampir semua aturan hanya berlaku di layar < 768px.
    Tidak perlu build ulang CSS (npm run build).
--}}
@once
<style>
    /* ---------- Berlaku di semua ukuran (aman untuk desktop) ---------- */
    .cc-tab .cc-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; padding: 1px 6px; border-radius: 999px; font-size: 10px; font-weight: 800; line-height: 16px; background: #f1f5f9; color: #475569; letter-spacing: 0; }
    .cc-tab.cc-active .cc-count { background: #dbeafe; color: #1d4ed8; }

    /* Kartu HP tambahan */
    .cc-stat3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .cc-stat3 > div { background: #f8fafc; border: 1px solid #eef2f7; border-radius: 12px; padding: 8px 6px; text-align: center; }
    .cc-stat3 b { display: block; font-size: 17px; line-height: 1.15; font-weight: 800; color: #0f172a; }
    .cc-stat3 span { display: block; margin-top: 2px; font-size: 10px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: #94a3b8; }
    .cc-grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 12px; }
    .cc-grid2 .cc-span2 { grid-column: span 2 / span 2; }
    .cc-pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .cc-clamp2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .cc-actions { display: flex; flex-wrap: wrap; gap: 8px; width: 100%; }
    .cc-actions > form { margin: 0; flex: 1 1 0; display: flex; }
    .cc-actions > a, .cc-actions > button, .cc-actions > form > button { flex: 1 1 0; }
    .cc-actions .mbtn { padding: 10px 10px; font-size: 12.5px; white-space: nowrap; }
    .mbtn-green { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .mbtn-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .mbtn-indigo { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .mpill-amber { background: #fef3c7; color: #92400e; }
    .mpill-purple { background: #f3e8ff; color: #6b21a8; }
    .mpill-slate { background: #f1f5f9; color: #334155; }
    .mpill-sky { background: #e0f2fe; color: #075985; }
    .mpill-indigo { background: #e0e7ff; color: #3730a3; }
    .mavatar-indigo { background: #eef2ff; color: #4338ca; }
    .cc-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .cc-link { font-size: 12px; font-weight: 700; color: #2563eb; }

    /* ---------- Khusus HP (< 768px) ---------- */
    @media (max-width: 767px) {
        .cc-hide-m { display: none !important; }
        .cc-wrap { padding-top: 16px !important; padding-bottom: 24px !important; }
        .cc-wrap > div { gap: 14px; }
        .cc-wrap > div > * + * { margin-top: 14px !important; }

        /* Header halaman */
        .cc-title { font-size: 18px !important; line-height: 1.3 !important; }
        .cc-head-actions { width: 100%; display: grid !important; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px !important; }
        a.cc-head-actions { display: flex !important; justify-content: center; }
        .cc-head-actions > form { margin: 0 !important; display: flex; }
        .cc-head-actions > a, .cc-head-actions > form > button { width: 100%; justify-content: center; padding-top: 10px !important; padding-bottom: 10px !important; }

        /* Hero */
        .cc-hero { padding: 16px !important; border-radius: 20px !important; }
        .cc-hero-main { gap: 12px !important; }
        .cc-logo { width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; max-width: 56px !important; max-height: 56px !important; padding: 8px !important; border-radius: 14px !important; }
        .cc-logo img { width: 40px !important; height: 40px !important; max-width: 40px !important; max-height: 40px !important; }
        .cc-hero h1 { font-size: 17px !important; line-height: 1.3 !important; }
        .cc-badges { gap: 6px !important; }
        .cc-badges > span { font-size: 10.5px !important; padding: 2px 8px !important; }
        .cc-contacts { display: grid !important; gap: 6px !important; padding-top: 4px !important; }
        .cc-side { padding: 12px 14px !important; border-radius: 14px !important; }

        /* Kartu statistik */
        .cc-stats { gap: 10px !important; }
        .cc-stat { padding: 12px !important; border-radius: 14px !important; }
        .cc-stat-label { display: block; font-size: 10px !important; letter-spacing: .04em !important; line-height: 1.3 !important; }
        .cc-stat .text-2xl { font-size: 22px !important; line-height: 1.2 !important; }

        /* Tab menjadi chip yang bisa digeser */
        .cc-tabs { gap: 8px !important; padding: 12px !important; margin: 0 !important; scroll-padding: 12px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .cc-tabs::-webkit-scrollbar { display: none; }
        .cc-tabs > * + * { margin-left: 0 !important; }
        .cc-tab { flex: none; border: 1px solid #e2e8f0 !important; border-radius: 999px !important; padding: 8px 12px !important; background: #fff; color: #475569 !important; text-transform: none !important; letter-spacing: 0 !important; font-size: 12.5px !important; font-weight: 700 !important; }
        .cc-tab.cc-active { background: #2563eb !important; border-color: #2563eb !important; color: #fff !important; }
        .cc-tab.cc-active .cc-count { background: rgba(255, 255, 255, .22); color: #fff; }
        .cc-tabs-bare { border-bottom: 0 !important; }
        .cc-tabs-bare .cc-tabs { padding: 0 0 2px !important; }

        /* Isi tab */
        .cc-panel { padding: 0 !important; }
        .cc-panel-head { padding: 14px 14px 0 !important; }
        .cc-panel-head h3 { font-size: 15px !important; }
        .cc-panel-head .cc-head-actions { margin-top: 2px; }
        .cc-panel .mlist { border-radius: 0 0 22px 22px; }
        .cc-card { border-radius: 18px !important; }
        .cc-box { padding: 16px !important; border-radius: 18px !important; }
        .cc-box > * + * { margin-top: 16px !important; }
        .cc-box h3 { font-size: 16px !important; line-height: 1.35 !important; }
        .cc-box .p-4, .cc-box .p-5 { padding: 12px 14px !important; }
        .cc-box .pb-5 { padding-bottom: 14px !important; }
        .cc-card .mlist { border-radius: 0 0 18px 18px; }
    }
</style>
@endonce
