{{--
    Tata letak lembar sertifikat di browser. Dipakai cetak satuan dan cetak kolektif.

    1. Lampiran transkrip: memilih tingkat kepadatan pertama yang muat (normal -> compact -> tight)
       berdasarkan hasil ukur, sehingga nama aspek panjang dan penilaian dua pihak ikut terhitung.
       Bila tingkat tight pun tidak muat, tabel berlanjut ke lembar berikutnya.
    2. Skala tampilan agar lembar A4 landscape muat di layar HP/tablet (hasil cetak tidak berubah).
--}}
<script>
    (function () {
        var LEVELS = ['normal', 'compact', 'tight'];
        var SAFE_BOTTOM = 16; // px di atas tepi lembar: strip bawah (9px) + sedikit ruang

        function part(sheet, name) {
            return sheet.querySelector(':scope > [data-tr-part="' + name + '"]');
        }

        function rowsOf(sheet) {
            return sheet.querySelector('.tr-table tbody');
        }

        function fits(sheet) {
            var limit = sheet.clientHeight - SAFE_BOTTOM;
            return ['body', 'footer'].every(function (name) {
                var el = part(sheet, name);
                return !el || el.hidden || el.offsetTop + el.offsetHeight <= limit;
            });
        }

        // Kembalikan ke keadaan awal agar pengukuran bisa diulang (font selesai dimuat, sebelum cetak)
        function reset(sheet) {
            if (sheet._trCont) {
                sheet._trCont.forEach(function (cont) {
                    (cont.closest('.sheet-scale-wrap') || cont).remove();
                });
            }
            sheet._trCont = [];
            sheet._trRows = sheet._trRows || Array.prototype.slice.call(rowsOf(sheet).children);
            var body = rowsOf(sheet);
            sheet._trRows.forEach(function (row) { body.appendChild(row); });
            part(sheet, 'footer').hidden = false;
            sheet.removeAttribute('data-tr-paged');
        }

        function makeContinuation(first) {
            var cont = first.cloneNode(true);
            cont.setAttribute('data-tr-cont', '1');
            cont.setAttribute('data-tr-paged', '1');
            var bio = cont.querySelector('[data-tr-bio]');
            if (bio) { bio.remove(); }
            rowsOf(cont).innerHTML = '';
            part(cont, 'footer').hidden = false;
            first._trCont.push(cont);
            return cont;
        }

        function insertAfter(sheet, node) {
            var anchor = sheet.closest('.sheet-scale-wrap') || sheet;
            anchor.parentNode.insertBefore(node, anchor.nextSibling);
        }

        function moveLastRow(from, to) {
            to.insertBefore(from.lastElementChild, to.firstChild);
        }

        function kind(row) {
            return row ? row.getAttribute('data-row') : null;
        }

        function paginate(first) {
            var current = first;
            var guard = 0;
            first.setAttribute('data-tr-paged', '1');

            while (!fits(current) && guard++ < 20) {
                part(current, 'footer').hidden = true;
                var next = makeContinuation(first);
                insertAfter(current, next);

                var from = rowsOf(current);
                var to = rowsOf(next);

                while (!fits(current) && from.children.length > 1) {
                    moveLastRow(from, to);
                }
                // Keterangan skala selalu ditemani baris nilai, tidak berdiri sendiri di lembar baru
                while (to.children.length < 2 && from.children.length > 1) {
                    moveLastRow(from, to);
                }
                // Judul bagian tidak boleh menjadi baris terakhir lembar
                while (kind(from.lastElementChild) === 'band' && from.children.length > 1) {
                    moveLastRow(from, to);
                }
                // Subtotal tidak boleh membuka lembar tanpa minimal satu aspek di atasnya
                while (kind(to.firstElementChild) === 'subtotal' && kind(from.lastElementChild) === 'aspect' && from.children.length > 1) {
                    moveLastRow(from, to);
                }
                current = next;
            }
        }

        function layoutTranscript(sheet) {
            reset(sheet);
            var chosen = LEVELS.filter(function (level) {
                sheet.setAttribute('data-density', level);
                return fits(sheet);
            })[0];
            sheet.setAttribute('data-density', chosen || 'tight');
            if (!chosen) {
                paginate(sheet);
            }

            var total = 2 + sheet._trCont.length;
            [sheet].concat(sheet._trCont).forEach(function (page, index) {
                var label = page.querySelector('[data-tr-page-label]');
                if (label) { label.textContent = 'Halaman ' + (index + 2) + ' dari ' + total; }
            });
        }

        function layoutAll() {
            document.querySelectorAll('.transcript-sheet:not([data-tr-cont])').forEach(layoutTranscript);
            fit();
            document.documentElement.setAttribute('data-cert-layout', 'done');
        }

        function clearScale() {
            document.querySelectorAll('.certificate-sheet').forEach(function (sheet) {
                sheet.style.transform = 'none';
                var wrap = sheet.closest('.sheet-scale-wrap');
                if (wrap) { wrap.style.height = ''; wrap.style.width = ''; }
            });
        }

        function fit() {
            var available = document.documentElement.clientWidth - 16;
            document.querySelectorAll('.certificate-sheet').forEach(function (sheet) {
                var wrap = sheet.closest('.sheet-scale-wrap');
                if (!wrap) {
                    wrap = document.createElement('div');
                    wrap.className = 'sheet-scale-wrap';
                    sheet.parentNode.insertBefore(wrap, sheet);
                    wrap.appendChild(sheet);
                }
                sheet.style.transform = 'none';
                var w = sheet.offsetWidth, h = sheet.offsetHeight;
                var styles = getComputedStyle(sheet);
                var mt = parseFloat(styles.marginTop) || 0, mb = parseFloat(styles.marginBottom) || 0;
                var scale = Math.min(1, available / w);
                sheet.style.transform = scale < 1 ? 'scale(' + scale + ')' : 'none';
                sheet.style.marginLeft = scale < 1 ? '0' : '';
                wrap.style.width = scale < 1 ? (w * scale) + 'px' : '';
                wrap.style.height = scale < 1 ? (h * scale + (mt + mb) * scale) + 'px' : '';
            });
        }

        fit();
        window.addEventListener('resize', fit);
        window.addEventListener('load', layoutAll);
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(layoutAll);
        }
        window.addEventListener('beforeprint', function () {
            clearScale();
            document.querySelectorAll('.transcript-sheet:not([data-tr-cont])').forEach(layoutTranscript);
            clearScale();
        });
        window.addEventListener('afterprint', fit);
    })();
</script>
