<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Input Transaksi', 'heading' => 'Pencatatan penjualan baru', 'copy' => 'Diskon, biaya tambahan, dan total pembayaran dihitung otomatis.']) ?>
<section class="panel">
    <form method="post" action="<?= site_url('transactions') ?>">
        <?= csrf_field() ?>
        <label>Pelanggan</label>
        <select name="customer_id" required><?php foreach ($customers as $customer): ?><option value="<?= $customer['id'] ?>"><?= esc($customer['name']) ?></option><?php endforeach ?></select>
        <label>Tanggal</label><input name="transaction_date" type="date" value="<?= date('Y-m-d') ?>">
        <label>Produk 1</label><select name="product_id[]"><?php foreach ($products as $product): ?><option value="<?= $product['id'] ?>"><?= esc($product['name']) ?> - <?= rupiah($product['selling_price']) ?></option><?php endforeach ?></select>
        <label>Qty 1</label><input name="qty[]" type="number" value="1" min="0">
        <label>Produk 2</label><select name="product_id[]"><option value="">- opsional -</option><?php foreach ($products as $product): ?><option value="<?= $product['id'] ?>"><?= esc($product['name']) ?> - <?= rupiah($product['selling_price']) ?></option><?php endforeach ?></select>
        <label>Qty 2</label><input name="qty[]" type="number" value="0" min="0">
        <label>Diskon manual</label><input name="discount" type="number" value="0">
        <label>Biaya tambahan</label><input name="extra_fee" type="number" value="0">
        <label>Status pesanan</label><select name="status"><option value="completed">Selesai</option><option value="canceled">Dibatalkan</option></select>
        <label>Catatan</label><textarea name="notes"></textarea>
        <button class="primary full">Simpan Transaksi</button>
    </form>
</section>
<?= $this->endSection() ?>
