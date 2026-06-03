<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Invoice') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/chilibooks.css') ?>">
</head>
<body class="public-page">
<div class="frame public-frame">
    <main class="content public-content">
        <section class="panel public-hero">
            <p class="eyebrow">Invoice Publik</p>
            <h1>Chili Oil Gen Z</h1>
            <p>Terima kasih telah berlangganan.</p>
        </section>

        <section class="panel">
            <div class="amount"><span>Nama</span><b><?= esc($customer['name'] ?? '-') ?></b></div>
            <div class="amount"><span>Pesanan</span><b><?= esc($transaction['invoice'] ?? '-') ?></b></div>
            <div class="amount"><span>Tanggal</span><b><?= esc(format_indonesian_datetime($transaction['transaction_date'] ?? null)) ?></b></div>
            <div class="amount"><span>Dibayar</span><b><?= esc(format_indonesian_datetime($transaction['paid_at'] ?? null)) ?></b></div>
            <div class="amount"><span>Metode bayar</span><b><?= esc(transaction_payment_method_label((string) ($transaction['payment_method'] ?? ''))) ?></b></div>
            <div class="amount"><span>Tipe pesanan</span><b><?= esc(transaction_delivery_label((string) ($transaction['delivery_type'] ?? ''))) ?></b></div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Produk</h2>
                    <p>Ringkasan pesanan yang telah dibayar.</p>
                </div>
            </div>
            <div class="list">
                <?php foreach ($items as $item): ?>
                    <article class="row-card">
                        <h3><?= esc($item['product_name']) ?></h3>
                        <div class="meta"><span>Qty</span><b><?= esc($item['qty']) ?></b></div>
                        <div class="meta"><span>Subtotal</span><b><?= rupiah($item['subtotal']) ?></b></div>
                    </article>
                <?php endforeach ?>
            </div>
        </section>

        <section class="panel">
            <div class="amount strong"><span>Total bayar</span><b><?= rupiah($transaction['total'] ?? 0) ?></b></div>
            <div class="amount"><span>Invoice</span><b><a href="<?= esc($invoiceUrl) ?>" target="_blank" rel="noopener">Buka tautan</a></b></div>
        </section>
    </main>
</div>
</body>
</html>
