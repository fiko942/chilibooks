<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class TransactionListTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testTransactionIndexRendersSearchField(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('transactions?q=INV-');

        $result->assertOK();
        $result->assertSee('Cari transaksi');
    }

    public function testTransactionIndexViewShowsInvoiceActionForEachRow(): void
    {
        helper('chili');

        $html = view('transactions/index', [
            'title' => 'Riwayat Transaksi',
            'active' => 'transactions',
            'transactions' => [[
                'id' => 99,
                'invoice' => 'INV-260603-0001',
                'customer_name' => 'Pelanggan Test',
                'transaction_date' => '2026-06-03',
                'total' => 125000,
                'profit' => 25000,
                'status' => 'completed',
            ]],
            'query' => 'INV-',
            'pagerHtml' => '',
            'listError' => null,
        ]);

        $this->assertStringContainsString('Kirim Invoice', $html);
        $this->assertStringContainsString('Detail', $html);
    }
}
