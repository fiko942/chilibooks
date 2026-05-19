<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Detail Pelanggan', 'heading' => $customer['name'] ?? 'Pelanggan', 'copy' => 'Riwayat transaksi dan total pembelian pelanggan.']) ?>
<section class="panel">
    <form method="post" action="<?= site_url('customers/update/'.$customer['id']) ?>">
        <?= csrf_field() ?>
        <label>Nama</label><input name="name" value="<?= esc($customer['name']) ?>" required>
        <label>Lokasi</label><input name="location" value="<?= esc($customer['location']) ?>">
        <label>Kontak</label><input name="phone" value="<?= esc($customer['phone']) ?>">
        <button class="primary full">Simpan Perubahan</button>
    </form>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Histori pembelian</h2><p>Riwayat dipakai untuk mengukur pelanggan loyal.</p></div></div>
    <div class="list">
        <?php foreach ($transactions as $row): ?>
            <article class="row-card">
                <h3><?= esc($row['invoice']) ?></h3>
                <div class="meta"><span>Tanggal</span><b><?= esc($row['transaction_date']) ?></b></div>
                <div class="meta"><span>Total</span><b><?= rupiah($row['total']) ?></b></div>
                <div class="meta"><span>Status</span><b class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></b></div>
            </article>
        <?php endforeach ?>
    </div>
</section>
<?= $this->endSection() ?>
