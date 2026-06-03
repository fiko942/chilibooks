<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionAuditsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_id' => ['type' => 'INT', 'unsigned' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'actor_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'actor_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'before_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'after_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('actor_user_id', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('transaction_audits', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('transaction_audits', true);
    }
}
