<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    private CustomerModel $customers;

    public function __construct()
    {
        $this->customers = new CustomerModel();
    }

    public function index()
    {
        $db = db_connect();
        $customers = $db->table('customers c')
            ->select('c.*, COUNT(t.id) transaction_count, COALESCE(SUM(CASE WHEN t.status = "completed" THEN t.total ELSE 0 END),0) total_spent')
            ->join('transactions t', 't.customer_id = c.id', 'left')
            ->where('c.deleted_at', null)
            ->groupBy('c.id')
            ->orderBy('c.name')
            ->get()->getResultArray();

        return view('customers/index', ['title' => 'Pelanggan', 'active' => 'customers', 'customers' => $customers]);
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
        $this->customers->insert($this->payload());

        return redirect()->to('/customers')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $this->customers->update($id, $this->payload());

        return redirect()->to('/customers/' . $id)->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $this->customers->delete($id);

        return redirect()->to('/customers')->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function payload(): array
    {
        return [
            'name' => trim((string) $this->request->getPost('name')),
            'location' => trim((string) $this->request->getPost('location')),
            'phone' => trim((string) $this->request->getPost('phone')),
        ];
    }
}
