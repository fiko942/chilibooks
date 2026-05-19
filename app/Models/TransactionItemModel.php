<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionItemModel extends Model
{
    protected $table = 'transaction_items';
    protected $allowedFields = ['transaction_id', 'product_id', 'qty', 'selling_price_snapshot', 'cost_price_snapshot', 'subtotal', 'profit'];
    protected $useTimestamps = true;
}
