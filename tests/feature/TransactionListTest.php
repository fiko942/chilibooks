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

    public function testPublicInvoiceViewRendersCustomerAndInvoice(): void
    {
        helper('chili');

        $html = view('invoices/public', [
            'title' => 'Invoice INV-260603-0001',
            'transaction' => [
                'invoice' => 'INV-260603-0001',
                'transaction_date' => '2026-06-03',
                'paid_at' => '2026-06-03 18:30:00',
                'payment_method' => 'cash',
                'delivery_type' => 'diantar',
                'total' => 125000,
            ],
            'customer' => [
                'name' => 'Rani Putri',
            ],
            'items' => [[
                'product_name' => 'Chili Crunch',
                'qty' => 2,
                'subtotal' => 125000,
            ]],
            'invoiceUrl' => 'http://example.com/invoice/1',
        ]);

        $this->assertStringContainsString('Chili Oil Gen Z', $html);
        $this->assertStringContainsString('Terima kasih telah berlangganan.', $html);
        $this->assertStringContainsString('Rani Putri', $html);
        $this->assertStringContainsString('INV-260603-0001', $html);
    }

    public function testTransactionShowViewRendersAuditTimeline(): void
    {
        helper('chili');

        $html = view('transactions/show', [
            'title' => 'Detail Transaksi',
            'active' => 'transactions',
            'transaction' => [
                'id' => 12,
                'invoice' => 'INV-260603-0012',
                'customer_name' => 'Rani Putri',
                'transaction_date' => '2026-06-03',
                'status' => 'canceled',
                'payment_method' => 'cash',
                'delivery_type' => 'diantar',
                'paid_at' => '2026-06-03 18:30:00',
                'payment_proof_path' => null,
                'subtotal' => 150000,
                'discount' => 0,
                'extra_fee' => 0,
                'total' => 0,
                'profit' => 0,
            ],
            'items' => [[
                'product_name' => 'Chili Crunch',
                'qty' => 2,
                'selling_price_snapshot' => 75000,
                'cost_price_snapshot' => 50000,
                'subtotal' => 150000,
            ]],
            'transactionAudits' => [[
                'event_type' => 'canceled',
                'actor_name' => 'Owner',
                'created_at' => '2026-06-03 18:40:00',
                'notes' => 'Transaksi dibatalkan dan stok dikembalikan.',
                'before_payload' => [
                    'status' => 'completed',
                    'total' => 150000,
                ],
                'after_payload' => [
                    'status' => 'canceled',
                    'total' => 0,
                    'stock_changes' => [[
                        'product_name' => 'Chili Crunch',
                        'qty' => 2,
                        'stock_before' => 8,
                        'stock_after' => 10,
                    ]],
                ],
            ]],
        ]);

        $this->assertStringContainsString('Jejak perubahan', $html);
        $this->assertStringContainsString('Dibatalkan', $html);
        $this->assertStringContainsString('Owner', $html);
        $this->assertStringContainsString('Chili Crunch', $html);
        $this->assertStringContainsString('Stok 8 → 10', $html);
    }
}
