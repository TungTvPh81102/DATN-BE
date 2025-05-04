<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Video;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RenderDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('vi_VN');

        $categories = Category::all();

        $instructors = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'instructor');
        })->get();

        $courseTemplates = [
            "Công nghệ thông tin & Truyền thông" => [
                "Lập trình Web chuyên nghiệp",
                "Phát triển ứng dụng di động",
                "Quản trị hệ thống mạng",
                "An ninh mạng và bảo mật thông tin",
                "Trí tuệ nhân tạo và ứng dụng"
            ],
            "Kinh doanh" => [
                "Quản trị kinh doanh hiện đại",
                "Khởi nghiệp và phát triển doanh nghiệp",
                "Chiến lược marketing số",
                "Quản lý tài chính doanh nghiệp",
                "Kế toán và kiểm toán"
            ],
            "Marketing" => [
                "Digital Marketing chuyên sâu",
                "Chiến lược quảng cáo online",
                "Phân tích dữ liệu marketing",
                "Xây dựng thương hiệu cá nhân",
                "Social Media Marketing"
            ],
            "Nghệ thuật & Thiết kế" => [
                "Thiết kế đồ hoạ chuyên nghiệp",
                "Nhiếp ảnh nghệ thuật",
                "Animation và Motion Graphics",
                "Thiết kế UI/UX",
                "Đồ hoạ 3D"
            ],
            "Kỹ năng mềm" => [
                "Kỹ năng giao tiếp hiệu quả",
                "Quản lý thời gian và năng suất",
                "Nghệ thuật thuyết trình",
                "Kỹ năng lãnh đạo",
                "Quản lý công việc và dự án"
            ],
            "Sức khoẻ & Làm đẹp" => [
                "Chăm sóc sức khoẻ toàn diện",
                "Yoga và thiền",
                "Dinh dưỡng và chế độ ăn",
                "Chăm sóc da chuyên nghiệp",
                "Massage và phục hồi"
            ],
            "Nấu ăn & Ẩm thực" => [
                "Ẩm thực chuyên nghiệp",
                "Đầu bếp tại gia",
                "Bánh và Dessert",
                "Ẩm thực quốc tế",
                "Chế biến đồ ăn vặt"
            ],
            "Thể thao & Fitness" => [
                "Huấn luyện thể hình",
                "Yoga và Pilates",
                "Dinh dưỡng thể thao",
                "Võ thuật",
                "Chạy bộ và marathon"
            ],
            "Giáo dục & Phát triển bản thân" => [
                "Kỹ năng học tập hiệu quả",
                "Phát triển bản thân toàn diện",
                "Quản lý tài chính cá nhân",
                "Tư duy phản biện",
                "Nghệ thuật sống"
            ],
            "AI & Machine Learning" => [
                "Nhập môn Trí tuệ nhân tạo",
                "Machine Learning chuyên sâu",
                "Xử lý ngôn ngữ tự nhiên",
                "Học sâu và Neural Network",
                "Ứng dụng AI trong kinh doanh"
            ]
        ];

        foreach ($categories as $category) {
            $courseNames = $courseTemplates[$category->name] ?? [];

            foreach ($courseNames as $courseName) {
                if ($instructors->isEmpty()) {
                    break;
                }

                $courseCode = Str::uuid();

                $course = Course::query()->create([
                    'name' => $courseName,
                    'code' => $courseCode,
                    'slug' => Str::slug($courseName) . '-' . $courseCode,
                    'description' => $faker->paragraph(),
                    'category_id' => $category->id,
                    'user_id' => $instructors->random()->id,
                    'level' => $faker->randomElement([
                        'beginner', 'intermediate', 'advanced'
                    ]),
                ]);

                $this->createChaptersWithLessonsAndVideos($course, $faker);
            }
        }
    }

    private function createChaptersWithLessonsAndVideos($course, $faker)
    {
        $chapterCount = $faker->numberBetween(4, 10);

        for ($i = 0; $i < $chapterCount; $i++) {
            $chapter = Chapter::create([
                'course_id' => $course->id,
                'title' => $this->generateChapterTitle($course->name, $i + 1),
                'order' => $i + 1,
            ]);

            $lessonCount = $faker->numberBetween(3, 7);

            for ($j = 0; $j < $lessonCount; $j++) {
                $lesson = Lesson::create([
                    'chapter_id' => $chapter->id,
                    'title' => sprintf(
                        "Bài %d: %s trong %s",
                        $j + 1,
                        $this->generateLessonTitle(),
                        $chapter->title
                    ),
                    'slug' => Str::slug(sprintf(
                        "Bai %d %s",
                        $j + 1,
                        $this->generateLessonTitle()
                    )),
                    'content' => $faker->paragraph(),
                    'is_free_preview' => $j === 0,
                    'order' => $j + 1,
                    'type' => 'video',
                    'lessonable_type' => Video::class,
                ]);

                $video = Video::create([
                    'title' => $lesson->title,
                    'type' => 'upload',
                    'url' => 'https://res.cloudinary.com/dvrexlsgx/video/upload/v1741057384/videos/lessons/iS0tbh035U.mp4',
                    'asset_id' => "GSLT64BKatzxbwXE01P5rzqmRxHmKk900v6CtxYFHUBg8",
                    'mux_playback_id' => 'NvLKobHTNEMqhXZktGplZZdiITb02ZmQQpVNYUib8UHU',
                    'duration' => 1163
                ]);

                $lesson->update([
                    'lessonable_id' => $video->id
                ]);
            }
        }
    }

    private function generateChapterTitle($courseName, $chapterNumber)
    {
        $prefixes = [
            'Tổng quan về',
            'Khám phá',
            'Nền tảng',
            'Chuyên sâu',
            'Thực hành'
        ];

        $topics = [
            'kỹ năng',
            'kiến thức',
            'công nghệ',
            'chiến lược',
            'ứng dụng'
        ];

        $prefix = $prefixes[array_rand($prefixes)];
        $topic = $topics[array_rand($topics)];

        return sprintf(
            "Chương %d: %s %s trong %s",
            $chapterNumber,
            $prefix,
            $topic,
            $courseName
        );
    }

    private function generateLessonTitle()
    {
        $topics = [
            'Kiến thức cơ bản',
            'Kỹ năng chuyên sâu',
            'Thực hành hiệu quả',
            'Chiến lược áp dụng',
            'Giải pháp chuyên nghiệp'
        ];

        return $topics[array_rand($topics)];
    }

}
