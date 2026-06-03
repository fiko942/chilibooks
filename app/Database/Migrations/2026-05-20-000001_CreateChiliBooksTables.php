<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChiliBooksTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'email' => ['type' => 'VARCHAR', 'constraint' => 160],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'owner'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'selling_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'cost_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'stock' => ['type' => 'INT', 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('products', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'location' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('customers', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'invoice' => ['type' => 'VARCHAR', 'constraint' => 50],
            'customer_id' => ['type' => 'INT', 'unsigned' => true],
            'transaction_date' => ['type' => 'DATE'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'completed'],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'discount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'extra_fee' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'profit' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'cash'],
            'payment_proof_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'delivery_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'diantar'],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('invoice');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('transactions', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_id' => ['type' => 'INT', 'unsigned' => true],
            'product_id' => ['type' => 'INT', 'unsigned' => true],
            'qty' => ['type' => 'INT', 'default' => 1],
            'selling_price_snapshot' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'cost_price_snapshot' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'profit' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('transaction_items', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'INT', 'unsigned' => true],
            'transaction_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'qty' => ['type' => 'INT'],
            'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('stock_movements', true);
    }

    public function down(): void
    {
        foreach (['stock_movements', 'transaction_items', 'transactions', 'customers', 'products', 'users'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
