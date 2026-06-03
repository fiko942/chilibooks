<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CustomerHelperTest extends CIUnitTestCase
{
    public function testCustomerPhoneHelpersNormalizeIndonesianNumbers(): void
    {
        helper('chili');

        $this->assertSame('+6281234567890', normalize_indonesian_phone('0812-3456-7890'));
        $this->assertSame('+6281234567890', normalize_indonesian_phone('6281234567890'));
        $this->assertTrue(is_valid_indonesian_phone('+6281234567890'));
        $this->assertFalse(is_valid_indonesian_phone('1234'));
    }

    public function testCustomerWhatsAppUrlIncludesPrefilledText(): void
    {
        helper('chili');

        $url = customer_whatsapp_url('+6281234567890', 'Toko Jaya', 'Malang');

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString(rawurlencode('Toko Jaya'), $url);
        $this->assertTrue(customer_can_contact_whatsapp('Toko Jaya', '+6281234567890'));
        $this->assertFalse(customer_can_contact_whatsapp('Ab', '+6281234567890'));
    }

    public function testTransactionInvoiceWhatsappMessageIncludesInvoiceLink(): void
    {
        helper('chili');

        $url = transaction_invoice_whatsapp_url([
            'invoice' => 'INV-260603-0001',
            'transaction_date' => '2026-06-03',
            'paid_at' => '2026-06-03 18:30:00',
            'payment_method' => 'cash',
            'delivery_type' => 'diantar',
            'total' => 125000,
        ], [
            'name' => 'Rani Putri',
            'phone' => '+6281234567890',
        ], [
            ['product_name' => 'Chili Crunch', 'qty' => 2],
        ], 'http://example.com/invoice/1');

        $this->assertNotNull($url);
        $this->assertStringContainsString(rawurlencode('Terima kasih telah berlangganan, Rani Putri.'), $url);
        $this->assertStringContainsString(rawurlencode('Lihat invoice: http://example.com/invoice/1'), $url);
    }
}
