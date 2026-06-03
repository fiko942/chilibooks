<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CustomerController extends BaseController
{
    private CustomerModel $customers;

    public function __construct()
    {
        $this->customers = new CustomerModel();
    }

    public function index()
    {
        $query = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page_customers') ?: 1));
        $perPage = 8;
        $customers = [];
        $pagerHtml = '';
        $listError = null;

        try {
            $db = db_connect();
            $builder = $db->table('customers c')
                ->select('c.*, COUNT(t.id) transaction_count, COALESCE(SUM(CASE WHEN t.status = "completed" THEN t.total ELSE 0 END),0) total_spent')
                ->join('transactions t', 't.customer_id = c.id', 'left')
                ->where('c.deleted_at', null)
                ->groupBy('c.id');

            if ($query !== '') {
                $builder->groupStart()
                    ->like('c.name', $query)
                    ->orLike('c.location', $query)
                    ->orLike('c.phone', $query)
                    ->groupEnd();
            }

            $total = (int) (clone $builder)->countAllResults();
            $customers = $builder
                ->orderBy('c.name')
                ->get($perPage, ($page - 1) * $perPage)
                ->getResultArray();

            if ($total > $perPage) {
                $pagerHtml = service('pager')
                    ->only(['q'])
                    ->makeLinks($page, $perPage, $total, 'default_full', 0, 'customers');
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Customer list load failed: {message}', ['message' => $exception->getMessage()]);
            $listError = 'Daftar pelanggan belum bisa dimuat karena koneksi database bermasalah.';
        }

        return view('customers/index', [
            'title' => 'Pelanggan',
            'active' => 'customers',
            'customers' => $customers,
            'query' => $query,
            'pagerHtml' => $pagerHtml,
            'listError' => $listError,
        ]);
    }

    public function new()
    {
        return view('customers/create', [
            'title' => 'Tambah Pelanggan',
            'active' => 'customers',
        ]);
    }

    public function show(int $id)
    {
        $customer = $this->customers->find($id);
        $transactions = db_connect()->table('transactions')
            ->where('customer_id', $id)
            ->orderBy('transaction_date', 'DESC')
            ->get()->getResultArray();

        return view('customers/show', ['title' => 'Detail Pelanggan', 'active' => 'customers', 'customer' => $customer, 'transactions' => $transactions]);
    }

    public function create()
    {
        return $this->saveCustomer('/customers', '/customers/new', 'Pelanggan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        return $this->saveCustomer('/customers/' . $id, '/customers/' . $id, 'Pelanggan berhasil diperbarui.', $id);
    }

    public function delete(int $id)
    {
        $this->customers->delete($id);

        return redirect()->to('/customers')->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function saveCustomer(string $successRedirect, string $errorRedirect, string $successMessage, ?int $id = null)
    {
        $payload = $this->payload();
        $validationError = $this->validateCustomerPayload($payload);

        if ($validationError !== null) {
            return redirect()->to($errorRedirect)->withInput()->with('error', $validationError);
        }

        try {
            if ($id === null) {
                $this->customers->insert($payload);
            } else {
                $this->customers->update($id, $payload);
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Customer save failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to($errorRedirect)->withInput()->with('error', 'Data pelanggan belum bisa disimpan karena database bermasalah.');
        }

        $submitMode = (string) $this->request->getPost('submit_mode');
        $redirectTo = $submitMode === 'keep_adding' ? '/customers/new' : $successRedirect;

        return redirect()->to($redirectTo)->with('success', $successMessage);
    }

    private function validateCustomerPayload(array $payload): ?string
    {
        if (mb_strlen($payload['name']) < 3) {
            return 'Nama pelanggan minimal 3 karakter.';
        }

        if ($payload['phone'] !== '' && ! is_valid_indonesian_phone($payload['phone'])) {
            return 'Nomor WhatsApp harus dimulai dengan +62 dan valid.';
        }

        return null;
    }

    private function payload(): array
    {
        return [
            'name' => trim((string) $this->request->getPost('name')),
            'location' => trim((string) $this->request->getPost('location')),
            'phone' => normalize_indonesian_phone((string) $this->request->getPost('phone')),
        ];
    }
}
