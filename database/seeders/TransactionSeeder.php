<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id')->all();
        
        for ($i = 1; $i <= 100; $i++) {
            Transaction::query()->create([
                'transactionable_type' => 'App\\Models\\User',
                'transactionable_id' => fake()->randomElement($users),
                'amount' => fake()->randomFloat(2, 10000, 500000),
                'coin' => fake()->randomFloat(2, 100, 5000),
                'status' => fake()->randomElement(['Đang xử lý', 'Thành công', 'Thất bại']),
            ]);
        }
    }
}
