<?php

namespace App\Controllers;

use App\Models\ProductModel;

class ProductController extends BaseController
{
    private ProductModel $products;

    public function __construct()
    {
        $this->products = new ProductModel();
    }

    public function index()
    {
        return view('products/index', [
            'title' => 'Produk & Stok',
            'active' => 'products',
            'products' => $this->products->orderBy('name')->findAll(),
        ]);
    }

    public function new()
    {
        return view('products/form', ['title' => 'Tambah Produk', 'active' => 'products', 'product' => null]);
    }

    public function create()
    {
        $this->products->insert($this->payload());

        return redirect()->to('/products')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        return view('products/form', ['title' => 'Edit Produk', 'active' => 'products', 'product' => $this->products->find($id)]);
    }

    public function update(int $id)
    {
        $this->products->update($id, $this->payload());

        return redirect()->to('/products')->with('success', 'Produk berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $this->products->delete($id);

        return redirect()->to('/products')->with('success', 'Produk berhasil dihapus.');
    }

    private function payload(): array
    {
        $stock = (int) $this->request->getPost('stock');

        return [
            'name' => trim((string) $this->request->getPost('name')),
            'sku' => trim((string) $this->request->getPost('sku')),
            'selling_price' => (float) $this->request->getPost('selling_price'),
            'cost_price' => (float) $this->request->getPost('cost_price'),
            'stock' => $stock,
            'status' => $stock <= 10 ? 'low' : 'active',
        ];
    }
}
