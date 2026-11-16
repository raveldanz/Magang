{{--
    Skala tampilan lembar A4 agar muat di layar HP/tablet tanpa geser ke samping.
    Hanya untuk layar: saat dicetak skala dikembalikan ke 100% sehingga hasil cetak tidak berubah.
    Pemakaian: @include('partials.sheet-fit', ['selector' => '.page-container', 'toolbar' => '.no-print'])
--}}
@php
    $sheetFitSelector = $selector ?? '.page-container';
    $sheetFitToolbar = $toolbar ?? null;
@endphp
<style>
    @media screen and (max-width: 860px) {
        body { padding-top: 0 !important; overflow-x: hidden; }
        @if ($sheetFitToolbar)
            {!! $sheetFitToolbar !!} { flex-wrap: wrap !important; gap: 8px !important; }
        @endif
    }

    @media print {
        [data-sheet-fit] { zoom: 1 !important; }
    }
</style>
<script>
    (function () {
        var selector = @json($sheetFitSelector);

        function fit() {
            var available = document.documentElement.clientWidth - 16;
            document.querySelectorAll(selector).forEach(function (sheet) {
                sheet.setAttribute('data-sheet-fit', '1');
                sheet.style.zoom = '';
                var width = sheet.offsetWidth;
                sheet.style.zoom = width > available ? String(available / width) : '';
            });
        }

        fit();
        window.addEventListener('resize', fit);
        window.addEventListener('load', fit);
    })();
</script>
