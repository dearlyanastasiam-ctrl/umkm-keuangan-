<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin Sistem', 'email' => 'admin@umkm.test', 'password' => 'password', 'role' => 'admin']);
        $owner = User::create([
            'name' => 'Bu Sari', 'email' => 'owner@umkm.test', 'password' => 'password',
            'role' => 'owner', 'business_name' => 'Warung Kopi Sari', 'profit_target' => 3000000,
        ]);

        $cats = [];
        foreach ([
            ['Penjualan Kopi', 'income', false], ['Penjualan Makanan', 'income', false], ['Pendapatan Lain', 'income', false],
            ['Bahan Baku', 'expense', true], ['Kemasan', 'expense', true],
            ['Gaji Karyawan', 'expense', false], ['Listrik & Air', 'expense', false], ['Sewa Tempat', 'expense', false],
            ['Transportasi', 'expense', false], ['Promosi', 'expense', false],
        ] as [$name, $type, $hpp]) {
            $cats[$name] = Category::create(['name' => $name, 'type' => $type, 'is_hpp' => $hpp]);
        }

        // Data contoh 6 bulan terakhir
        for ($m = 5; $m >= 0; $m--) {
            $base = now()->subMonthsNoOverflow($m)->startOfMonth();
            for ($d = 1; $d <= 24; $d += 2) {
                $date = $base->copy()->addDays($d - 1)->toDateString();
                $owner->transactions()->create(['category_id' => $cats['Penjualan Kopi']->id, 'date' => $date, 'amount' => rand(250, 600) * 1000, 'description' => 'Penjualan harian']);
                $owner->transactions()->create(['category_id' => $cats['Penjualan Makanan']->id, 'date' => $date, 'amount' => rand(100, 300) * 1000, 'description' => 'Penjualan makanan']);
            }
            foreach ([['Bahan Baku', rand(2500, 3500) * 1000], ['Kemasan', rand(300, 500) * 1000], ['Gaji Karyawan', 2000000],
                      ['Listrik & Air', rand(350, 500) * 1000], ['Sewa Tempat', 1500000], ['Transportasi', rand(150, 300) * 1000], ['Promosi', rand(100, 400) * 1000]] as [$c, $amt]) {
                $owner->transactions()->create(['category_id' => $cats[$c]->id, 'date' => $base->copy()->addDays(rand(0, 26))->toDateString(), 'amount' => $amt, 'description' => $c]);
            }
        }
    }
}
