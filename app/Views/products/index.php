<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Produk & Stok', 'heading' => 'Data produk dan persediaan', 'copy' => 'Simpan nama produk, harga jual, harga modal, dan stok operasional.', 'actions' => '<a class="btn primary" href="'.site_url('products/new').'">Tambah Produk</a>']) ?>
<section class="panel">
    <div class="panel-head"><div><h2>Katalog produk</h2><p>Harga modal dipakai untuk hitung keuntungan otomatis.</p></div></div>
    <div class="list">
        <?php foreach ($products as $product): ?>
            <article class="row-card">
                <h3><?= esc($product['name']) ?></h3>
                <div class="meta"><span>SKU</span><b><?= esc($product['sku']) ?></b></div>
                <div class="meta"><span>Harga jual</span><b><?= rupiah($product['selling_price']) ?></b></div>
                <div class="meta"><span>Harga modal</span><b><?= rupiah($product['cost_price']) ?></b></div>
                <div class="meta"><span>Stok</span><b><?= esc($product['stock']) ?> pcs</b></div>
                <div class="meta"><span>Status</span><b class="status <?= $product['status'] === 'low' ? 'warning' : 'ok' ?>"><?= $product['status'] === 'low' ? 'Menipis' : 'Aman' ?></b></div>
                <a class="btn ghost full" href="<?= site_url('products/edit/'.$product['id']) ?>">Edit Produk</a>
            </article>
        <?php endforeach ?>
    </div>
</section>
<?= $this->endSection() ?>
