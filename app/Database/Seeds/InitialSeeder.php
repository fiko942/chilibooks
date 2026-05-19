<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        if (! $this->db->table('users')->where('email', 'owner@chilioilgenz.id')->countAllResults()) {
            $this->db->table('users')->insert([
                'name' => 'Owner Chili Oil Gen Z',
                'email' => 'owner@chilioilgenz.id',
                'password_hash' => password_hash('officer123', PASSWORD_DEFAULT),
                'role' => 'owner',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $products = [
            ['Chili Oil Original 150ml', 'CO-150-ORI', 38000, 22000, 42],
            ['Chili Oil Extra Hot 150ml', 'CO-150-HOT', 42000, 24000, 18],
            ['Paket Bundling 3 Jar', 'CO-BND-3', 110000, 69000, 9],
            ['Rice Bowl Chili Oil', 'RB-CHO-01', 18000, 11000, 27],
        ];
        foreach ($products as [$name, $sku, $sell, $cost, $stock]) {
            if ($this->db->table('products')->where('sku', $sku)->countAllResults()) {
                continue;
            }
            $this->db->table('products')->insert([
                'name' => $name,
                'sku' => $sku,
                'selling_price' => $sell,
                'cost_price' => $cost,
                'stock' => $stock,
                'status' => $stock <= 10 ? 'low' : 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([['Fhatir Aldo', 'Donomulyo'], ['Rani Putri', 'Kepanjen'], ['Ilham Pratama', 'Sumberoto'], ['Nadia Safira', 'Malang']] as [$name, $location]) {
            if ($this->db->table('customers')->where('name', $name)->countAllResults()) {
                continue;
            }
            $this->db->table('customers')->insert([
                'name' => $name,
                'location' => $location,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
