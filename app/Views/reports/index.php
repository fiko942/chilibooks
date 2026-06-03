<?php
$reportPicker = '<form method="get" action="' . site_url('reports') . '" class="report-range-form" data-report-range>
    <button type="button" class="report-range-trigger" data-range-trigger>
        <span data-range-label>' . esc($periodLabel) . '</span>
        <small>Klik untuk pilih periode</small>
    </button>
    <input type="hidden" name="start" value="' . esc($start, 'attr') . '" data-range-start>
    <input type="hidden" name="end" value="' . esc($end, 'attr') . '" data-range-end>
    <div class="report-range-popover" hidden data-range-popover>
        <div class="report-range-head">
            <button type="button" class="ghost small" data-range-prev>&lsaquo;</button>
            <strong data-range-month></strong>
            <button type="button" class="ghost small" data-range-next>&rsaquo;</button>
        </div>
        <div class="report-range-summary">
            <div><span>Mulai</span><b data-range-start-label>' . esc(format_indonesian_date($start)) . '</b></div>
            <div><span>Selesai</span><b data-range-end-label>' . esc(format_indonesian_date($end)) . '</b></div>
        </div>
        <div class="date-weekdays">
            <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
        </div>
        <div class="report-range-grid" data-range-grid></div>
        <div class="toolbar report-range-actions">
            <button type="button" class="btn ghost" data-range-reset>Bulan ini</button>
            <button type="submit" class="btn primary">Filter Periode</button>
        </div>
    </div>
</form>';
?>
<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php if (! empty($reportError)): ?>
    <div class="alert error"><?= esc($reportError) ?></div>
<?php endif ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Laporan',
    'heading' => 'Laporan penjualan dan keuntungan',
    'copy' => 'Export PDF dan Excel mengikuti periode yang dipilih dari kalender khusus.',
    'controls' => $reportPicker,
    'actions' => '<a class="btn ghost" href="' . site_url('reports/excel?start=' . $start . '&end=' . $end) . '">Export Excel</a><a class="btn primary" href="' . site_url('reports/pdf?start=' . $start . '&end=' . $end) . '">Export PDF</a>',
]) ?>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Profit bulanan</h2>
            <p>Periode <strong class="inline-period"><?= esc($periodLabel) ?></strong> dibaca langsung dari transaksi completed.</p>
        </div>
        <span class="pill inline-period-pill"><?= esc($periodLabel) ?></span>
    </div>
    <div class="area-chart">
        <svg viewBox="0 0 760 280" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 230 C90 210 120 130 210 150 C300 170 330 80 430 95 C520 110 560 40 650 70 C700 86 730 120 760 96 L760 280 L0 280 Z" fill="#fff0d9"/>
            <path d="M0 230 C90 210 120 130 210 150 C300 170 330 80 430 95 C520 110 560 40 650 70 C700 86 730 120 760 96" fill="none" stroke="#d84727" stroke-width="6"/>
        </svg>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Cara membaca angka</h2>
            <p>Formula ringkas ini dipakai di dashboard dan laporan supaya owner awam tetap paham asal angka.</p>
        </div>
    </div>
    <div class="formula-grid">
        <article class="formula-card">
            <span>Omzet</span>
            <strong><?= esc($businessNotes['omzet'] ?? '-') ?></strong>
        </article>
        <article class="formula-card">
            <span>Profit</span>
            <strong><?= esc($businessNotes['profit'] ?? '-') ?></strong>
        </article>
        <article class="formula-card">
            <span>Cash-in</span>
            <strong><?= esc($businessNotes['cash_in'] ?? '-') ?></strong>
        </article>
        <article class="formula-card">
            <span>Pesanan</span>
            <strong><?= esc($businessNotes['pesanan'] ?? '-') ?></strong>
        </article>
    </div>
</section>

<?php if (empty($summary['total_transactions']) && empty($transactions) && empty($topProducts)): ?>
    <section class="panel empty-panel">
        <h2>Belum ada data laporan</h2>
        <p>Atur periode lain atau pastikan transaksi completed tersedia di database produksi.</p>
    </section>
<?php else: ?>
    <section class="panel">
        <h2>Ringkasan</h2>
        <div class="amount strong"><span>Omzet</span><b><?= rupiah($summary['total_sales']) ?></b></div>
        <div class="amount strong"><span>Profit bersih</span><b><?= rupiah($summary['total_profit']) ?></b></div>
        <div class="amount"><span>Transaksi</span><b><?= esc($summary['total_transactions']) ?></b></div>
        <div class="amount"><span>Cash-in</span><b><?= rupiah($cashInTotal ?? 0) ?></b></div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Transaksi</h2>
                <p>Data real dalam periode terpilih.</p>
            </div>
        </div>
        <?php if (! empty($transactions)): ?>
            <div class="list">
                <?php foreach ($transactions as $row): ?>
                    <article class="row-card report-transaction">
                        <h3><?= esc($row['invoice']) ?></h3>
                        <div class="meta"><span>Pelanggan</span><b><?= esc($row['customer_name']) ?></b></div>
                        <div class="meta"><span>Tanggal</span><b><?= esc(format_indonesian_date($row['transaction_date'])) ?></b></div>
                        <div class="meta"><span>Status</span><b class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></b></div>
                        <div class="meta"><span>Total</span><b><?= rupiah($row['total']) ?></b></div>
                        <div class="meta"><span>Profit</span><b><?= rupiah($row['profit']) ?></b></div>
                    </article>
                <?php endforeach ?>
            </div>
        <?php else: ?>
            <div class="empty-state">Belum ada transaksi completed pada periode ini.</div>
        <?php endif ?>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Produk terlaris</h2>
                <p>Prioritas restock berdasarkan transaksi real.</p>
            </div>
        </div>
        <?php if (! empty($topProducts)): ?>
            <div class="list">
                <?php foreach ($topProducts as $product): ?>
                    <article class="row-card report-product">
                        <h3><?= esc($product['name']) ?></h3>
                        <div class="meta"><span>Terjual</span><b><?= esc($product['sold_qty']) ?> pcs</b></div>
                        <div class="meta"><span>Omzet</span><b><?= rupiah($product['revenue']) ?></b></div>
                        <div class="meta"><span>Profit</span><b><?= rupiah($product['profit']) ?></b></div>
                    </article>
                <?php endforeach ?>
            </div>
        <?php else: ?>
            <div class="empty-state">Belum ada produk terjual pada periode ini.</div>
        <?php endif ?>
    </section>
