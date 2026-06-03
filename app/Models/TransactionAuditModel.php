<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionAuditModel extends Model
{
    protected $table = 'transaction_audits';
    protected $allowedFields = ['transaction_id', 'event_type', 'actor_user_id', 'actor_name', 'before_payload', 'after_payload', 'notes'];
    protected $useTimestamps = true;
}
