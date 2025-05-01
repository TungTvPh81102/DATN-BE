<?php

namespace Database\Seeders;

use App\Models\Approvable;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstructorSeeder extends Seeder
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
            $faker = \Faker\Factory::create('vi_VN');

            $avatarUrl = 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1743316311/users/34wNL3FsA1.png';

            $total = 100;
            $start = \Carbon\Carbon::create(2024, 1, 1);
            $months = \Carbon\Carbon::now()->diffInMonths($start) + 1;
            $usersPerMonth = intdiv($total, $months);
            $remainder = $total % $months;

            $index = 0;

            for ($m = 0; $m < $months; $m++) {
                $month = $start->copy()->addMonths($m);
                $countThisMonth = $usersPerMonth + ($m < $remainder ? 1 : 0); // phân bổ phần dư

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

                    $user->assignRole('instructor');

                    $profile = $user->profile()->create([
                        'phone' => $this->generateVietnamesePhoneNumber($faker),
                        'about_me' => $faker->paragraph(2),
                        'address' => $faker->address(),
                        'experience' => $faker->paragraph(2),
                        'bio' => null,
                        'certificates' => null,
                        'qa_systems' => null,
                        'banking_info' => null,
                        'identity_verification' => $avatarUrl,
                    ]);

                    $careerCount = rand(1, 3);
                    for ($j = 0; $j < $careerCount; $j++) {
                        $profile->careers()->create([
                            'institution_name' => $faker->company(),
                            'degree' => $faker->randomElement(['Cử nhân', 'Kỹ sư', 'Thạc sĩ', 'Tiến sĩ']),
                            'major' => $faker->jobTitle(),
                            'start_date' => now()->subYears(rand(5, 10)),
                            'end_date' => now()->subYears(rand(1, 4)),
                            'description' => Str::limit($faker->paragraph(), 190),
                        ]);
                    }

                    Approvable::create([
                        'approvable_id' => $user->id,
                        'approvable_type' => 'App\Models\User',
                        'approver_id' => null,
                        'status' => 'approved',
                        'request_date' => $createdAt,
                        'approved_at' => $createdAt,
                        'note' => 'Giảng viên đã được phê duyệt.',
                        'approval_logs' => json_encode([[
                            'name' => 'Hệ thống',
                            'status' => 'approved',
                            'note' => 'Giảng viên tự động được phê duyệt.',
                            'reason' => null,
                            'action_at' => $createdAt,
                        ]]),
                    ]);

                    DB::table('instructor_commissions')->insert([
                        'instructor_id' => $user->id,
                        'rate' => 0.6,
                        'rate_logs' => json_encode([[
                            'old_rate' => null,
                            'new_rate' => 0.6,
                            'changed_at' => $createdAt,
                            'user_name' => 'Hệ thống tự động đánh giá',
                            'note' => 'Tỷ lệ mặc định khi giảng viên bắt đầu tham gia'
                        ]]),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

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

    private function generateVietnamesePhoneNumber($faker)
    {
        $prefix = $faker->randomElement(['090', '091', '092', '093', '094']);
        $number = $faker->numerify('#######');
        return $prefix . $number;
    }

    private function randomDate($startDate, $endDate)
    {
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);

        return date('Y-m-d H:i:s', $randomTimestamp);
    }
}
