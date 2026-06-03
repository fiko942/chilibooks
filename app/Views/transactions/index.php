<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Histori Transaksi',
    'heading' => 'Daftar penjualan tersimpan',
    'copy' => 'Cari invoice atau nama pelanggan tanpa mengorbankan tampilan mobile.',
    'controls' => '<form method="get" action="'.site_url('transactions').'" class="list-search"><input type="search" name="q" value="'.esc($query ?? '').'" placeholder="Cari transaksi"><button class="ghost" type="submit">Cari</button></form>',
    'actions' => '<a class="btn primary" href="'.site_url('transactions/new').'">Input Transaksi</a>',
]) ?>

<?php if (! empty($listError)): ?>
    <div class="empty-state list-alert"><?= esc($listError) ?></div>
<?php endif ?>

<section class="list compact-list">
    <?php if (empty($transactions)): ?>
        <div class="empty-state">Tidak ada transaksi yang cocok.</div>
    <?php endif ?>

    <?php foreach ($transactions as $row): ?>
        <article class="row-card transaction-card">
            <div class="transaction-card-head">
                <div>
                    <h3><?= esc($row['invoice']) ?></h3>
                    <p><?= esc($row['customer_name']) ?></p>
                </div>
                <span class="status <?= $row['status'] === 'completed' ? 'ok' : 'bad' ?>"><?= esc($row['status']) ?></span>
            </div>
            <div class="transaction-card-meta">
                <div>
                    <span>Tanggal</span>
                    <b><?= esc($row['transaction_date']) ?></b>
                </div>
                <div>
                    <span>Total</span>
                    <b><?= rupiah($row['total']) ?></b>
                </div>
                <div>
                    <span>Profit</span>
                    <b><?= rupiah($row['profit']) ?></b>
                </div>
            </div>
            <div class="toolbar transaction-actions">
                <a class="btn ghost full" href="<?= site_url('transactions/'.$row['id']) ?>">Detail</a>
                <a class="btn ghost full send-invoice" href="<?= site_url('transactions/send-invoice/'.$row['id']) ?>">Kirim Invoice</a>
            </div>
        </article>
    <?php endforeach ?>
</section>

<?php if (! empty($pagerHtml)): ?>
    <div class="pager-wrap">
        <?= $pagerHtml ?>
    </div>
<?php endif ?>

<?= $this->endSection() ?>
