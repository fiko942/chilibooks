<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $allowedFields = ['name', 'sku', 'selling_price', 'cost_price', 'stock', 'status'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
}
