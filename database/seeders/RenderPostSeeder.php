<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class RenderPostSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id')->toArray();

        $topInstructors = User::whereHas('roles', function ($query) {
            $query->where('name', 'instructor');
        })
            ->withCount('courses')
            ->orderByDesc('courses_count')
            ->take(10)
            ->get();

        foreach ($topInstructors as $instructor) {
            $numPosts = rand(1, 2);

            for ($i = 0; $i < $numPosts; $i++) {
                $title = 'Bài viết từ giảng viên ' . $instructor->name;
                $slug = \Illuminate\Support\Str::slug($title . '-' . \Illuminate\Support\Str::random(6));

                Post::create([
                    'user_id' => $instructor->id,
                    'category_id' => $categories[array_rand($categories)],
                    'title' => $title,
                    'slug' => $slug,
                    'description' => 'Giảng viên chia sẻ những kiến thức mới, kinh nghiệm dạy học và thông tin khóa học.',
                    'content' => '<p>Xin chào! Tôi là <strong>' . $instructor->name . '</strong>. Trong bài viết này, tôi sẽ chia sẻ một số mẹo học tập hiệu quả và cập nhật nội dung mới nhất của khóa học.</p><p>Hãy theo dõi để không bỏ lỡ thông tin hữu ích nhé!</p>',
                    'thumbnail' => 'https://picsum.photos/600/400?random=' . rand(1, 1000),
                    'status' => Post::STATUS_PENDING,
                    'views' => rand(100, 1000),
                    'is_hot' => rand(0, 1),
                    'published_at' => now()->subDays(rand(0, 7)),
                ]);
            }
        }
    }
}
