LAPORAN CHILIBOOKS
Periode: <?= esc($periodLabel) ?>
Tanggal: <?= esc($start) ?> s/d <?= esc($end) ?>

CARA MEMBACA ANGKA
Omzet: <?= esc($businessNotes['omzet'] ?? '-') ?>
Profit: <?= esc($businessNotes['profit'] ?? '-') ?>
Cash-in: <?= esc($businessNotes['cash_in'] ?? '-') ?>
Pesanan: <?= esc($businessNotes['pesanan'] ?? '-') ?>

RINGKASAN
Omzet: <?= rupiah($summary['total_sales']) ?>
Profit bersih: <?= rupiah($summary['total_profit']) ?>
Transaksi: <?= esc($summary['total_transactions']) ?>
Cash-in: <?= rupiah($cashInTotal ?? 0) ?>

CASH-IN PER METODE
<?php if (empty($paymentBreakdown)): ?>
Belum ada pembayaran completed yang bisa dibagi ke metode bayar.
<?php else: ?>
<?php foreach ($paymentBreakdown as $breakdown): ?>
<?= esc($breakdown['label'] ?? transaction_payment_method_label((string) ($breakdown['payment_method'] ?? ''))) ?> | <?= rupiah($breakdown['total_amount']) ?>
<?php endforeach ?>
<?php endif ?>

TRANSAKSI
<?php if (empty($transactions)): ?>
Belum ada transaksi completed pada periode ini.
<?php else: ?>
<?php foreach ($transactions as $row): ?>
<?= esc($row['invoice']) ?> | <?= esc($row['customer_name']) ?> | <?= esc(format_indonesian_date($row['transaction_date'])) ?> | <?= esc($row['status']) ?> | <?= rupiah($row['total']) ?> | <?= rupiah($row['profit']) ?>
<?php endforeach ?>
<?php endif ?>

PRODUK TERLARIS
<?php if (empty($topProducts)): ?>
Belum ada produk terjual pada periode ini.
<?php else: ?>
<?php foreach ($topProducts as $product): ?>
<?= esc($product['name']) ?> | <?= esc($product['sold_qty']) ?> pcs | <?= rupiah($product['revenue']) ?> | <?= rupiah($product['profit']) ?>
<?php endforeach ?>
<?php endif ?>
