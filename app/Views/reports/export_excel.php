<table border="1">
    <tr><th colspan="6">Laporan ChiliBooks <?= esc($periodLabel) ?></th></tr>
    <tr><td colspan="6">Periode: <?= esc($start) ?> s/d <?= esc($end) ?></td></tr>
</table>

<br>

<table border="1">
    <tr><th colspan="2">Cara membaca angka</th></tr>
    <tr><th>Omzet</th><td><?= esc($businessNotes['omzet'] ?? '-') ?></td></tr>
    <tr><th>Profit</th><td><?= esc($businessNotes['profit'] ?? '-') ?></td></tr>
    <tr><th>Cash-in</th><td><?= esc($businessNotes['cash_in'] ?? '-') ?></td></tr>
    <tr><th>Pesanan</th><td><?= esc($businessNotes['pesanan'] ?? '-') ?></td></tr>
</table>

<br>

<table border="1">
    <tr><th colspan="4">Ringkasan</th></tr>
    <tr><th>Omzet</th><th>Profit Bersih</th><th>Transaksi</th><th>Cash-in</th></tr>
    <tr>
        <td><?= esc($summary['total_sales']) ?></td>
        <td><?= esc($summary['total_profit']) ?></td>
        <td><?= esc($summary['total_transactions']) ?></td>
        <td><?= esc($cashInTotal ?? 0) ?></td>
    </tr>
</table>

<br>

<table border="1">
    <tr><th colspan="2">Cash-in per metode</th></tr>
    <tr><th>Metode</th><th>Total</th></tr>
    <?php foreach ($paymentBreakdown as $breakdown): ?>
        <tr>
            <td><?= esc($breakdown['label'] ?? transaction_payment_method_label((string) ($breakdown['payment_method'] ?? ''))) ?></td>
            <td><?= esc($breakdown['total_amount']) ?></td>
        </tr>
    <?php endforeach ?>
</table>

<br>

<table border="1">
    <tr><th colspan="6">Transaksi</th></tr>
    <tr><th>Invoice</th><th>Pelanggan</th><th>Tanggal</th><th>Status</th><th>Total</th><th>Profit</th></tr>
    <?php foreach ($transactions as $row): ?>
        <tr>
            <td><?= esc($row['invoice']) ?></td>
            <td><?= esc($row['customer_name']) ?></td>
            <td><?= esc(format_indonesian_date($row['transaction_date'])) ?></td>
            <td><?= esc($row['status']) ?></td>
            <td><?= esc($row['total']) ?></td>
            <td><?= esc($row['profit']) ?></td>
        </tr>
    <?php endforeach ?>
</table>

<br>

<table border="1">
    <tr><th colspan="4">Produk terlaris</th></tr>
    <tr><th>Produk</th><th>Terjual</th><th>Omzet</th><th>Profit</th></tr>
    <?php foreach ($topProducts as $product): ?>
        <tr>
            <td><?= esc($product['name']) ?></td>
            <td><?= esc($product['sold_qty']) ?></td>
            <td><?= esc($product['revenue']) ?></td>
            <td><?= esc($product['profit']) ?></td>
        </tr>
    <?php endforeach ?>
</table>
