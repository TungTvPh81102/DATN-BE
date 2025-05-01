<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\InstructorCommission;
use App\Models\Invoice;
use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Str;

class RenderInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $startDate = Carbon::create(2024, 1, 1);
            $endDate = Carbon::create(2025, 4, 30);

            $members = User::whereHas('roles', function ($q) {
                $q->where('name', 'member');
            })->pluck('id');

            $courses = Course::with('instructor')
                ->where('status', 'draft')
                ->where('is_free', '!=', 1)
                ->where(function ($q) {
                    $q->where('price', '>', 0)
                        ->orWhere('price_sale', '>', 0);
                })
                ->get();

            $systemFund = SystemFund::firstOrCreate([], [
                'balance' => 0,
                'pending_balance' => 0,
            ]);

            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                $daysInMonth = $currentDate->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $currentDate->copy()->setDay($day);

                    $course = $courses->random();
                    $userId = $members->random();
                    $amount = $course->price_sale > 0 ? $course->price_sale : $course->price;

                    if ($amount <= 0) {
                        continue; 
                    }

                    $commission = InstructorCommission::where('instructor_id', $course->instructor->id)->first();
                    $rate = $commission?->rate ?? 0.6;
                    $instructorCommission = round($amount * $rate);
                    $systemAmount = $amount - $instructorCommission;

                    $this->command->info("Ngày: {$date->toDateString()} | Khóa học #{$course->id} | Giá: {$amount} | Tỷ lệ: {$rate}% | GV: {$instructorCommission} | HT: {$systemAmount}");

                    $paymentMethods = ['vnpay', 'momo'];

                    $invoice = Invoice::create([
                        'user_id' => $userId,
                        'course_id' => $course->id,
                        'code' => \Illuminate\Support\Str::uuid(),
                        'amount' => $amount,
                        'final_amount' => $amount,
                        'status' => 'Đã thanh toán',
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'invoice_type' => 'course',
                        'instructor_commissions' => $instructorCommission,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $transaction = Transaction::create([
                        'transaction_code' => strtoupper(\Illuminate\Support\Str::random(10)),
                        'transactionable_id' => $invoice->id,
                        'transactionable_type' => Invoice::class,
                        'amount' => $amount,
                        'status' => 'Giao dịch thành công',
                        'type' => 'invoice',
                        'user_id' => $userId,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    SystemFundTransaction::create([
                        'transaction_id' => $transaction->id,
                        'user_id' => $userId,
                        'total_amount' => $amount,
                        'retained_amount' => $systemAmount,
                        'type' => 'commission_received',
                        'description' => 'Học viên mua khoá học #' . $course->id,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $systemFund->increment('balance', $systemAmount);

                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $course->instructor->id],
                        ['balance' => 0, 'status' => 1]
                    );
                    $wallet->increment('balance', $instructorCommission);
                }

                $currentDate->addMonth();
            }
        });
    }
}
