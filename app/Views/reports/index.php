<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Laporan', 'heading' => 'Laporan penjualan dan keuntungan', 'copy' => 'Export PDF dan Excel mengikuti filter periode.', 'actions' => '<a class="btn ghost" href="'.site_url('reports/excel?start='.$start.'&end='.$end).'">Export Excel</a><a class="btn primary" href="'.site_url('reports/pdf?start='.$start.'&end='.$end).'">Export PDF</a>']) ?>
<section class="panel">
    <form method="get" action="<?= site_url('reports') ?>">
        <label>Mulai</label><input name="start" type="date" value="<?= esc($start) ?>">
        <label>Selesai</label><input name="end" type="date" value="<?= esc($end) ?>">
        <button class="ghost full">Filter Periode</button>
    </form>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Profit bulanan</h2><p>Keuntungan dihitung dari harga jual dikurangi harga modal.</p></div><span class="pill"><?= esc(date('M Y', strtotime($end))) ?></span></div>
    <div class="area-chart"><svg viewBox="0 0 760 280" preserveAspectRatio="none"><path d="M0 230 C90 210 120 130 210 150 C300 170 330 80 430 95 C520 110 560 40 650 70 C700 86 730 120 760 96 L760 280 L0 280 Z" fill="#fff0d9"/><path d="M0 230 C90 210 120 130 210 150 C300 170 330 80 430 95 C520 110 560 40 650 70 C700 86 730 120 760 96" fill="none" stroke="#d84727" stroke-width="6"/></svg></div>
</section>
<section class="panel">
    <h2>Ringkasan</h2>
    <div class="amount strong"><span>Omzet</span><b><?= rupiah($summary['total_sales']) ?></b></div>
    <div class="amount strong"><span>Profit bersih</span><b><?= rupiah($summary['total_profit']) ?></b></div>
    <div class="amount"><span>Transaksi</span><b><?= esc($summary['total_transactions']) ?></b></div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Produk terlaris</h2><p>Prioritas restock berdasarkan transaksi.</p></div></div>
    <div class="list">
        <?php foreach ($topProducts as $product): ?>
            <article class="row-card"><h3><?= esc($product['name']) ?></h3><div class="meta"><span>Terjual</span><b><?= esc($product['sold_qty']) ?> pcs</b></div><div class="meta"><span>Omzet</span><b><?= rupiah($product['revenue']) ?></b></div><div class="meta"><span>Profit</span><b><?= rupiah($product['profit']) ?></b></div></article>
        <?php endforeach ?>
    </div>
</section>
<?= $this->endSection() ?>
