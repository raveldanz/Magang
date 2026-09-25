{{--
    Tabel responsif: di layar < 768px setiap baris <tr> tabel ber-class "rtable" tampil sebagai kartu.
    Label kolom diambil dari atribut data-label pada <td>. Tanpa perlu build ulang CSS (npm run build).
    Pemakaian: <x-responsive-table-style /> sekali di view, lalu <table class="rtable ..."> dan <td data-label="...">.
--}}
@once
<style>
    @media (max-width: 767px) {
        table.rtable,
        table.rtable > tbody,
        table.rtable > tbody > tr,
        table.rtable > tbody > tr > td {
            display: block;
            width: 100%;
        }
        table.rtable > thead { display: none; }
        table.rtable > tbody {
            padding: 12px;
            background: #f8fafc;
            border: 0 !important;
        }
        table.rtable > tbody > tr {
            margin-bottom: 12px;
            padding: 14px;
            background: #fff;
            border: 1px solid #e6ebf2 !important;
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }
        table.rtable > tbody > tr:last-child { margin-bottom: 0; }
        table.rtable > tbody > tr > td + td { border-top: 1px dashed #eef2f7; }
        table.rtable > tbody > tr > td.rt-title + td,
        table.rtable > tbody > tr > td.rt-hide-mobile + td.rt-title + td { border-top: 0; }
        table.rtable > tbody > tr > td {
            padding: 5px 0 !important;
            text-align: left !important;
            max-width: none !important;
            min-width: 0 !important;
            white-space: normal !important;
            overflow-wrap: anywhere;
        }
        table.rtable > tbody > tr > td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 3px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        table.rtable > tbody > tr > td.rt-title::before { display: none; }
        table.rtable > tbody > tr > td.rt-title { font-size: 15px; font-weight: 700; color: #0f172a; padding: 0 0 6px !important; }
        table.rtable > tbody > tr > td.rt-hide-mobile { display: none; }
        table.rtable > tbody > tr > td.rt-actions { padding-top: 12px !important; margin-top: 4px; border-top: 1px solid #f1f5f9 !important; }
        table.rtable > tbody > tr > td.rt-actions::before { display: none; }
        table.rtable > tbody > tr > td.rt-actions > *,
        table.rtable > tbody > tr > td.rt-actions .btn-action-group { justify-content: flex-start; }
        table.rtable > tbody > tr > td[colspan] { text-align: center !important; }
        table.rtable > tbody > tr > td[colspan]::before { display: none; }
        table.rtable > tbody > tr > td .text-center,
        table.rtable > tbody > tr > td.text-center > div { text-align: left; margin-left: 0; }
    }
</style>
@endonce
