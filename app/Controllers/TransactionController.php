<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\StockMovementModel;
use App\Models\TransactionItemModel;
use App\Models\TransactionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTimeImmutable;
use Throwable;

class TransactionController extends BaseController
{
    private const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
    ];

    private const DELIVERY_TYPES = [
        'diantar' => 'Diantar',
        'diambil_di_tempat' => 'Diambil di tempat',
    ];

    public function index()
    {
        $query = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page_transactions') ?: 1));
        $perPage = 8;
        $rows = [];
        $pagerHtml = '';
        $listError = null;

        try {
            $db = db_connect();
            $builder = $db->table('transactions t')
                ->select('t.*, c.name customer_name')
                ->join('customers c', 'c.id = t.customer_id');

            if ($query !== '') {
                $builder->groupStart()
                    ->like('t.invoice', $query)
                    ->orLike('c.name', $query)
                    ->groupEnd();
            }

            $total = (int) (clone $builder)->countAllResults();

            $rows = $builder
                ->orderBy('t.transaction_date', 'DESC')
                ->orderBy('t.id', 'DESC')
                ->get($perPage, ($page - 1) * $perPage)
                ->getResultArray();

            if ($total > $perPage) {
                $pagerHtml = service('pager')
                    ->only(['q'])
                    ->makeLinks($page, $perPage, $total, 'default_full', 0, 'transactions');
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Transaction list load failed: {message}', ['message' => $exception->getMessage()]);
            $listError = 'Daftar transaksi belum bisa dimuat karena koneksi database bermasalah.';
        }

        return view('transactions/index', [
            'title' => 'Riwayat Transaksi',
            'active' => 'transactions',
            'transactions' => $rows,
            'query' => $query,
            'pagerHtml' => $pagerHtml,
            'listError' => $listError,
        ]);
    }

    public function new()
    {
        try {
            $customers = (new CustomerModel())->orderBy('name')->findAll();
            $products = (new ProductModel())->orderBy('name')->findAll();
            $formError = null;
        } catch (DatabaseException $exception) {
            log_message('error', 'Transaction form load failed: {message}', ['message' => $exception->getMessage()]);
            $customers = [];
            $products = [];
            $formError = 'Data pelanggan atau produk belum bisa dimuat karena koneksi database bermasalah.';
        }

        return view('transactions/form', [
            'title' => 'Input Transaksi',
            'active' => 'transactions',
            'customers' => $customers,
            'products' => $products,
            'paymentMethods' => self::PAYMENT_METHODS,
            'deliveryTypes' => self::DELIVERY_TYPES,
            'formError' => $formError,
        ]);
    }

    public function create()
    {
        $formUrl = site_url('transactions/new');
        $customerId = (int) $this->request->getPost('customer_id');
        $transactionDate = (string) ($this->request->getPost('transaction_date') ?: date('Y-m-d'));
        $paymentMethod = (string) ($this->request->getPost('payment_method') ?: 'cash');
        $deliveryType = (string) ($this->request->getPost('delivery_type') ?: 'diantar');
        $status = $this->request->getPost('status') === 'canceled' ? 'canceled' : 'completed';
        $discount = (float) $this->request->getPost('discount');
        $extraFee = (float) $this->request->getPost('extra_fee');
        $notes = (string) $this->request->getPost('notes');
        $db = null;
        $paymentProofPath = null;

        if (! $this->isValidDate($transactionDate)) {
            return redirect()->to($formUrl)->withInput()->with('error', 'Tanggal transaksi tidak valid.');
        }

        try {
            if (! array_key_exists($paymentMethod, self::PAYMENT_METHODS)) {
                return redirect()->to($formUrl)->withInput()->with('error', 'Metode pembayaran tidak valid.');
            }

            if (! array_key_exists($deliveryType, self::DELIVERY_TYPES)) {
                return redirect()->to($formUrl)->withInput()->with('error', 'Jenis pengantaran tidak valid.');
            }

            $proofRequired = in_array($paymentMethod, ['qris', 'transfer'], true);
            $proofFile = $this->request->getFile('payment_proof');

            if ($proofRequired && (! $proofFile || ! $proofFile->isValid())) {
                return redirect()->to($formUrl)->withInput()->with('error', 'Bukti pembayaran wajib diunggah untuk QRIS atau transfer.');
            }

            if ($customerId < 1 || ! (new CustomerModel())->find($customerId)) {
                return redirect()->to($formUrl)->withInput()->with('error', 'Pelanggan belum dipilih atau tidak valid.');
            }

            $productIds = (array) ($this->request->getPost('product_id') ?? []);
            $qtys = (array) ($this->request->getPost('qty') ?? []);
            [$items, $itemsError] = $this->collectItems($productIds, $qtys);

            if ($itemsError !== null) {
                return redirect()->to($formUrl)->withInput()->with('error', $itemsError);
            }

            if ($proofFile && $proofFile->isValid() && ! $proofFile->hasMoved()) {
                $paymentProofPath = $this->storePaymentProof($proofFile);
                if ($paymentProofPath === null) {
                    return redirect()->to($formUrl)->withInput()->with('error', 'Bukti pembayaran gagal disimpan.');
                }
            }

            $subtotal = 0.0;
            $profit = 0.0;

            foreach ($items as $item) {
                $subtotal += $item['lineSubtotal'];
                $profit += $item['lineProfit'];
            }

            $total = max(0, $subtotal - $discount + $extraFee);
            if ($status === 'canceled') {
                $total = 0;
                $profit = 0;
            }

            $db = db_connect();
            $db->transStart();

            $transactionId = (new TransactionModel())->insert([
                'invoice' => 'INV-' . date('ymd-His'),
                'customer_id' => $customerId,
                'transaction_date' => $transactionDate,
                'status' => $status,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'extra_fee' => $extraFee,
                'total' => $total,
                'profit' => $profit,
                'payment_method' => $paymentMethod,
                'payment_proof_path' => $paymentProofPath,
                'delivery_type' => $deliveryType,
                'notes' => $notes,
            ], true);

            $itemModel = new TransactionItemModel();
            $movementModel = new StockMovementModel();
            $productModel = new ProductModel();

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

            if (! $db->transStatus()) {
                $this->deletePaymentProof($paymentProofPath);
                return redirect()->to($formUrl)->withInput()->with('error', 'Transaksi gagal disimpan karena database bermasalah.');
            }
        } catch (DatabaseException $exception) {
            $this->deletePaymentProof($paymentProofPath);
            log_message('error', 'Transaction save failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to($formUrl)->withInput()->with('error', 'Data transaksi belum bisa diproses karena database bermasalah.');
        } catch (Throwable $exception) {
            if ($db) {
                $db->transRollback();
            }
            $this->deletePaymentProof($paymentProofPath);
            log_message('error', 'Transaction save failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to($formUrl)->withInput()->with('error', 'Transaksi gagal disimpan.');
        }

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

    public function proof(int $id)
    {
        $transaction = (new TransactionModel())->find($id);
        if (! $transaction) {
            return redirect()->to('/transactions/' . $id)->with('error', 'Transaksi tidak ditemukan.');
        }

        $proofPath = $transaction['payment_proof_path'] ?? null;

        if (empty($proofPath)) {
            return redirect()->to('/transactions/' . $id)->with('error', 'Bukti pembayaran tidak tersedia.');
        }

        $absolutePath = WRITEPATH . ltrim($proofPath, '/');
        if (! is_file($absolutePath)) {
            return redirect()->to('/transactions/' . $id)->with('error', 'File bukti pembayaran tidak ditemukan.');
        }

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $fileName = basename($absolutePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->setBody((string) file_get_contents($absolutePath));
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

    private function collectItems(array $productIds, array $qtys): array
    {
        $productModel = new ProductModel();
        $items = [];
        $indices = array_keys(array_replace($productIds, $qtys));

        foreach ($indices as $index) {
            $productId = trim((string) ($productIds[$index] ?? ''));
            $qtyRaw = trim((string) ($qtys[$index] ?? ''));

            if ($productId === '' && $qtyRaw === '') {
                continue;
            }

            if ($productId === '' || $qtyRaw === '' || ! ctype_digit($qtyRaw) || (int) $qtyRaw < 1) {
                return [null, 'Isi produk dan qty dengan benar di setiap baris.'];
            }

            $product = $productModel->find((int) $productId);
            if (! $product) {
                return [null, 'Produk yang dipilih tidak valid.'];
            }

            $qty = (int) $qtyRaw;
            $lineSubtotal = (float) $product['selling_price'] * $qty;
            $lineProfit = ((float) $product['selling_price'] - (float) $product['cost_price']) * $qty;

            $items[] = compact('product', 'qty', 'lineSubtotal', 'lineProfit');
        }

        if ($items === []) {
            return [null, 'Minimal pilih satu produk.'];
        }

        return [$items, null];
    }

    private function storePaymentProof($file): ?string
    {
        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            return null;
        }

        $directory = WRITEPATH . 'uploads/payments/';
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return null;
        }

        $newName = $file->getRandomName();
        if (! $file->move($directory, $newName)) {
            return null;
        }

        return 'uploads/payments/' . $newName;
    }

    private function deletePaymentProof(?string $proofPath): void
    {
        if (empty($proofPath)) {
            return;
        }

        $absolutePath = WRITEPATH . ltrim($proofPath, '/');
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $parsed instanceof DateTimeImmutable && $parsed->format('Y-m-d') === $date;
    }
}
