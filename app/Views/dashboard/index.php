<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Dashboard', 'heading' => 'Ringkasan bisnis hari ini', 'copy' => 'Monitor penjualan, keuntungan, transaksi, dan pelanggan paling bernilai.', 'actions' => '<a class="btn ghost" href="'.site_url('dashboard').'">Bulan ini</a><a class="btn primary" href="'.site_url('transactions/new').'">Tambah Transaksi</a>']) ?>
<section class="stats-grid">
    <div class="stat"><p>Total Penjualan</p><strong><?= rupiah($summary['total_sales'] ?? 0) ?></strong><span>Periode aktif</span></div>
    <div class="stat"><p>Keuntungan</p><strong><?= rupiah($summary['total_profit'] ?? 0) ?></strong><span>Profit</span></div>
    <div class="stat"><p>Transaksi</p><strong><?= esc($summary['total_transactions'] ?? 0) ?></strong><span>Selesai</span></div>
    <div class="stat"><p>Pesanan Batal</p><strong><?= esc($canceled) ?></strong><span style="color:var(--gold)">Dibatalkan</span></div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Tren penjualan</h2><p>Visual ringkas periode <?= esc($start) ?> s/d <?= esc($end) ?>.</p></div><span class="pill">7 hari</span></div>
    <div class="chart"><span style="height:42%"></span><span style="height:62%"></span><span style="height:38%"></span><span style="height:72%"></span><span style="height:56%"></span><span style="height:84%"></span><span style="height:68%"></span></div>
    <div class="chart-labels"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Pelanggan loyal</h2><p>Frekuensi transaksi tertinggi.</p></div></div>
    <div class="list">
        <?php foreach ($loyalCustomers as $customer): ?>
            <div class="row-card"><h3><?= esc($customer['name']) ?></h3><div class="meta"><span>Transaksi</span><b><?= esc($customer['transaction_count']) ?></b></div><div class="meta"><span>Total</span><b><?= rupiah($customer['total_spent']) ?></b></div></div>
        <?php endforeach ?>
    </div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Transaksi terbaru</h2><p>Status selesai dan dibatalkan tersimpan.</p></div></div>
    <div class="list">
        <?php foreach ($latest as $row): ?>
            <div class="row-card"><h3><?= esc($row['invoice']) ?></h3><div class="meta"><span>Pelanggan</span><b><?= esc($row['customer_name']) ?></b></div><div class="meta"><span>Total</span><b><?= rupiah($row['total']) ?></b></div><div class="meta"><span>Status</span><b class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></b></div></div>
        <?php endforeach ?>
    </div>
</section>
<?= $this->endSection() ?>
