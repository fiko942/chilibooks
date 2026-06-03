<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $allowedFields = ['invoice', 'customer_id', 'transaction_date', 'status', 'subtotal', 'discount', 'extra_fee', 'total', 'profit', 'payment_method', 'payment_proof_path', 'delivery_type', 'notes'];
    protected $useTimestamps = true;
}
