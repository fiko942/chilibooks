<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table = 'stock_movements';
    protected $allowedFields = ['product_id', 'transaction_id', 'type', 'qty', 'notes'];
    protected $useTimestamps = true;
}
