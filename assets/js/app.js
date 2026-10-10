(function () {
    const todayInputs = document.querySelectorAll('input[data-today]');
    const today = new Date().toISOString().slice(0, 10);
    todayInputs.forEach(function (el) {
        if (!el.value) el.value = today;
    });

    const itemSelect = document.querySelector('#barang_id');
    const qtyInput = document.querySelector('#jumlah');
    if (itemSelect && qtyInput) {
        itemSelect.addEventListener('change', function () {
            const opt = itemSelect.options[itemSelect.selectedIndex];
            const stok = opt ? opt.getAttribute('data-stok') : null;
            const hint = document.querySelector('#stok-hint');
            if (hint) {
                hint.textContent = stok !== null && itemSelect.value ? 'Stok tersedia: ' + stok : '';
            }
        });
        itemSelect.dispatchEvent(new Event('change'));
    }

    const existing = document.querySelector('select[name="barang_id"]');
    const newFields = document.querySelectorAll('[data-new-item]');
    if (existing && newFields.length) {
        const sync = function () {
            const isExisting = existing.value !== '';
            newFields.forEach(function (el) {
                el.querySelectorAll('input').forEach(function (input) {
                    input.disabled = isExisting;
                });
                el.style.opacity = isExisting ? '0.45' : '1';
            });
        };
        existing.addEventListener('change', sync);
        sync();
    }

    const printBtn = document.querySelector('[data-print]');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }
})();
