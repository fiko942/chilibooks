<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Histori Transaksi', 'heading' => 'Daftar penjualan tersimpan', 'copy' => 'Semua transaksi tersimpan dengan status selesai atau dibatalkan.', 'actions' => '<a class="btn primary" href="'.site_url('transactions/new').'">Input Transaksi</a>']) ?>
<section class="list">
    <?php foreach ($transactions as $row): ?>
        <article class="row-card">
            <h3><?= esc($row['invoice']) ?></h3>
            <div class="meta"><span>Pelanggan</span><b><?= esc($row['customer_name']) ?></b></div>
            <div class="meta"><span>Tanggal</span><b><?= esc($row['transaction_date']) ?></b></div>
            <div class="meta"><span>Total</span><b><?= rupiah($row['total']) ?></b></div>
            <div class="meta"><span>Profit</span><b><?= rupiah($row['profit']) ?></b></div>
            <div class="meta"><span>Status</span><b class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></b></div>
            <a class="btn ghost full" href="<?= site_url('transactions/'.$row['id']) ?>">Detail</a>
        </article>
    <?php endforeach ?>
</section>
<?= $this->endSection() ?>
