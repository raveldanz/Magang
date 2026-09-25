{{--
    Gaya kartu khusus tampilan HP (< 768px). Dipakai halaman yang punya versi kartu terpisah
    dari tabel desktop. Kelas: .m-only (tampil di HP saja), .d-only (sembunyi di HP).
    Tidak perlu build ulang CSS (npm run build).
--}}
@once
<style>
    /* Desktop (>= 768px): kartu HP disembunyikan total. !important supaya tidak kalah oleh
       kelas lain yang juga mengatur display (mis. .mlist { display: flex }). */
    @media (min-width: 768px) {
        .m-only { display: none !important; }
    }
    @media (max-width: 767px) {
        .d-only { display: none !important; }
    }
    .mlist { display: flex; flex-direction: column; gap: 12px; padding: 12px; background: #f8fafc; }
    .mcard { background: #fff; border: 1px solid #e6ebf2; border-radius: 16px; padding: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .mcard-head { display: flex; align-items: flex-start; gap: 12px; }
    .mcard-main { flex: 1; min-width: 0; }
    .mavatar { flex: none; width: 40px; height: 40px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
    .mcard-title { font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .mcard-sub { margin-top: 3px; font-size: 12px; color: #64748b; line-height: 1.45; }
    .mcard-sub .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; color: #2563eb; }
    .mcard-body { margin-top: 12px; display: grid; gap: 10px; }
    .mfield-label { font-size: 10.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; }
    .mfield-value { margin-top: 2px; font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.4; }
    .mcard-foot { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9; display: flex; gap: 8px; align-items: center; }
    .mscore { flex: none; min-width: 54px; padding: 5px 8px; border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; text-align: center; }
    .mscore b { display: block; font-size: 17px; line-height: 1.1; color: #047857; }
    .mscore span { font-size: 10px; font-weight: 700; color: #059669; }
    .mpill { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .mpill-green { background: #d1fae5; color: #065f46; }
    .mpill-red { background: #ffe4e6; color: #9f1239; }
    .mpill-blue { background: #eff6ff; color: #1d4ed8; }
    .mbar { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .mbar > i { display: block; height: 100%; border-radius: 999px; }
    .mbtn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 700; line-height: 1; cursor: pointer; }
    .mbtn-block { display: flex; width: 100%; }
    .mbtn-primary { background: #2563eb; color: #fff; }
    .mbtn-primary:active { background: #1d4ed8; }
    .mbtn-soft { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .mbtn-danger { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
    .mstepper { display: inline-flex; align-items: stretch; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; position: relative; }
    .mstepper button { width: 40px; height: 40px; font-size: 18px; font-weight: 700; color: #334155; background: #f8fafc; }
    .mstepper button:active { background: #e2e8f0; }
    .mstepper input { width: 56px; height: 40px; border: 0; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; text-align: center; font-size: 15px; font-weight: 700; color: #0f172a; padding: 0; -moz-appearance: textfield; }
    .mstepper input:focus { outline: 2px solid #93c5fd; outline-offset: -2px; box-shadow: none; }
    .mstepper input::-webkit-outer-spin-button, .mstepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .mempty { padding: 32px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
</style>
@endonce
