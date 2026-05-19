<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $isEdit = ! empty($product); ?>
<?= view('partials/page_head', ['eyebrow' => 'Produk', 'heading' => $isEdit ? 'Edit produk' : 'Tambah produk', 'copy' => 'Kelola harga jual, harga modal, dan stok.']) ?>
<section class="panel">
    <form method="post" action="<?= $isEdit ? site_url('products/update/'.$product['id']) : site_url('products') ?>">
        <?= csrf_field() ?>
        <label>Nama produk</label><input name="name" value="<?= old('name', $product['name'] ?? '') ?>" required>
        <label>SKU</label><input name="sku" value="<?= old('sku', $product['sku'] ?? '') ?>">
        <label>Harga jual</label><input name="selling_price" type="number" value="<?= old('selling_price', $product['selling_price'] ?? 0) ?>" required>
        <label>Harga modal</label><input name="cost_price" type="number" value="<?= old('cost_price', $product['cost_price'] ?? 0) ?>" required>
        <label>Stok</label><input name="stock" type="number" value="<?= old('stock', $product['stock'] ?? 0) ?>" required>
        <button class="primary full"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' ?></button>
    </form>
    <?php if ($isEdit): ?>
        <form method="post" action="<?= site_url('products/delete/'.$product['id']) ?>"><?= csrf_field() ?><button class="danger full">Hapus Produk</button></form>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
