<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class DashboardTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testDashboardShowsGracefulMessageWhenDatabaseLookupFails(): void
    {
        helper('chili');

        $result = $this->withSession(['user_id' => 1])->get('dashboard');

        $result->assertNotRedirect();
        $result->assertSee('Data dashboard belum bisa dimuat karena koneksi database bermasalah.');
        $result->assertSee('Transaksi sepanjang waktu');
        $result->assertSee(month_label(date('Y-m')));
        $result->assertSee(month_label(previous_month_key(date('Y-m'))));
        $result->assertSee(month_label(previous_month_key(previous_month_key(date('Y-m')))));
        $result->assertSee('Tambah Transaksi');
    }

    public function testDashboardExplainsBusinessFormulas(): void
    {
        $html = view('dashboard/index', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'selectedPeriod' => 'all',
            'selectedPeriodLabel' => 'Transaksi sepanjang waktu',
            'monthOptions' => [],
            'trendBadge' => 'Transaksi sepanjang waktu',
            'metrics' => [],
            'trend' => [],
            'loyalCustomers' => [],
            'latestTransactions' => [],
            'dashboardError' => null,
            'referenceMonths' => [
                'current' => 'Juni 2026',
                'previous' => 'Mei 2026',
                'twoMonthsAgo' => 'April 2026',
            ],
            'paymentBreakdown' => [
                ['payment_method' => 'cash', 'total_amount' => 1000],
                ['payment_method' => 'qris', 'total_amount' => 2000],
                ['payment_method' => 'transfer', 'total_amount' => 3000],
            ],
            'businessNotes' => [
                'omzet' => 'Omzet = total transaksi completed pada periode terpilih.',
                'profit' => 'Profit = total jual - modal pada transaksi completed.',
                'cash_in' => 'Cash-in = uang masuk per metode bayar dari transaksi completed.',
                'pesanan' => 'Pesanan = transaksi completed yang selesai dibayar dan siap dihitung sebagai penjualan.',
            ],
        ]);

        $this->assertStringContainsString('Cara hitung angka', $html);
        $this->assertStringContainsString('Cash-in', $html);
        $this->assertStringContainsString('QRIS', $html);
        $this->assertStringContainsString('Transfer', $html);
        $this->assertStringContainsString('Pesanan', $html);
    }
}
