<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Follow;
use App\Models\Invoice;
use App\Models\Lesson;
use App\Models\Rating;
use App\Models\User;
use App\Models\Video;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            Log::info('Start seeding demo data...');

            DB::beginTransaction();
            $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng'];
            $tenDem = ['Văn', 'Thị', 'Hữu', 'Ngọc', 'Thanh', 'Minh', 'Thùy', 'Tấn'];
            $ten = ['An', 'Bình', 'Chi', 'Dương', 'Giang', 'Hà', 'Khánh', 'Linh', 'My', 'Nam', 'Oanh', 'Phúc', 'Quang', 'Sơn', 'Trang', 'Uyên'];

            for ($i = 1; $i <= 200; $i++) {
                $firstName = $ho[array_rand($ho)] . ' ' . $tenDem[array_rand($tenDem)] . ' ' . $ten[array_rand($ten)];
                $randomCourseIds = Course::where([
                    'is_practical_course' => 0,
                    'status' => 'approved',
                    'visibility' => 'public'
                ])->where('price', '!=', 0)->inRandomOrder()->select('id', 'user_id', 'price')->limit(5)->get();

                $uuid = Str::uuid()->toString();
                $shortUuid = substr(str_replace('-', '', $uuid), 0, 10);
                $email = 'hocvien' . $shortUuid . '@gmail.com';

                $user = User::create([
                    'code' => $shortUuid,
                    'name' => $firstName,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Str::ucfirst($email),
                    'avatar' => 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1742240754/users/la8fl8USzX.png',
                    'status' => 'active'
                ]);

                $user->assignRole('member');

                DB::table('profiles')->insert([
                    'user_id' => $user->id,
                    'about_me' => 'Tôi là một người luôn khao khát học hỏi và không ngừng phát triển bản thân mỗi ngày. Với niềm đam mê sâu sắc đối với tri thức và sự tiến bộ, tôi luôn tìm kiếm cơ hội để trau dồi kỹ năng, mở rộng hiểu biết và thử thách chính mình trong những lĩnh vực mới. Tôi tin rằng sự kiên trì, tinh thần cầu tiến và thái độ tích cực chính là chìa khóa để đạt được thành công bền vững.',
                    'phone' => '09' . rand(10000000, 99999999),
                    'address' => 'Số ' . rand(1, 200) . ' Đường ' . $ten[array_rand($ten)] . ', Quận ' . rand(1, 12) . ', TP. Hà Nội',
                ]);

                if ($i >= 10 && $i <= 300) {
                    $userId = $user->id;

                    foreach ($randomCourseIds as $course) {
                        $instructor = User::query()->where('id', $course['user_id'])->with('instructorCommissions')->first();
                        $rateInstructor = !empty($instructor?->instructorCommissions->rate) ? $instructor->instructorCommissions->rate : 0.6;

                        $year = fake()->randomElement([
                            2025,
                            2025,
                            2025
                        ]);

                        if ($year == 2025) {
                            $randomDate = fake()->dateTimeBetween("2025-01-01", now());
                        } else {
                            $randomDate = fake()->dateTimeBetween("{$year}-01-01", "{$year}-12-31");
                        }

                        $rate = fake()->randomElement([1, 2, 3, 4, 5]);

                        if ($instructor->email == "ducmely@gmail.com") {
                            $rate = 5;
                        }

                        Rating::create([
                            'user_id' => $userId,
                            'course_id' => $course['id'],
                            'content' => $rate == 1 ? 'Quá tệ không có gì hay' : ($rate == 2 ? 'Bài học dở' : ($rate == 3 ? 'Tạm được' : ($rate == 4 ? 'Hay nha, dễ hiểu' : 'Tuyệt vời, bài học dễ hiệu không có gì để chê'))),
                            'rate' => $rate
                        ]);

                        Follow::createOrFirst([
                            'follower_id' => $userId,
                            'instructor_id' => $instructor->id,
                        ]);

                        $chapterIds = Chapter::where('course_id', $course['id'])->pluck('id');

                        $lessonIds = Lesson::whereIn('chapter_id', $chapterIds)->pluck('id');

                        if ($lessonIds->isEmpty()) {
                            continue;
                        }

                        $completedLessons = $lessonIds->random(rand(1, $lessonIds->count()));

                        foreach ($lessonIds as $lessonId) {
                            $lesson = DB::table('lessons')->where('id', $lessonId)->first();

                            $videoDuration = 0;

                            if ($lesson && $lesson->lessonable_type == Video::class) {
                                $videoDuration = DB::table('videos')
                                    ->where('id', $lesson->lessonable_id)
                                    ->value('duration') ?? 0;
                            }

                            DB::table('lesson_progress')->insert([
                                'user_id' => $userId,
                                'lesson_id' => $lessonId,
                                'is_completed' => 1,
                                'last_time_video' => $videoDuration,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        $progress = 100;

                        DB::table('course_users')->insert([
                            'user_id' => $userId,
                            'course_id' => $course['id'],
                            'progress_percent' => $progress,
                            'enrolled_at' => now()->subDays(rand(1, 30)),
                            'completed_at' => $progress === 100 ? now() : null,
                            'source' => 'purchase',
                            'access_status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::table('courses')
                            ->where('id', $course['id'])
                            ->increment('total_student', 1);

                        $viewsToAdd = rand(5, 20);
                        DB::table('courses')
                            ->where('id', $course['id'])
                            ->increment('views', $viewsToAdd);

                        $amount = $course['price'] ?? 100000;
                        $finalAmount = $amount;

                        $invoiceId = DB::table('invoices')->insertGetId([
                            'code' => 'INV' . strtoupper(Str::random(10)),
                            'user_id' => $userId,
                            'course_id' => $course['id'],
                            'amount' => $amount,
                            'final_amount' => $finalAmount,
                            'status' => 'Đã thanh toán',
                            'invoice_type' => 'course',
                            'payment_method' => $i % 2 == 0 ? 'vnpay' : 'momo',
                            'instructor_commissions' => $rateInstructor,
                            'created_at' => $randomDate,
                            'updated_at' => $randomDate,
                        ]);

                        $transactionId = DB::table('transactions')->insertGetId([
                            'transaction_code' => 'TXN' . strtoupper(Str::random(10)),
                            'user_id' => $userId,
                            'amount' => $finalAmount,
                            'type' => 'invoice',
                            'status' => 'Giao dịch thành công',
                            'transactionable_type' => Invoice::class,
                            'transactionable_id' => $invoiceId,
                            'created_at' => $randomDate,
                            'updated_at' => $randomDate,
                        ]);

                        $systemBalance = DB::table('system_funds')->first();

                        if (!$systemBalance) {
                            DB::table('system_funds')->insert([
                                'balance' => $finalAmount * (1 - $rateInstructor),
                                'pending_balance' => $finalAmount * $rateInstructor,
                                'created_at' => $randomDate,
                                'updated_at' => $randomDate,
                            ]);
                        } else {
                            DB::table('system_funds')->update([
                                'balance' => $systemBalance->balance + $finalAmount * (1 - $rateInstructor),
                                'pending_balance' => $systemBalance->pending_balance + $finalAmount * $rateInstructor,
                                'updated_at' => $randomDate,
                            ]);
                        }

                        DB::table('system_fund_transactions')->insert([
                            'transaction_id' => $transactionId,
                            'user_id' => $userId,
                            'total_amount' => $finalAmount,
                            'retained_amount' => $finalAmount * (1 - $rateInstructor),
                            'type' => 'commission_received',
                            'description' => "Nhận tiền hoa hồng từ việc bán khóa học",
                            'created_at' => $randomDate,
                            'updated_at' => $randomDate,
                        ]);

                        $wallet = Wallet::firstOrCreate(
                            ['user_id' => $course['user_id']],
                            ['balance' => 0]
                        );

                        $wallet->increment('balance', $finalAmount * $rateInstructor);

                        $conversation = Conversation::query()->where([
                            'conversationable_id' => $course['id'],
                            'conversationable_type' => Course::class
                        ])->first();

                        if ($conversation) {
                            $conversation->users()->syncWithoutDetaching([$userId]);
                        } else {
                            $conversation = Conversation::create([
                                'conversationable_id' => $course['id'],
                                'conversationable_type' => Course::class,
                                'name' => "Nhóm thảo luận của khóa học {$course['id']}"
                            ]);

                            $conversation->users()->attach([$userId, $course['user_id']]);
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage());
        }
    }
}
