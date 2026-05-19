<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', ['eyebrow' => 'Detail Transaksi', 'heading' => $transaction['invoice'] ?? 'Transaksi', 'copy' => 'Snapshot harga menjaga integritas histori.']) ?>
<section class="panel">
    <div class="amount"><span>Pelanggan</span><b><?= esc($transaction['customer_name']) ?></b></div>
    <div class="amount"><span>Status</span><b><?= esc($transaction['status']) ?></b></div>
    <div class="amount"><span>Subtotal</span><b><?= rupiah($transaction['subtotal']) ?></b></div>
    <div class="amount"><span>Diskon</span><b>- <?= rupiah($transaction['discount']) ?></b></div>
    <div class="amount"><span>Biaya tambahan</span><b><?= rupiah($transaction['extra_fee']) ?></b></div>
    <div class="divider"></div>
    <div class="amount strong"><span>Total bayar</span><b><?= rupiah($transaction['total']) ?></b></div>
    <div class="amount strong"><span>Profit</span><b><?= rupiah($transaction['profit']) ?></b></div>
    <?php if ($transaction['status'] === 'completed'): ?><form method="post" action="<?= site_url('transactions/cancel/'.$transaction['id']) ?>"><?= csrf_field() ?><button class="danger full">Tandai Dibatalkan</button></form><?php endif ?>
</section>
<section class="list">
    <?php foreach ($items as $item): ?>
        <article class="row-card"><h3><?= esc($item['product_name']) ?></h3><div class="meta"><span>Qty</span><b><?= esc($item['qty']) ?></b></div><div class="meta"><span>Harga</span><b><?= rupiah($item['selling_price_snapshot']) ?></b></div><div class="meta"><span>Modal</span><b><?= rupiah($item['cost_price_snapshot']) ?></b></div><div class="meta"><span>Subtotal</span><b><?= rupiah($item['subtotal']) ?></b></div></article>
    <?php endforeach ?>
</section>
<?= $this->endSection() ?>
