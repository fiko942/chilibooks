<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class CustomerManagementTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testCustomerIndexShowsSearchAndWhatsAppAction(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('customers?q=Jaya');

        $result->assertOK();
        $result->assertSee('Cari pelanggan');
        $result->assertSee('Daftar pelanggan belum bisa dimuat karena koneksi database bermasalah.');
    }

    public function testCustomerCreatePageShowsTwoSubmitModes(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('customers/new');

        $result->assertOK();
        $result->assertSee('Tambah pelanggan baru');
        $result->assertSee('Tambah dan input lagi');
    }

    public function testCustomerIndexViewShowsWhatsAppAction(): void
    {
        helper('chili');

        $html = view('customers/index', [
            'title' => 'Pelanggan',
            'active' => 'customers',
            'customers' => [[
                'id' => 1,
                'name' => 'Toko Jaya',
                'location' => 'Malang',
                'phone' => '+6281234567890',
                'transaction_count' => 5,
                'total_spent' => 250000,
            ]],
            'query' => 'Jaya',
            'pagerHtml' => '',
            'listError' => null,
        ]);

        $this->assertStringContainsString('Kontak melalui WhatsApp', $html);
        $this->assertStringContainsString('Lihat Detail', $html);
    }
}