<?php endif ?>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Cash-in per metode</h2>
            <p>Uang masuk dipisah supaya owner bisa baca jalur pembayaran dengan jelas.</p>
        </div>
    </div>
    <?php if (! empty($paymentBreakdown)): ?>
        <div class="cashin-grid">
            <?php foreach ($paymentBreakdown as $breakdown): ?>
                <article class="cashin-card">
                    <span><?= esc($breakdown['label'] ?? transaction_payment_method_label((string) ($breakdown['payment_method'] ?? ''))) ?></span>
                    <strong><?= rupiah($breakdown['total_amount']) ?></strong>
                </article>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Belum ada pembayaran completed yang bisa dibagi ke metode bayar.</div>
    <?php endif ?>
</section>

<script>
(function () {
    const picker = document.querySelector('[data-report-range]');
    if (!picker) {
        return;
    }

    const trigger = picker.querySelector('[data-range-trigger]');
    const popover = picker.querySelector('[data-range-popover]');
    const monthLabel = picker.querySelector('[data-range-month]');
    const grid = picker.querySelector('[data-range-grid]');
    const startInput = picker.querySelector('[data-range-start]');
    const endInput = picker.querySelector('[data-range-end]');
    const rangeLabel = picker.querySelector('[data-range-label]');
    const startLabel = picker.querySelector('[data-range-start-label]');
    const endLabel = picker.querySelector('[data-range-end-label]');
    const prevButton = picker.querySelector('[data-range-prev]');
    const nextButton = picker.querySelector('[data-range-next]');
    const resetButton = picker.querySelector('[data-range-reset]');

    const dayFormatter = new Intl.DateTimeFormat('id-ID', { day: 'numeric' });
    const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
    const displayFormatter = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const today = new Date();
    let anchor = parseValue(startInput.value) || new Date(today.getFullYear(), today.getMonth(), 1);

    function parseValue(value) {
        if (!value) {
            return null;
        }

        const parts = value.split('-').map(Number);
        if (parts.length !== 3) {
            return null;
        }

        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function toValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function display(date) {
        return displayFormatter.format(date);
    }

    function updateLabels() {
        const start = parseValue(startInput.value);
        const end = parseValue(endInput.value);
        rangeLabel.textContent = `${display(start)} - ${display(end)}`;
        startLabel.textContent = display(start);
        endLabel.textContent = display(end);
    }

    function renderCalendar() {
        const year = anchor.getFullYear();
        const month = anchor.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const offset = firstDay.getDay();

        monthLabel.textContent = monthFormatter.format(firstDay);
        grid.innerHTML = '';

        for (let index = 0; index < offset; index += 1) {
            const filler = document.createElement('span');
            filler.className = 'calendar-filler';
            grid.appendChild(filler);
        }

        const currentStart = parseValue(startInput.value);
        const currentEnd = parseValue(endInput.value);

        for (let day = 1; day <= lastDay.getDate(); day += 1) {
            const date = new Date(year, month, day);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'calendar-day';
            button.dataset.date = toValue(date);
            button.textContent = dayFormatter.format(date);

            if (currentStart && toValue(date) === toValue(currentStart)) {
                button.classList.add('selected');
            }

            if (currentEnd && toValue(date) === toValue(currentEnd)) {
                button.classList.add('selected-end');
            }

            if (currentStart && currentEnd && date >= currentStart && date <= currentEnd) {
                button.classList.add('in-range');
            }

            grid.appendChild(button);
        }
    }

    function setRange(startDate, endDate) {
        startInput.value = toValue(startDate);
        endInput.value = toValue(endDate);
        updateLabels();
        renderCalendar();
    }

    trigger.addEventListener('click', () => {
        popover.hidden = !popover.hidden;
        if (!popover.hidden) {
            renderCalendar();
        }
    });

    prevButton.addEventListener('click', () => {
        anchor = new Date(anchor.getFullYear(), anchor.getMonth() - 1, 1);
        renderCalendar();
    });

    nextButton.addEventListener('click', () => {
        anchor = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 1);
        renderCalendar();
    });

    resetButton.addEventListener('click', () => {
        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        setRange(monthStart, today);
        anchor = new Date(monthStart);
    });

    grid.addEventListener('click', (event) => {
        const button = event.target.closest('[data-date]');
        if (!button) {
            return;
        }

        const selectedDate = parseValue(button.dataset.date);
        const currentStart = parseValue(startInput.value);
        const currentEnd = parseValue(endInput.value);

        if (!currentStart || (currentStart && currentEnd && toValue(currentStart) !== toValue(currentEnd))) {
            setRange(selectedDate, selectedDate);
            return;
        }

        if (selectedDate < currentStart) {
            setRange(selectedDate, currentStart);
        } else {
            setRange(currentStart, selectedDate);
        }
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) {
            popover.hidden = true;
        }
    });

    updateLabels();
    renderCalendar();
})();
</script>
<?= $this->endSection() ?>
