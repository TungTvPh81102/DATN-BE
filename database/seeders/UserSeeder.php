<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    protected $lastNames = [
        'Nguyễn',
        'Trần',
        'Lê',
        'Phạm',
        'Hoàng',
        'Huỳnh',
        'Phan',
        'Vũ',
        'Võ',
        'Đặng',
        'Bùi',
        'Đỗ',
        'Hồ',
        'Ngô',
        'Dương',
        'Lý'
    ];

    protected $middleNames = [
        'Văn',
        'Thị',
        'Hữu',
        'Gia',
        'Minh',
        'Thanh',
        'Ngọc',
        'Anh',
        'Bảo',
        'Nhật',
        'Quốc',
        'Xuân',
        'Khánh',
        'Tuấn',
        'Thành',
        'Phương'
    ];

    protected $firstNames = [
        'An',
        'Bình',
        'Châu',
        'Dương',
        'Giang',
        'Hà',
        'Hải',
        'Hân',
        'Hiếu',
        'Hùng',
        'Khoa',
        'Khôi',
        'Lam',
        'Linh',
        'Long',
        'Mai',
        'Minh',
        'My',
        'Nam',
        'Ngân',
        'Ngọc',
        'Nhung',
        'Phát',
        'Phong',
        'Phúc',
        'Quân',
        'Quỳnh',
        'Sơn',
        'Thảo',
        'Thư',
        'Trang',
        'Trung',
        'Tú',
        'Tuấn',
        'Vy',
        'Yến'
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $avatarUrl = 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1743316311/users/34wNL3FsA1.png';

            $total = 500;
            $start = \Carbon\Carbon::create(2024, 1, 1);
            $months = \Carbon\Carbon::now()->diffInMonths($start) + 1;
            $usersPerMonth = intdiv($total, $months);
            $remainder = $total % $months;

            $index = 0;

            for ($m = 0; $m < $months; $m++) {
                $month = $start->copy()->addMonths($m);
                $countThisMonth = $usersPerMonth + ($m < $remainder ? 1 : 0); 

                for ($i = 0; $i < $countThisMonth; $i++) {
                    do {
                        $uuid = strtolower(str_replace('-', '', Str::uuid()->toString()));
                        $code = substr($uuid, 0, 10);
                    } while (User::query()->where('code', $code)->exists());

                    $name = $this->generateVietnameseName();
                    $email = Str::slug($name, '.') . $index . '@example.com';

                    $createdAt = $this->randomDate($month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString());

                    $user = User::create([
                        'code' => $code,
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('Password123'),
                        'avatar' => $avatarUrl,
                        'email_verified_at' => $createdAt,
                        'status' => User::STATUS_ACTIVE,
                        'is_temporary' => false,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $user->assignRole('member');

                    $index++;
                }
            }
        });
    }


    private function generateVietnameseName()
    {
        $lastName = collect($this->lastNames)->random();
        $middleName = collect($this->middleNames)->random();
        $firstName = collect($this->firstNames)->random();

        return "{$lastName} {$middleName} {$firstName}";
    }

    private function randomDate($startDate, $endDate)
    {
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);

        return date('Y-m-d H:i:s', $randomTimestamp);
    }
}
