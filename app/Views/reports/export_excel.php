<table border="1">
    <tr><th colspan="6">Laporan ChiliBooks <?= esc($start) ?> s/d <?= esc($end) ?></th></tr>
    <tr><th>Invoice</th><th>Pelanggan</th><th>Tanggal</th><th>Status</th><th>Total</th><th>Profit</th></tr>
    <?php foreach ($transactions as $row): ?>
        <tr>
            <td><?= esc($row['invoice']) ?></td>
            <td><?= esc($row['customer_name']) ?></td>
            <td><?= esc($row['transaction_date']) ?></td>
            <td><?= esc($row['status']) ?></td>
            <td><?= esc($row['total']) ?></td>
            <td><?= esc($row['profit']) ?></td>
        </tr>
    <?php endforeach ?>
</table>
