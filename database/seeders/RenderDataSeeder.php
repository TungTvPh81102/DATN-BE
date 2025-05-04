<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Video;
use App\Models\Wallet;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenderDataSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::beginTransaction();
            $faker = Factory::create('vi_VN');

            $categories = Category::all();
            $instructors = User::query()
                ->whereHas('roles', fn($q) => $q->where('name', 'instructor'))
                ->get();
            $students = User::query()->inRandomOrder()->whereHas('roles', fn($q) => $q->where('name', 'member'))
                ->limit(30)->pluck('id')->toArray();

            if ($categories->isEmpty() || $instructors->isEmpty()) {
                return;
            }

            foreach ($instructors as $instructor) {
                $courseCount = rand(5, 10);

                for ($i = 0; $i < $courseCount; $i++) {
                    $category = $categories->random();

                    $courseName = $this->generateCourseTitle($category->name, $faker);
                    $courseCode = Str::uuid();
                    $startDate = Carbon::create(2024, 1, 1)->addDays(rand(0, now()->diffInDays('2024-01-01')));
                    $isFree = rand(0, 1) === 1;
                    if ($isFree) {
                        $price = 0;
                        $priceSale = 0;
                    } else {
                        $price = rand(60, 200) * 5000;

                        $priceSale = rand(0, 1) ? rand(50, intval($price / 5000) - 2) * 5000 : null;
                    }

                    $course = Course::create([
                        'name' => $courseName,
                        'code' => $courseCode,
                        'slug' => Str::slug($courseName) . '-' . $courseCode,
                        'description' => $faker->paragraph(5),
                        'category_id' => $category->id,
                        'user_id' => $instructor->id,
                        'level' => $faker->randomElement(['beginner', 'advanced']),
                        'is_free' => $isFree,
                        'price' => $price,
                        'price_sale' => $priceSale ?? 0,
                        'created_at' => $startDate,
                        'benefits' => json_encode($this->generateBenefits($faker)),
                        'requirements' => json_encode($this->generateRequirements($faker)),
                        'qa' => json_encode($this->generateQa($faker)),
                    ]);

                    $this->createChaptersWithLessonsAndVideos($course, $faker);

                    $instructor = User::query()->where('id', $course['user_id'])->with('instructorCommissions')->first();
                    $rateInstructor = !empty($instructor?->instructorCommissions->rate) ? $instructor->instructorCommissions->rate : 0.6;
                    $amount = $course['price'] ?? 100000;
                    $finalAmount = $amount;
                    $year = fake()->randomElement([
                        2024,
                        2024,
                        2025,
                        2025,
                        2025
                    ]);

                    if ($year == 2025) {
                        $randomDate = fake()->dateTimeBetween("2025-05-01", now());
                    } else {
                        $randomDate = fake()->dateTimeBetween("{$year}-01-01", "{$year}-12-31");
                    }

                    $userId = $faker->randomElement($students);
                    $progress = random_int(0, 100);

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

                    $invoiceId = DB::table('invoices')->insertGetId([
                        'code' => 'INV' . strtoupper(Str::random(10)),
                        'user_id' => $faker->randomElement($students),
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
                        'user_id' => $faker->randomElement($students),
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
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage());
        }
    }

    private function createChaptersWithLessonsAndVideos($course, $faker)
    {
        $chapterCount = rand(4, 8);

        for ($i = 1; $i <= $chapterCount; $i++) {
            $chapterTitle = $this->generateChapterTitle($faker);

            $chapter = Chapter::create([
                'course_id' => $course->id,
                'title' => $chapterTitle,
                'order' => $i,
            ]);

            $lessonCount = rand(4, 7);

            for ($j = 1; $j <= $lessonCount; $j++) {
                $lessonTitle = $this->generateLessonTitle($faker);

                $lesson = Lesson::create([
                    'chapter_id' => $chapter->id,
                    'title' => $lessonTitle,
                    'slug' => Str::slug($lessonTitle),
                    'content' => $faker->paragraph(4),
                    'is_free_preview' => $j == 1,
                    'order' => $j,
                    'type' => 'video',
                    'lessonable_type' => Video::class,
                ]);

                $video = Video::create([
                    'title' => $lessonTitle,
                    'type' => 'upload',
                    'url' => 'https://res.cloudinary.com/dvrexlsgx/video/upload/v1741057384/videos/lessons/iS0tbh035U.mp4',
                    'asset_id' => '
02xvTqNE02XRop6ijrWi8ZQPcpbPu69901AYMPcq1BFQLY',
                    'mux_playback_id' => 'nkNIkX149RhUNR2sR7tZp9yfDQ83mmBzyRMTmgWAgeg',
                    'duration' => rand(300, 1200),
                ]);

                $lesson->update([
                    'lessonable_id' => $video->id,
                ]);
            }
        }
    }

    private function generateCourseTitle($categoryName, $faker)
    {
        $keywords = [
            'Cơ bản',
            'Chuyên sâu',
            'Nâng cao',
            'Thực chiến',
            'Từ A đến Z',
            'Hiện đại',
            'Ứng dụng',
            'Kỹ thuật số',
            'Quản lý',
            'Thành công'
        ];

        $prefix = $faker->randomElement(['Khoá học', 'Hướng dẫn', 'Chương trình đào tạo']);
        $keyword = $faker->randomElement($keywords);

        return "{$prefix} {$categoryName} {$keyword}";
    }

    private function generateChapterTitle($faker)
    {
        $topics = [
            'Giới thiệu',
            'Công cụ cần thiết',
            'Kiến thức nền tảng',
            'Thực hành cơ bản',
            'Phân tích nâng cao',
            'Ứng dụng thực tế',
            'Chiến lược tối ưu',
            'Bí quyết thành công',
            'Quản lý dự án',
            'Kỹ năng mềm hỗ trợ'
        ];

        return $faker->randomElement($topics);
    }

    private function generateLessonTitle($faker)
    {
        $actions = ['Tìm hiểu', 'Phân tích', 'Áp dụng', 'Xây dựng', 'Thực hành', 'Đánh giá', 'Triển khai', 'Tối ưu hóa'];
        $topics = ['cơ bản', 'kỹ thuật', 'quy trình', 'chiến lược', 'công cụ', 'bài tập thực hành', 'dự án mẫu', 'bài học thực tế'];

        return $faker->randomElement($actions) . ' ' . $faker->randomElement($topics);
    }

    private function generateBenefits($faker)
    {
        $benefits = [
            'Nắm vững kiến thức nền tảng',
            'Phát triển kỹ năng thực hành',
            'Ứng dụng lý thuyết vào thực tế',
            'Mở rộng cơ hội nghề nghiệp',
            'Tiết kiệm thời gian học tập',
            'Cập nhật công nghệ mới nhất',
            'Nâng cao kỹ năng quản lý dự án',
            'Phát triển tư duy phản biện',
            'Xây dựng nền tảng vững chắc'
        ];

        return $faker->randomElements($benefits, 4);
    }

    private function generateRequirements($faker)
    {
        $requirements = [
            'Máy tính có kết nối Internet ổn định',
            'Kiến thức cơ bản về Tin học',
            'Tinh thần ham học hỏi và kỷ luật',
            'Khả năng tự học và tự nghiên cứu',
            'Tư duy logic và phân tích vấn đề',
            'Đã cài đặt phần mềm cần thiết',
            'Kỹ năng đọc hiểu tài liệu tiếng Anh cơ bản',
            'Đã từng học qua môn liên quan là lợi thế',
            'Sẵn sàng thực hành thường xuyên'
        ];

        return $faker->randomElements($requirements, 4);
    }

    private function generateQa($faker)
    {
        $qaList = [];
        $questions = [
            'Tôi cần kiến thức nền tảng gì trước khi tham gia?',
            'Khóa học kéo dài bao lâu?',
            'Có bài tập thực hành không?',
            'Khóa học có cấp chứng chỉ không?',
            'Tôi có thể học theo tốc độ riêng không?',
            'Giảng viên hỗ trợ như thế nào?',
            'Có yêu cầu phần mềm/hệ thống nào không?'
        ];

        $answers = [
            'Bạn cần có kiến thức cơ bản.',
            'Khoảng 3 tháng, tùy theo tiến độ.',
            'Có các bài tập thực hành sau mỗi chương.',
            'Hoàn thành khóa học sẽ nhận được chứng chỉ.',
            'Bạn có thể học linh hoạt theo tốc độ của mình.',
            'Giảng viên hỗ trợ qua email và diễn đàn thảo luận.',
            'Cần máy tính cài đặt phần mềm hỗ trợ học tập.'
        ];

        $qaCount = rand(3, 5);

        for ($i = 0; $i < $qaCount; $i++) {
            $qaList[] = [
                'question' => $faker->randomElement($questions),
                'answer' => $faker->optional()->randomElement($answers)
            ];
        }

        return $qaList;
    }
}