<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Pelanggan', 'heading' => 'Manajemen data pelanggan', 'copy' => 'Catat pelanggan dan lihat histori transaksi pembeli loyal.']) ?>
<section class="panel">
    <h2>Tambah Pelanggan</h2>
    <form method="post" action="<?= site_url('customers') ?>">
        <?= csrf_field() ?>
        <label>Nama</label><input name="name" required>
        <label>Lokasi</label><input name="location">
        <label>Kontak</label><input name="phone">
        <button class="primary full">Simpan Pelanggan</button>
    </form>
</section>
<section class="list">
    <?php foreach ($customers as $customer): ?>
        <article class="row-card">
            <h3><?= esc($customer['name']) ?></h3>
            <div class="meta"><span>Lokasi</span><b><?= esc($customer['location'] ?: '-') ?></b></div>
            <div class="meta"><span>Transaksi</span><b><?= esc($customer['transaction_count']) ?></b></div>
            <div class="meta"><span>Total</span><b><?= rupiah($customer['total_spent']) ?></b></div>
            <a class="btn ghost full" href="<?= site_url('customers/'.$customer['id']) ?>">Lihat Detail</a>
        </article>
    <?php endforeach ?>
</section>
<?= $this->endSection() ?>
