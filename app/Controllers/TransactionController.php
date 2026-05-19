<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\StockMovementModel;
use App\Models\TransactionItemModel;
use App\Models\TransactionModel;

class TransactionController extends BaseController
{
    public function index()
    {
        $rows = db_connect()->table('transactions t')
            ->select('t.*, c.name customer_name')
            ->join('customers c', 'c.id = t.customer_id')
            ->orderBy('t.transaction_date', 'DESC')
            ->orderBy('t.id', 'DESC')
            ->get()->getResultArray();

        return view('transactions/index', ['title' => 'Riwayat Transaksi', 'active' => 'transactions', 'transactions' => $rows]);
    }

    public function new()
    {
        return view('transactions/form', [
            'title' => 'Input Transaksi',
            'active' => 'transactions',
            'customers' => (new CustomerModel())->orderBy('name')->findAll(),
            'products' => (new ProductModel())->orderBy('name')->findAll(),
        ]);
    }

    public function create()
    {
        $productIds = $this->request->getPost('product_id') ?? [];
        $qtys = $this->request->getPost('qty') ?? [];
        $productModel = new ProductModel();
        $items = [];
        $subtotal = 0;
        $profit = 0;

        foreach ($productIds as $idx => $productId) {
            $qty = max(0, (int) ($qtys[$idx] ?? 0));
            if (! $productId || $qty < 1) {
                continue;
            }

            $product = $productModel->find((int) $productId);
            if (! $product) {
                continue;
            }

            $lineSubtotal = (float) $product['selling_price'] * $qty;
            $lineProfit = ((float) $product['selling_price'] - (float) $product['cost_price']) * $qty;
            $subtotal += $lineSubtotal;
            $profit += $lineProfit;
            $items[] = compact('product', 'qty', 'lineSubtotal', 'lineProfit');
        }

        if ($items === []) {
            return redirect()->back()->withInput()->with('error', 'Minimal pilih satu produk.');
        }

        $discount = (float) $this->request->getPost('discount');
        $extraFee = (float) $this->request->getPost('extra_fee');
        $status = $this->request->getPost('status') === 'canceled' ? 'canceled' : 'completed';
        $total = max(0, $subtotal - $discount + $extraFee);
        if ($status === 'canceled') {
            $total = 0;
            $profit = 0;
        }

        $db = db_connect();
        $db->transStart();

        $transactionId = (new TransactionModel())->insert([
            'invoice' => 'INV-' . date('ymd-His'),
            'customer_id' => (int) $this->request->getPost('customer_id'),
            'transaction_date' => $this->request->getPost('transaction_date') ?: date('Y-m-d'),
            'status' => $status,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'extra_fee' => $extraFee,
            'total' => $total,
            'profit' => $profit,
            'notes' => $this->request->getPost('notes'),
        ], true);

        $itemModel = new TransactionItemModel();
        $movementModel = new StockMovementModel();
        foreach ($items as $item) {
            $product = $item['product'];
            $itemModel->insert([
                'transaction_id' => $transactionId,
                'product_id' => $product['id'],
                'qty' => $item['qty'],
                'selling_price_snapshot' => $product['selling_price'],
                'cost_price_snapshot' => $product['cost_price'],
                'subtotal' => $status === 'completed' ? $item['lineSubtotal'] : 0,
                'profit' => $status === 'completed' ? $item['lineProfit'] : 0,
            ]);

            if ($status === 'completed') {
                $newStock = max(0, (int) $product['stock'] - $item['qty']);
                $productModel->update($product['id'], ['stock' => $newStock, 'status' => $newStock <= 10 ? 'low' : 'active']);
                $movementModel->insert([
                    'product_id' => $product['id'],
                    'transaction_id' => $transactionId,
                    'type' => 'sale',
                    'qty' => -$item['qty'],
                    'notes' => 'Transaksi penjualan',
                ]);
            }
        }

        $db->transComplete();

        return redirect()->to('/transactions/' . $transactionId)->with('success', 'Transaksi berhasil disimpan.');
    }

    public function show(int $id)
    {
        $transaction = db_connect()->table('transactions t')
            ->select('t.*, c.name customer_name')
            ->join('customers c', 'c.id = t.customer_id')
            ->where('t.id', $id)
            ->get()->getRowArray();
        $items = db_connect()->table('transaction_items ti')
            ->select('ti.*, p.name product_name')
            ->join('products p', 'p.id = ti.product_id')
            ->where('ti.transaction_id', $id)
            ->get()->getResultArray();

        return view('transactions/show', ['title' => 'Detail Transaksi', 'active' => 'transactions', 'transaction' => $transaction, 'items' => $items]);
    }

    public function cancel(int $id)
    {
        $transaction = (new TransactionModel())->find($id);
        if ($transaction && $transaction['status'] === 'completed') {
            $productModel = new ProductModel();
            $movementModel = new StockMovementModel();
            $items = (new TransactionItemModel())->where('transaction_id', $id)->findAll();

            foreach ($items as $item) {
                $product = $productModel->find((int) $item['product_id']);
                if (! $product) {
                    continue;
                }

                $newStock = (int) $product['stock'] + (int) $item['qty'];
                $productModel->update($product['id'], ['stock' => $newStock, 'status' => $newStock <= 10 ? 'low' : 'active']);
                $movementModel->insert([
                    'product_id' => $product['id'],
                    'transaction_id' => $id,
                    'type' => 'cancel',
                    'qty' => (int) $item['qty'],
                    'notes' => 'Pengembalian stok karena transaksi dibatalkan',
                ]);
            }

            (new TransactionModel())->update($id, ['status' => 'canceled', 'total' => 0, 'profit' => 0]);
        }

        return redirect()->to('/transactions/' . $id)->with('success', 'Transaksi ditandai dibatalkan.');
    }
}
