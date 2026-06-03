<?php

$monthSelector = '<form class="month-picker" method="get" action="' . site_url('dashboard') . '">
    <label for="dashboard-month">Periode</label>
    <select id="dashboard-month" name="month" onchange="this.form.submit()">';

foreach ($monthOptions as $option) {
    $selected = $option['selected'] ? ' selected' : '';
    $monthSelector .= '<option value="' . esc($option['value'], 'attr') . '"' . $selected . '>' . esc($option['label']) . '</option>';
}

$monthSelector .= '</select><noscript><button class="btn ghost" type="submit">Tampilkan</button></noscript></form>';
?>
<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php if (! empty($dashboardError)): ?>
    <div class="alert error"><?= esc($dashboardError) ?></div>
<?php endif ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Dashboard',
    'heading' => 'Ringkasan bisnis ' . $selectedMonthLabel,
    'copy' => 'Data dashboard dibaca langsung dari transaksi real. Pilih bulan lain untuk membandingkan periode sebelumnya.',
    'controls' => $monthSelector,
    'actions' => '<a class="btn primary" href="'.site_url('transactions/new').'">Tambah Transaksi</a>',
]) ?>

<?php if (empty($metrics)): ?>
    <section class="panel empty-panel">
        <h2>Belum ada data dashboard</h2>
        <p>Hubungkan database produksi agar metrik penjualan bisa dimuat dari transaksi real.</p>
    </section>
<?php else: ?>
    <section class="stats-grid">
        <?php foreach ($metrics as $metric): ?>
            <div class="stat">
                <p><?= esc($metric['label']) ?></p>
                <strong><?= esc($metric['value']) ?></strong>
                <span><?= esc($metric['note']) ?></span>
            </div>
        <?php endforeach ?>
    </section>
<?php endif ?>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Tren penjualan</h2>
            <p><?= esc($selectedMonthLabel) ?> dibandingkan bucket harian dalam bulan tersebut.</p>
        </div>
        <span class="pill"><?= esc($previousMonthLabel) ?></span>
    </div>
    <?php if (! empty($trend)): ?>
        <div class="chart">
            <?php foreach ($trend as $point): ?>
                <span style="height: <?= esc($point['height']) ?>%"></span>
            <?php endforeach ?>
        </div>
        <div class="chart-labels">
            <?php foreach ($trend as $point): ?>
                <span><?= esc($point['label']) ?></span>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Belum ada transaksi completed untuk periode ini.</div>
    <?php endif ?>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Pelanggan loyal</h2>
            <p>Berdasarkan transaksi completed dalam periode terpilih.</p>
        </div>
    </div>
    <?php if (! empty($loyalCustomers)): ?>
        <div class="list">
            <?php foreach ($loyalCustomers as $customer): ?>
                <div class="row-card">
                    <h3><?= esc($customer['name']) ?></h3>
                    <div class="meta"><span>Transaksi</span><b><?= esc($customer['transaction_count']) ?></b></div>
                    <div class="meta"><span>Total</span><b><?= rupiah($customer['total_spent']) ?></b></div>
                </div>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Belum ada pelanggan loyal pada bulan ini.</div>
    <?php endif ?>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Transaksi terbaru</h2>
            <p>Status selesai dan dibatalkan di periode terpilih.</p>
        </div>
    </div>
    <?php if (! empty($latestTransactions)): ?>
        <div class="list">
            <?php foreach ($latestTransactions as $row): ?>
                <div class="row-card">
                    <h3><?= esc($row['invoice']) ?></h3>
                    <div class="meta"><span>Pelanggan</span><b><?= esc($row['customer_name']) ?></b></div>
                    <div class="meta"><span>Total</span><b><?= rupiah($row['total']) ?></b></div>
                    <div class="meta"><span>Status</span><b class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></b></div>
                </div>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Belum ada transaksi pada periode ini.</div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
