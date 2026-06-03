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
        $result->assertSee('All');
        $result->assertSee(month_label(date('Y-m')));
        $result->assertSee(month_label(previous_month_key(date('Y-m'))));
        $result->assertSee(month_label(previous_month_key(previous_month_key(date('Y-m')))));
        $result->assertSee('Tambah Transaksi');
    }
}
