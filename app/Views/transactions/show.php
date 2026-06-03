<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/page_head', [
    'eyebrow' => 'Detail Transaksi',
    'heading' => $transaction['invoice'] ?? 'Transaksi',
    'copy' => 'Snapshot harga menjaga histori tetap konsisten dan jejak perubahan terlihat jelas.',
]) ?>
<section class="panel">
    <div class="amount"><span>Pelanggan</span><b><?= esc($transaction['customer_name']) ?></b></div>
    <div class="amount"><span>Tanggal</span><b><?= esc($transaction['transaction_date']) ?></b></div>
    <div class="amount"><span>Status</span><b><?= esc($transaction['status']) ?></b></div>
    <div class="amount"><span>Metode bayar</span><b><?= esc(transaction_payment_method_label((string) ($transaction['payment_method'] ?? ''))) ?></b></div>
    <div class="amount"><span>Pengantaran</span><b><?= esc(transaction_delivery_label((string) ($transaction['delivery_type'] ?? ''))) ?></b></div>
    <div class="amount"><span>Dibayar</span><b><?= esc(format_indonesian_datetime($transaction['paid_at'] ?? null)) ?></b></div>
    <?php if (! empty($transaction['payment_proof_path'])): ?>
        <div class="amount"><span>Bukti bayar</span><b><a href="<?= site_url('transactions/proof/' . $transaction['id']) ?>" target="_blank" rel="noopener">Lihat bukti</a></b></div>
    <?php endif ?>
    <div class="amount"><span>Invoice publik</span><b><a href="<?= site_url('invoice/' . $transaction['id']) ?>" target="_blank" rel="noopener">Buka invoice</a></b></div>
    <div class="amount"><span>Subtotal</span><b><?= rupiah($transaction['subtotal']) ?></b></div>
    <div class="amount"><span>Diskon</span><b>- <?= rupiah($transaction['discount']) ?></b></div>
    <div class="amount"><span>Biaya tambahan</span><b><?= rupiah($transaction['extra_fee']) ?></b></div>
    <div class="divider"></div>
    <div class="amount strong"><span>Total bayar</span><b><?= rupiah($transaction['total']) ?></b></div>
    <div class="amount strong"><span>Profit</span><b><?= rupiah($transaction['profit']) ?></b></div>
    <?php if ($transaction['status'] === 'completed'): ?>
        <form method="post" action="<?= site_url('transactions/cancel/' . $transaction['id']) ?>">
            <?= csrf_field() ?>
            <button class="danger full">Tandai Dibatalkan</button>
        </form>
    <?php endif ?>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Item pesanan</h2>
            <p>Data item berikut disimpan sebagai snapshot histori transaksi.</p>
        </div>
    </div>
    <div class="list">
        <?php foreach ($items as $item): ?>
            <article class="row-card">
                <h3><?= esc($item['product_name']) ?></h3>
                <div class="meta"><span>Qty</span><b><?= esc($item['qty']) ?></b></div>
                <div class="meta"><span>Harga</span><b><?= rupiah($item['selling_price_snapshot']) ?></b></div>
                <div class="meta"><span>Modal</span><b><?= rupiah($item['cost_price_snapshot']) ?></b></div>
                <div class="meta"><span>Subtotal</span><b><?= rupiah($item['subtotal']) ?></b></div>
            </article>
        <?php endforeach ?>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Jejak perubahan</h2>
            <p>Semua perubahan penting dicatat supaya owner bisa cek histori dan dampak stoknya.</p>
        </div>
    </div>

    <?php if (! empty($transactionAudits)): ?>
        <div class="audit-timeline">
            <?php foreach ($transactionAudits as $audit): ?>
                <article class="audit-card">
                    <div class="audit-head">
                        <div>
                            <span class="audit-pill"><?= esc(transaction_audit_label((string) ($audit['event_type'] ?? ''))) ?></span>
                            <h3><?= esc($audit['actor_name'] ?? 'Owner') ?></h3>
                            <p><?= esc(format_indonesian_datetime($audit['created_at'] ?? null)) ?></p>
                        </div>
                        <?php if (! empty($audit['notes'])): ?>
                            <span class="pill"><?= esc($audit['notes']) ?></span>
                        <?php endif ?>
                    </div>

                    <?php $afterPayload = $audit['after_payload'] ?? []; ?>
                    <?php if (! empty($afterPayload['stock_changes']) && is_array($afterPayload['stock_changes'])): ?>
                        <div class="audit-list">
                            <?php foreach ($afterPayload['stock_changes'] as $change): ?>
                                <div class="audit-item">
                                    <strong><?= esc($change['product_name'] ?? '-') ?></strong>
                                    <span><?= esc(($change['qty'] ?? 0) > 0 ? '+' : '') ?><?= esc($change['qty'] ?? 0) ?> pcs</span>
                                    <small>Stok <?= esc($change['stock_before'] ?? 0) ?> → <?= esc($change['stock_after'] ?? 0) ?></small>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>

                    <?php if (! empty($audit['before_payload']) && ! empty($audit['after_payload'])): ?>
                        <div class="audit-summary">
                            <div><span>Sebelum</span><b><?= esc(transaction_audit_label((string) ($audit['before_payload']['status'] ?? ''))) ?></b></div>
                            <div><span>Sesudah</span><b><?= esc(transaction_audit_label((string) ($audit['after_payload']['status'] ?? ''))) ?></b></div>
                            <div><span>Omzet</span><b><?= rupiah($audit['before_payload']['total'] ?? 0) ?> → <?= rupiah($audit['after_payload']['total'] ?? 0) ?></b></div>
                        </div>
                    <?php endif ?>
                </article>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Belum ada jejak perubahan yang tercatat.</div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
