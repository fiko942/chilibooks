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
        $result = $this->withSession(['user_id' => 1])->get('dashboard');

        $result->assertNotRedirect();
        $result->assertSee('Data dashboard belum bisa dimuat karena koneksi database bermasalah.');
    }
}
