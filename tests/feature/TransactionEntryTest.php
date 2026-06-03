<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class TransactionEntryTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testTransactionFormRendersDynamicControls(): void
    {
        $result = $this->withSession(['user_id' => 1])->get('transactions/new');

        $result->assertOK();
        $result->assertSee('Tambah Produk');
        $result->assertSee('Bukti pembayaran');
        $result->assertSee('Diambil di tempat');
        $result->assertSee('Klik untuk pilih tanggal');
    }

    public function testTransactionCreateRejectsInvalidPaymentMethodBeforeDatabaseLookup(): void
    {
        $result = $this->withSession(['user_id' => 1])->post('transactions', [
            'customer_id' => 1,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'bitcoin',
            'delivery_type' => 'diantar',
            'status' => 'completed',
            'product_id' => ['1'],
            'qty' => ['1'],
        ]);

        $result->assertRedirectTo('transactions/new');
        $result->assertSessionHas('error', 'Metode pembayaran tidak valid.');
    }

    public function testTransactionCreateRequiresProofForQris(): void
    {
        $result = $this->withSession(['user_id' => 1])->post('transactions', [
            'customer_id' => 1,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'qris',
            'delivery_type' => 'diantar',
            'status' => 'completed',
            'product_id' => ['1'],
            'qty' => ['1'],
        ]);

        $result->assertRedirectTo('transactions/new');
        $result->assertSessionHas('error', 'Bukti pembayaran wajib diunggah untuk QRIS atau transfer.');
    }

    public function testTransactionCreateRejectsInvalidDeliveryTypeBeforeDatabaseLookup(): void
    {
        $result = $this->withSession(['user_id' => 1])->post('transactions', [
            'customer_id' => 1,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'cash',
            'delivery_type' => 'dine_in',
            'status' => 'completed',
            'product_id' => ['1'],
            'qty' => ['1'],
        ]);

        $result->assertRedirectTo('transactions/new');
        $result->assertSessionHas('error', 'Jenis pengantaran tidak valid.');
    }
}
