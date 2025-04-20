<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ChangePasswordRequest;
use App\Http\Requests\API\User\StoreBankingInfoRequest;
use App\Http\Requests\API\User\StoreCareerRequest;
use App\Http\Requests\API\User\UpdateBankingInfoRequest;
use App\Http\Requests\API\User\UpdateCareerRequest;
use App\Http\Requests\API\User\UpdateUserProfileRequest;
use App\Models\Career;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\CouponUse;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Invoice;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\MembershipSubscription;
use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToLocalTrait;
use FontLib\Table\Type\post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToCloudinaryTrait, UploadToLocalTrait;

    const FOLDER_USER = 'users';
    const FOLDER_CERTIFICATE = 'certificates';

    public function showProfile()
    {
        try {
            $user = Auth::user();

            return $this->respondOk('Thông tin người dùng ' . $user->name, [
                'user' => $user->load('profile.careers'),
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    public function updateProfile(UpdateUserProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    $this->deleteImage($user->avatar, self::FOLDER_USER);
                }

                $avatarUrl = $this->uploadImage($request->file('avatar'), self::FOLDER_USER);
                $user->avatar = $avatarUrl;
            }

            $user->name = $request->name ?? $user->name;
            $user->save();

            $profile = Profile::query()->firstOrCreate(['user_id' => $user->id]);

            if ($profile) {
                if ($request->hasFile('certificates')) {
                    $certificates = json_decode($profile->certificates, true);

                    $uploadedCertificates = $this->uploadCertificates($request->file('certificates'));

                    $uploadedCertificates = array_merge($certificates, $uploadedCertificates);
                }

                $data['about_me'] = $request->about_me;
                if ($data['about_me'] === null) {
                    $data['about_me'] = '';
                } else {
                    $data['about_me'] = $request->about_me ?? $profile->about_me;
                }

                $profile->update([
                    'about_me' => $data['about_me'],
                    'phone' => $request->phone ?? $profile->phone,
                    'address' => $request->address ?? $profile->address,
                    'experience' => $request->experience ?? $profile->experience,
                    'certificates' => !empty($uploadedCertificates)
                        ? json_encode($uploadedCertificates)
                        : $profile->certificates,
                    'bio' => $request->bio ? $this->prepareBioData($request->bio, $profile) : $profile->bio,
                ]);
            }

            DB::commit();

            return $this->respondOk('Cập nhật thông tin thành công', [
                'user' => $user->load('profile.careers'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    private function uploadCertificates($certificates)
    {
        if ($certificates) {
            return $this->uploadMultiple($certificates, self::FOLDER_CERTIFICATE);
        }
        return [];
    }

    private function prepareBioData($bioData, $profile)
    {
        if ($bioData) {
            $bio = [];
            $profile = !empty($profile->bio) ? $profile->bio : '';

            if (isset($bioData['facebook'])) {
                $bio['facebook'] = $bioData['facebook'];
            } else {
                if ($profile && !empty($profile['facebook'])) $bio['facebook'] = $profile['facebook'];
            }

            if (isset($bioData['instagram'])) {
                $bio['instagram'] = $bioData['instagram'];
            } else {
                if ($profile && !empty($profile['instagram'])) $bio['instagram'] = $profile['instagram'];
            }

            if (isset($bioData['github'])) {
                $bio['github'] = $bioData['github'];
            } else {
                if ($profile && !empty($profile['github'])) $bio['github'] = $profile['github'];
            }

            if (isset($bioData['linkedin'])) {
                $bio['linkedin'] = $bioData['linkedin'];
            } else {
                if ($profile && !empty($profile['linkedin'])) $bio['linkedin'] = $profile['linkedin'];
            }

            if (isset($bioData['twitter'])) {
                $bio['twitter'] = $bioData['twitter'];
            } else {
                if ($profile && !empty($profile['twitter'])) $bio['twitter'] = $profile['twitter'];
            }

            if (isset($bioData['youtube'])) {
                $bio['youtube'] = $bioData['youtube'];
            } else {
                if ($profile && !empty($profile['youtube'])) $bio['youtube'] = $profile['youtube'];
            }

            if (isset($bioData['website'])) {
                $bio['website'] = $bioData['website'];
            } else {
                if ($profile && !empty($profile['website'])) $bio['website'] = $profile['website'];
            }

            return json_encode($bio);
        }

        return null;
    }


    public function changePassword(ChangePasswordRequest $request)
    {
        try {

            $user = Auth::user();

            $user->password = Hash::make($request->new_password);
            $user->save();

            $user->tokens->each(function ($token) {
                $token->delete();
            });

            return $this->respondOk('Mật khẩu của ' . $user->name . ' đã được thay đổi thành công. Vui lòng đăng nhập lại!');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    public function getUserCourses(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn chưa đăng nhập');
            }

            $courses = Course::query()
                ->select([
                    'id',
                    'user_id',
                    'name',
                    'slug',
                    'category_id',
                    'status',
                    'thumbnail',
                    'level',
                    'is_practical_course',
                ])
                ->whereHas('courseUsers', function ($query) use ($user) {
                    $query->where('user_id', $user->id)->where('access_status', 'active');
                })
                ->with([
                    'courseUsers:id,user_id,course_id,progress_percent,source',
                    'category:id,name,slug',
                    'user:id,name,avatar',
                ])
                ->withCount([
                    'chapters',
                    'lessons'
                ])->get();

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            $courseRatings = Rating::whereIn('course_id', $courses->pluck('id'))
                ->groupBy('course_id')
                ->select(
                    'course_id',
                    DB::raw('COUNT(*) as ratings_count'),
                    DB::raw('ROUND(AVG(rate), 1) as average_rating')
                )
                ->get()
                ->keyBy('course_id');

            $totalCompletedLessons = 0;
            $totalLessons = $courses->sum('lessons_count');

            $completedCoursesCount = 0;

            $totalProgressPercent = 0;

            foreach ($courses as $course) {
                $lessonIds = $course->chapters->flatMap(function ($chapter) {
                    return $chapter->lessons->pluck('id');
                });

                $completedCount = LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('is_completed', 1)
                    ->count();

                $totalCompletedLessons += $completedCount;

                $progress = $course->courseUsers->where('user_id', $user->id)->first();
                if ($progress) {
                    $totalProgressPercent += $progress->progress_percent;

                    if ($progress->progress_percent == 100) {
                        $completedCoursesCount++;
                    }
                }
            }

            $averageProgress = $courses->count() > 0 ? $totalProgressPercent / $courses->count() : 0;

            $result = $courses->map(function ($course) use ($courseRatings, $user) {
                $videoLessons = $course->chapters->flatMap(function ($chapter) {
                    return $chapter->lessons->where('lessonable_type', Video::class);
                });

                $totalVideoDuration = $videoLessons->sum(function ($lesson) {
                    return $lesson->lessonable->duration ?? 0;
                });

                $ratingInfo = $courseRatings->get($course->id);

                $lessonProgress = LessonProgress::query()
                    ->where('user_id', $user->id)
                    ->whereHas('lesson', function ($query) use ($course) {
                        $lessonIds = $course->chapters->flatMap(function ($chapter) {
                            return $chapter->lessons->pluck('id');
                        });

                        $query->whereIn('id', $lessonIds);
                    })
                    ->with('lesson:id,title')
                    ->latest('updated_at')
                    ->first();

                if (!$lessonProgress) {
                    $firstChapter = $course->chapters->first();
                    $firstLesson = $firstChapter ? $firstChapter->lessons->where('is_completed', false)->first() : null;

                    $currentLesson = $firstLesson ? [
                        'id' => $firstLesson->id,
                        'title' => $firstLesson->title
                    ] : null;
                } else {
                    $progress = $course->courseUsers->where('user_id', $user->id)->first();

                    if ($progress && $progress->progress_percent == 100) {
                        $lastChapter = $course->chapters->last();
                        $lastLesson = $lastChapter ? $lastChapter->lessons->last() : null;

                        $currentLesson = $lastLesson ? [
                            'id' => $lastLesson->id,
                            'title' => $lastLesson->title
                        ] : null;
                    } else {
                        $currentLesson = [
                            'id' => $lessonProgress->lesson->id,
                            'title' => $lessonProgress->lesson->title,
                        ];
                    }
                }

                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'slug' => $course->slug,
                    'thumbnail' => $course->thumbnail,
                    'level' => $course->level,
                    'status' => $course->status,
                    'chapters_count' => $course->chapters_count,
                    'lessons_count' => $course->lessons_count,
                    'is_practical_course' => $course->is_practical_course,
                    'ratings' => [
                        'count' => $ratingInfo ? $ratingInfo->ratings_count : 0,
                        'average' => $ratingInfo ? $ratingInfo->average_rating : 0
                    ],
                    'total_video_duration' => $totalVideoDuration,
                    'progress_percent' => $course->courseUsers
                        ->where('user_id', $user->id)->first()
                        ->progress_percent ?? 0,
                    'current_lesson' => $currentLesson,
                    'source' => $course->courseUsers->where('user_id', $user->id)->first()->source ?? null,
                    'category' => [
                        'id' => $course->category->id ?? null,
                        'name' => $course->category->name ?? null,
                        'slug' => $course->category->slug ?? null
                    ],
                    'user' => [
                        'id' => $course->user->id ?? null,
                        'name' => $course->user->name ?? null,
                        'avatar' => $course->user->avatar ?? null,
                        'code' => $course->user->code ?? null
                    ]
                ];
            });

            $summary = [
                'total_courses' => $courses->count(),
                'completed_lessons' => $totalCompletedLessons . '/' . $totalLessons,
                'average_progress' => round($averageProgress, 1),
                'completed_courses' => $completedCoursesCount
            ];

            return $this->respondOk('Danh sách khoá học của người dùng: ' . $user->name, [
                'courses' => $result,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    public function getCourseProgress($slug)
    {
        try {
            $user = Auth::user();

            $course = Course::query()->where('slug', $slug)->first();

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            $courseProgress = CourseUser::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->select('progress_percent')
                ->first();

            if (!$courseProgress) {
                return $this->respondNotFound('Học viên chưa đăng ký khóa học này');
            }

            return $this->respondOk('Tiến độ khóa học ' .
                $course->name . ' của người dùng: ' .
                $user->name, $courseProgress);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }

    public function getOrdersBought()
    {
        try {
            $user = Auth::user();

            $orders = Invoice::where('user_id', $user->id)
                ->with('course:id,name', 'membershipPlan:id,name')
                ->select(
                    'id',
                    'course_id',
                    'membership_plan_id',
                    'created_at',
                    DB::raw('(amount - IFNULL(coupon_discount, 0)) as final_amount'),
                    'status',
                    'invoice_type'
                )
                ->get()
                ->groupBy('invoice_type');

            return $this->respondOk('Danh sách đơn hàng của người dùng: ' . $user->name, $orders);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }


    public function showOrdersBought($id)
    {
        try {
            $user = Auth::user();

            $order = Invoice::where('id', $id)
                ->where('user_id', $user->id)
                ->select(
                    'id',
                    'invoice_type',
                    'course_id',
                    'membership_plan_id',
                    'code',
                    'coupon_code',
                    'coupon_discount',
                    'amount',
                    'created_at',
                    DB::raw('(amount - IFNULL(coupon_discount, 0)) as final_amount'),
                    'status'
                )
                ->first();

            if (!$order) {
                return $this->respondNotFound('Đơn hàng không tồn tại hoặc không thuộc về người dùng.');
            }

            if ($order->invoice_type === 'course') {
                $order->load([
                    'course' => function ($query) {
                        $query->select('id', 'name', 'user_id')
                            ->with('instructor:id,name');
                    }
                ]);
            }

            if ($order->invoice_type === 'membership') {
                $order->load([
                    'membershipPlan' => function ($query) {
                        $query->select('id', 'name', 'instructor_id')
                            ->with('instructor:id,name');
                    }
                ]);
                // dd($order);
            }

            $title = match ($order->invoice_type) {
                'course' => $order->course->name ?? 'Không xác định',
                'membership' => $order->membership->title ?? 'Không xác định',
                default => 'Không xác định'
            };

            return $this->respondOk("Chi tiết đơn hàng {$title} của người dùng: {$user->name}", $order);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }


    public function storeCareers(StoreCareerRequest $request)
    {
        try {
            if ($request->has('careers')) {
                $user = Auth::user();

                if (!$user) return $this->respondForbidden('Vui lòng đăng nhập và thử lại');

                $profile = Profile::query()->firstOrCreate([
                    'user_id' => $user->id
                ]);

                foreach ($request->careers as $careerData) {
                    Career::create(
                        [
                            'profile_id' => $profile->id,
                            'degree' => $careerData['degree'],
                            'major' => $careerData['major'],
                            'start_date' => $careerData['start_date'],
                            'end_date' => $careerData['end_date'],
                            'description' => $careerData['description'] ?? null,
                            'institution_name' => $careerData['institution_name'],
                        ]
                    );
                }
                return $this->respondCreated('Thêm mới sự nghiệp thành công', ['user' => $user->load('profile.careers')]);
            } else {
                return $this->respondError('Không có dữ liệu để thêm mới');
            }
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Chưa thể thêm thông tin');
        }
    }

    public function updateCareers(UpdateCareerRequest $request, string $id)
    {
        try {
            if ($request->has('careers') && !empty($request->careers) && is_array($request->careers)) {

                $user = Auth::user();

                if (!$user) return $this->respondForbidden('Vui lòng đăng nhập và thử lại');

                $profile = Profile::query()->firstOrCreate([
                    'user_id' => $user->id
                ]);

                $careerData = $request->careers;

                $career = Career::query()->where('id', $id)->first();

                if ($career) {
                    $career->update(
                        [
                            'profile_id' => $profile->id,
                            'degree' => $careerData[0]['degree'],
                            'major' => $careerData[0]['major'],
                            'start_date' => $careerData[0]['start_date'],
                            'end_date' => $careerData[0]['end_date'],
                            'description' => $careerData[0]['description'] ?? null,
                            'institution_name' => $careerData[0]['institution_name'],
                        ]
                    );
                } else return $this->respondNotFound('Không tìm thấy thông tin');
            }

            return $this->respondOk('Cập nhật thành công', ['user' => $user->load('profile.careers')]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Chưa thể thêm thông tin');
        }
    }

    public function deleteCareers(string $id)
    {
        try {
            $career = Career::destroy($id);

            if (!$career) {
                return $this->respondNotFound('Không tìm thấy thông tin');
            } else {
                return $this->respondNoContent();
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondError('Chưa thể thêm thông tin');
        }
    }

    public function getCouponUser()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $couponUse = CouponUse::query()
                ->with('coupon', function ($query) {
                    $query->select('id', 'code', 'name', 'discount_value', 'discount_type', 'status', 'specific_course')
                        ->orderBy('discount_value', 'desc')
                        ->with('couponCourses:id,name');
                })
                ->whereHas('coupon', function ($query) {
                    $query->where('status', 1);
                })
                ->where('user_id', $user->id)
                ->where('status', 'unused')
                ->where(function ($query) {
                    $query->whereNull('expired_at')
                        ->orWhere('expired_at', '>', now());
                })
                ->select('id', 'user_id', 'coupon_id', 'expired_at', 'status')
                ->get();

            if (!$couponUse) {
                return $this->respondNotFound('Không tìm thấy mã giảm giá');
            }
            return $this->respondOk('Danh sách mã giảm giá của người dùng' . $user->name, $couponUse);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function downloadCertificate(Request $request, string $slug)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $course = Course::query()->where('slug', $slug)->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $courseUser = CourseUser::query()->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if (!$courseUser) {
                return $this->respondNotFound('Bạn chưa tham gia khoá học');
            }

            $certificate = Certificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if (!$certificate) {
                return $this->respondNotFound('Không tìm thấy chứng chỉ');
            }

            return $this->respondOk('Thao tác thành công', $certificate->file_path);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function getCertificate()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $certificate = Certificate::query()
                ->where('user_id', $user->id)
                ->get();

            if (!$certificate) {
                return $this->respondNotFound('Không tìm thấy chứng chỉ');
            }

            return $this->respondOk('Danh sách chứng chỉ', $certificate);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getBankingInfos()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $profile = Profile::query()->where('user_id', $user->id)->first();

            if (!$profile) {
                return $this->respondNotFound('Không tìm thấy thống tin người dùng');
            }

            $bankingInfos = $profile->banking_info ?? [];

            return $this->respondOk('Lấy danh sách thông tin ngân hàng thành công', $bankingInfos);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function addBankingInfo(StoreBankingInfoRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $profile = Profile::query()->where('user_id', $user->id)->first();

            if (!$profile) {
                return $this->respondNotFound('Không tìm thấy thống tin người dùng');
            }

            $bankingInfos = $profile->banking_info ?? [];

            if (count($bankingInfos) >= 3) {
                return $this->respondError('Chỉ được phép thêm tối đa 3 tài khoản');
            }

            $data = $request->validated();

            $isDuplicate = collect($bankingInfos)->contains(function ($item) use ($data) {
                return $item['account_no'] === $data['account_no'];
            });

            if ($isDuplicate) {
                return $this->respondError('Số tài khoản đã tồn tại');
            }

            $isFirstRecord = empty($bankingInfos);

            $newBankingInfo = [
                'id' => uniqid(),
                'acq_id' => $data['bin'],
                'name' => $data['name'],
                'logo' => $data['logo'] ?? '',
                'logo_rounded' => $data['logo_rounded'] ?? '',
                'short_name' => $data['short_name'] ?? '',
                'account_no' => $data['account_no'],
                'account_name' => $data['account_name'],
                'is_default' => $isFirstRecord ? true : ($data['is_default'] ?? false),
            ];

            if ($newBankingInfo['is_default']) {
                $bankingInfos = collect($bankingInfos)->map(function ($item) {
                    $item['is_default'] = false;
                    return $item;
                })->toArray();
            }

            $bankingInfos[] = $newBankingInfo;

            $profile->update([
                'banking_info' => $bankingInfos
            ]);

            return $this->respondCreated(
                'Thêm thông tin ngân hàng thành công',
                $bankingInfos
            );
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function updateBankingInfo(UpdateBankingInfoRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $profile = Profile::query()->where('user_id', $user->id)->first();

            if (!$profile) {
                return $this->respondNotFound('Không tìm thấy hồ sơ');
            }

            $bankingInfos = $profile->banking_info ?? [];
            $data = $request->validated();
            $bankingInfoId = $data['id'];

            $index = null;
            foreach ($bankingInfos as $key => $info) {
                if ($info['id'] === $bankingInfoId) {
                    $index = $key;
                    break;
                }
            }

            if ($index === null) {
                return $this->respondNotFound('Không tìm thấy thông tin ngân hàng cần cập nhật');
            }

            $duplicateExists = collect($bankingInfos)
                ->filter(function ($item) use ($data, $bankingInfoId) {
                    return $item['account_no'] === $data['account_no'] &&
                        $item['id'] !== $bankingInfoId;
                })->isNotEmpty();

            if ($duplicateExists) {
                return $this->respondError('Số tài khoản đã tồn tại');
            }

            $updatedBankingInfos = collect($bankingInfos)->map(function ($item) use ($data) {
                if ($item['id'] === $data['id']) {
                    return [
                        'id' => $data['id'],
                        'acq_id' => $data['bin'],
                        'name' => $data['name'],
                        'logo' => $data['logo'] ?? $item['logo'] ?? '',
                        'logo_rounded' => $data['logo_rounded'] ?? $item['logo_rounded'] ?? '',
                        'short_name' => $data['short_name'] ?? $item['short_name'] ?? '',
                        'account_no' => $data['account_no'],
                        'account_name' => $data['account_name'],
                        'is_default' => isset($data['is_default']) ? (bool)$data['is_default'] : ($item['is_default'] ?? false),
                    ];
                }

                if (isset($data['is_default']) && $data['is_default'] && $item['id'] !== $data['id']) {
                    $item['is_default'] = false;
                }

                return $item;
            })->all();

            $profile->update([
                'banking_info' => $updatedBankingInfos
            ]);

            return $this->respondOk('Cập nhật thông tin ngân hàng thành công', $updatedBankingInfos);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function setDefaultBankingInfo(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $profile = Profile::query()->where('user_id', $user->id)->first();

            if (!$profile) {
                return $this->respondNotFound('Không tìm thấy hồ sơ');
            }

            $bankingInfos = $profile->banking_info ?? [];

            if (empty($bankingInfos)) {
                return $this->respondNotFound('Không có thông tin ngân hàng nào');
            }

            $bankingInfoId = $request->input('id');

            $exists = collect($bankingInfos)->contains(function ($item) use ($bankingInfoId) {
                return $item['id'] === $bankingInfoId;
            });

            if (!$exists) {
                return $this->respondNotFound('Không tìm thấy thông tin ngân hàng');
            }

            $updatedBankingInfos = collect($bankingInfos)
                ->map(function ($item) use ($bankingInfoId) {
                    // Đặt tài khoản được chọn thành mặc định, các tài khoản khác thành không mặc định
                    $item['is_default'] = ($item['id'] === $bankingInfoId);
                    return $item;
                })
                ->all();

            $profile->update([
                'banking_info' => $updatedBankingInfos
            ]);

            return $this->respondOk('Cập nhật tài khoản mặc định thành công', $updatedBankingInfos);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function removeBankingInfo(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $profile = Profile::query()->where('user_id', $user->id)->first();

            if (!$profile) {
                return $this->respondNotFound('Không tìm thấy hồ sơ');
            }

            $bankingInfos = $profile->banking_info ?? [];
            $bankingInfoId = $request->input('id');

            $bankingInfo = collect($bankingInfos)->firstWhere('id', $bankingInfoId);

            if (!$bankingInfo) {
                return $this->respondNotFound('Không tìm thấy thông tin ngân hàng cần xóa');
            }

            $isDefault = $bankingInfo['is_default'] ?? false;

            // Lọc ra các tài khoản không bị xóa
            $updatedBankingInfos = collect($bankingInfos)
                ->reject(function ($item) use ($bankingInfoId) {
                    return $item['id'] === $bankingInfoId;
                })
                ->values()
                ->all();

            if ($isDefault && !empty($updatedBankingInfos)) {
                $updatedBankingInfos[0]['is_default'] = true;
            }

            $profile->update([
                'banking_info' => $updatedBankingInfos
            ]);

            return $this->respondOk('Xóa thông tin ngân hàng thành công', $updatedBankingInfos);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function getMembershipPlanList(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Vui lý đăng nhập');
            }

            $membershipSubscriptions = MembershipSubscription::query()
                ->with([
                    'membershipPlan' => function ($query) {
                        $query->with([
                            'membershipCourseAccess' => function ($courseQuery) {
                                $courseQuery->select(['courses.id', 'name', 'slug', 'thumbnail'])
                                    ->withCount('lessons');
                            }
                        ]);
                    }
                ])
                ->where('user_id', $user->id)
                ->get();

            if ($membershipSubscriptions->isEmpty()) {
                return $this->respondNotFound('Bạn chưa mua gói thành viên nào');
            }

            $allCourseIds = $membershipSubscriptions->flatMap(function ($subscription) {
                if ($subscription->membershipPlan && $subscription->membershipPlan->membershipCourseAccess) {
                    return $subscription->membershipPlan->membershipCourseAccess->pluck('id');
                }
                return [];
            })->unique();

            $courseDurations = [];
            if ($allCourseIds->isNotEmpty()) {
                $courseVideos = DB::table('chapters')
                    ->join('lessons', 'chapters.id', '=', 'lessons.chapter_id')
                    ->join('videos', 'lessons.lessonable_id', '=', 'videos.id')
                    ->whereIn('chapters.course_id', $allCourseIds)
                    ->where('lessons.lessonable_type', 'App\\Models\\Video')
                    ->select('chapters.course_id', DB::raw('SUM(videos.duration) as total_duration'))
                    ->groupBy('chapters.course_id')
                    ->get();

                foreach ($courseVideos as $course) {
                    $courseDurations[$course->course_id] = $course->total_duration;
                }
            }

            $currentLessons = [];
            if ($allCourseIds->isNotEmpty()) {
                $courseLessons = DB::table('chapters')
                    ->join('lessons', 'chapters.id', '=', 'lessons.chapter_id')
                    ->whereIn('chapters.course_id', $allCourseIds)
                    ->select('chapters.course_id', 'lessons.id as lesson_id', 'lessons.title')
                    ->get()
                    ->groupBy('course_id');

                $lessonProgresses = DB::table('lesson_progress')
                    ->where('user_id', $user->id)
                    ->select('lesson_id', 'updated_at')
                    ->orderBy('updated_at', 'desc')
                    ->get()
                    ->keyBy('lesson_id');

                foreach ($allCourseIds as $courseId) {
                    $courseLessonIds = $courseLessons->get($courseId, collect())->pluck('lesson_id')->toArray();

                    if (!empty($courseLessonIds)) {
                        $latestLessonProgress = null;
                        $latestLesson = null;

                        foreach ($courseLessonIds as $lessonId) {
                            if (isset($lessonProgresses[$lessonId])) {
                                if (
                                    $latestLessonProgress === null ||
                                    $lessonProgresses[$lessonId]->updated_at > $latestLessonProgress->updated_at
                                ) {
                                    $latestLessonProgress = $lessonProgresses[$lessonId];
                                    $latestLesson = $courseLessons->get($courseId)
                                        ->firstWhere('lesson_id', $lessonId);
                                }
                            }
                        }

                        if ($latestLesson === null) {
                            $firstLesson = $courseLessons->get($courseId)->first();
                            if ($firstLesson) {
                                $currentLessons[$courseId] = [
                                    'id' => $firstLesson->lesson_id,
                                    'title' => $firstLesson->title
                                ];
                            }
                        } else {
                            $currentLessons[$courseId] = [
                                'id' => $latestLesson->lesson_id,
                                'title' => $latestLesson->title
                            ];
                        }
                    }
                }
            }

            $result = $membershipSubscriptions->map(function ($subscription) use ($courseDurations, $currentLessons) {
                $courses = collect();

                if ($subscription->membershipPlan && $subscription->membershipPlan->membershipCourseAccess) {
                    $courses = $subscription->membershipPlan->membershipCourseAccess->map(function ($course) use ($courseDurations, $currentLessons) {
                        return [
                            'id' => $course->id,
                            'name' => $course->name,
                            'slug' => $course->slug,
                            'thumbnail' => $course->thumbnail,
                            'lessons_count' => $course->lessons_count,
                            'total_duration' => $courseDurations[$course->id] ?? 0,
                            'current_lesson' => $currentLessons[$course->id] ?? null
                        ];
                    });
                }

                return [
                    'id' => $subscription->id,
                    'membership_plan_id' => $subscription->membership_plan_id,
                    'user_id' => $subscription->user_id,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'status' => $subscription->status,
                    'created_at' => $subscription->created_at,
                    'updated_at' => $subscription->updated_at,
                    'membership_plan' => [
                        'id' => $subscription->membershipPlan->id,
                        'instructor_id' => $subscription->membershipPlan->instructor_id,
                        'code' => $subscription->membershipPlan->code,
                        'name' => $subscription->membershipPlan->name,
                        'description' => $subscription->membershipPlan->description,
                        'price' => $subscription->membershipPlan->price,
                        'duration_months' => $subscription->membershipPlan->duration_months,
                        'benefits' => $subscription->membershipPlan->benefits,
                        'status' => $subscription->membershipPlan->status,
                        'created_at' => $subscription->membershipPlan->created_at,
                        'updated_at' => $subscription->membershipPlan->updated_at,
                        'courses' => $courses
                    ]
                ];
            });

            return $this->respondOk('Danh sách gói thành viên đã mua của: ' . $user->name, $result);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function removeCertificate(Request $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $profile = Profile::where('user_id', $user->id)->first();


            if (!$profile) {
                return $this->respondForbidden('Không tìm thấy hồ sơ');
            }

            $certificates = json_decode($profile->certificates, true);
            if (!is_array($certificates)) {
                $certificates = [];
            }

            // dd($certificates);

            $certificatePath = $request->certificate;

            if (!in_array($certificatePath, $certificates)) {
                return $this->respondForbidden('Không tìm thấy chứng chỉ trong danh sách');
            }

            $certificates = array_values(array_diff($certificates, [$certificatePath]));
            $profile->certificates = json_encode($certificates);
            $profile->save();

            // Kiểm tra xem file có tồn tại trong storage không rồi mới xóa
            if (Storage::disk('public')->exists($certificatePath)) {
                Storage::disk('public')->delete($certificatePath);
            }

            return $this->respondOk('Xóa chứng chỉ thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
    public function getRecentCourses()
    {
        try {
            $user = Auth::user();

            $recentCourses = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                ->join('courses', 'chapters.course_id', '=', 'courses.id')
                ->join('course_users', function ($join) use ($user) {
                    $join->on('course_users.course_id', '=', 'courses.id')
                        ->where('course_users.user_id', '=', $user->id);
                })
                ->where('lesson_progress.user_id', $user->id)
                ->groupBy('courses.id', 'courses.name', 'courses.thumbnail', 'courses.slug', 'course_users.progress_percent')
                ->select(
                    'courses.id as course_id',
                    'courses.name as course_name',
                    'courses.thumbnail',
                    'courses.slug',
                    'course_users.progress_percent',
                    DB::raw('MAX(lesson_progress.updated_at) as last_updated')
                )
                ->orderByDesc('last_updated')
                ->limit(10)
                ->get();

            $coursesWithProgress = $recentCourses->map(function ($course) use ($user) {
                $courseModel = Course::with(['chapters.lessons.lessonable'])->find($course->course_id);

                if (!$courseModel) {
                    return null;
                }
                $lessonIds = $courseModel->chapters->flatMap(function ($chapter) {
                    return $chapter->lessons->pluck('id');
                });
                $totalLessons = $lessonIds->count();
                $completedCount = LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('is_completed', 1)
                    ->count();

                $lessonProgress = LessonProgress::query()
                    ->where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->with('lesson:id,title')
                    ->latest('updated_at')
                    ->first();

                if (!$lessonProgress) {
                    $firstChapter = $courseModel->chapters->first();
                    $firstLesson = $firstChapter ? $firstChapter->lessons->where('is_completed', false)->first() : null;

                    $currentLesson = $firstLesson ? [
                        'id' => $firstLesson->id,
                        'title' => $firstLesson->title
                    ] : null;
                } else {
                    if ($course->progress_percent == 100) {
                        $lastChapter = $courseModel->chapters->last();
                        $lastLesson = $lastChapter ? $lastChapter->lessons->last() : null;

                        $currentLesson = $lastLesson ? [
                            'id' => $lastLesson->id,
                            'title' => $lastLesson->title
                        ] : null;
                    } else {
                        $currentLesson = [
                            'id' => $lessonProgress->lesson->id,
                            'title' => $lessonProgress->lesson->title
                        ];
                    }
                }

                return [
                    'id' => $course->course_id,
                    'name' => $course->course_name,
                    'slug' => $course->slug,
                    'thumbnail' => $course->thumbnail,
                    'total_lessons' => $totalLessons,
                    'progress_percent' => $course->progress_percent,
                    'completed_lessons' => $completedCount,
                    'current_lesson' => $currentLesson
                ];
            })->filter();

            return $this->respondOk('Các khóa học bạn đã học gần đây:', [
                'courses' => $coursesWithProgress->values()
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}
