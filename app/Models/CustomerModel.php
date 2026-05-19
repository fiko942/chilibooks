<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $allowedFields = ['name', 'location', 'phone'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
}
