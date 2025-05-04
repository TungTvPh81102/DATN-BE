<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Follow;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class RenderRateAndFollowSeeder extends Seeder
{
    public function run(): void
    {
        $courseApproval = Course::query()
            ->where('status', 'approved')
            ->get();
        $memberIds = CourseUser::distinct()->pluck('user_id');
        $members = User::whereIn('id', $memberIds)->role('member')->get();
        $instructorIds = Course::distinct()->pluck('user_id');
        $instructors = User::whereIn('id', $instructorIds)->role('instructor')->get();

        foreach ($courseApproval as $course) {
            $buyers = $members->filter(function ($member) use ($course) {
                return CourseUser::where('user_id', $member->id)
                    ->where('course_id', $course->id)
                    ->exists();
            });

            $buyers->random(min(rand(3, 5), $buyers->count()))->each(function ($member) use ($course) {
                Rating::firstOrCreate([
                    'user_id' => $member->id,
                    'course_id' => $course->id,
                ], [
                    'rate' => rand(3, 5),
                    'content' => 'Đánh giá: ' . \Illuminate\Support\Str::random(30),
                ]);
            });
        }

        foreach ($members as $member) {
            $instructors->random(min(rand(2, 5), $instructors->count()))->each(function ($instructor) use ($member) {
                Follow::firstOrCreate([
                    'follower_id' => $member->id,
                    'instructor_id' => $instructor->id,
                ]);
            });
        }
    }
}
