<?php

namespace App\Http\Controllers\API\Common;

use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Common\UploadImageRequest;
use App\Models\Course;
use App\Models\Media;
use App\Models\MembershipPlan;
use App\Models\Rating;
use App\Models\User;
use App\Services\VideoUploadService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CommonController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

    const FOLDER_NAME = 'uploads';

    protected $videoUploadService;

    public function __construct(VideoUploadService $videoUploadService)
    {
        $this->videoUploadService = $videoUploadService;
    }

    public function instructorOrderByCountCourse(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $page == 1 ? 4 : 2;
            $maxInstructors = 10;


            $query = User::query()->role('instructor')
                ->whereHas('courses', function ($query) {
                    $query->where('status', 'approved');
                })
                ->withCount(['courses' => function ($query) {
                    $query->where('status', 'approved');
                }])
                ->orderByDesc('courses_count');

            $totalInstructors = $query->count();

            $instructors = $query
                ->take($maxInstructors)
                ->skip(($page - 1) * $perPage)
                ->get()
                ->map(function ($instructor) {
                    return [
                        'id' => $instructor->id,
                        'code' => $instructor->code,
                        'name' => $instructor->name,
                        'email' => $instructor->email,
                        'avatar' => $instructor->avatar_url,
                        'total_approved_courses' => $instructor->courses_count
                    ];
                });

            $hasMore = $totalInstructors > ($page * $perPage);

            return $this->respondOk('Danh sách giảng viên theo số lượng khoá học', [
                'instructors' => $instructors,
                'has_more' => $hasMore
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getCheckProfileUser(Request $request)
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Vui lòng đăng nhập');
            }

            $profile = $user->profile;

            $aboutMeExists = !empty($profile->about_me);
            $phoneExists = !empty($profile->phone);
            $addressExists = !empty($profile->address);

            if (!$aboutMeExists || !$phoneExists || !$addressExists) {
                return $this->respondOk('Thiếu thông tin', false);
            }

            return $this->respondOk('Thông tin đầy đủ', true);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function instructorInfo(string $code)
    {
        try {
            $user = User::where('code', $code)
                ->where('status', '!=', 'blocked')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'instructor');
                })->first();

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy giảng viên');
            }

            $info_instructor = DB::table('users')
                ->select(
                    'users.name',
                    'users.avatar',
                    'profiles.bio',
                    'users.code',
                    'users.email',
                    'profiles.address',
                    'profiles.phone',
                    'profiles.about_me',
                    'users.created_at',
                    'users.updated_at',
                    DB::raw('ROUND(AVG(DISTINCT ratings.rate), 1) as avg_rating, COUNT(DISTINCT courses.id) as total_courses, COUNT(DISTINCT follows.id) as total_followers')
                )
                ->where('users.code', $code)
                ->where('users.status', '!=', 'blocked')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->leftJoin('courses', 'users.id', '=', 'courses.user_id')
                ->leftJoin('ratings', 'courses.id', '=', 'ratings.course_id')
                ->leftJoin('follows', 'follows.instructor_id', '=', 'users.id')
                ->groupBy(
                    'users.id',
                    'users.name',
                    'users.avatar',
                    'profiles.bio',
                    'users.code',
                    'users.email',
                    'profiles.address',
                    'profiles.phone',
                    'profiles.about_me',
                    'users.created_at',
                    'users.updated_at'
                )
                ->first();

            return response()->json([
                'message' => 'Thông tin giảng viên',
                'instructor' => $info_instructor
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getCourseInstructor(string $code)
    {
        try {
            $user = User::where('code', $code)
                ->where('status', '!=', 'blocked')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'instructor');
                })->first();

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy giảng viên');
            }

            $courses_instructor = DB::table('courses')
                ->selectRaw(
                    'courses.id,
                    courses.name,
                    courses.slug,
                    courses.thumbnail,
                    courses.price,
                    courses.price_sale,
                    courses.is_free,
                    courses.total_student,
                    ROUND(AVG(ratings.rate), 1) as avg_rating,
                    COUNT(DISTINCT lessons.id) as lessons_count'
                )
                ->where([
                    'courses.status' => 'approved',
                    'courses.visibility' => 'public',
                    'courses.user_id' => $user->id
                ])
                ->join('chapters', 'chapters.course_id', '=', 'courses.id')
                ->join('lessons', 'lessons.chapter_id', '=', 'chapters.id')
                ->leftJoin('ratings', 'courses.id', '=', 'ratings.course_id')
                ->groupBy(
                    'courses.id',
                    'courses.name',
                    'courses.slug',
                    'courses.thumbnail',
                    'courses.price',
                    'courses.price_sale',
                    'courses.total_student'
                )
                ->paginate(9);

            return response()->json([
                'message' => 'Thông tin giảng viên',
                'courses' => $courses_instructor
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getMemberShipPlans(string $code)
    {
        try {
            $user = User::query()->where('code', $code)
                ->where('status', '!=', 'blocked')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'instructor');
                })->first();

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy giảng viên');
            }

            $memberShipPlans = MembershipPlan::query()
                ->with('membershipCourseAccess', function ($query) {
                    $query->select('id', 'code', 'name', 'slug', 'thumbnail')
                        ->where('status', 'approved')
                        ->where('visibility', 'public');
                })
                ->where([
                    'instructor_id' => $user->id,
                    'status' => 'active'
                ])->get();

            $memberShipPlans->makeHidden([
                'instructor_id',
                'created_at',
                'updated_at'
            ]);

            return $this->respondOk('Danh sách gói membership của giảng viên: ' . $user->name, $memberShipPlans);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function uploadImage(UploadImageRequest $request)
    {
        try {
            $file = $request->file('image');

            $filePath = $this->uploadToLocal($file, self::FOLDER_NAME);

            if (!$filePath || is_array($filePath)) {
                return $this->respondError('Tải ảnh lên thất bại');
            }

            $fullPath = Storage::url($filePath);

            return $this->respondOk('Tải ảnh lên thành công', $fullPath);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getUploadUrl()
    {
        try {
            $result = $this->videoUploadService->createUploadUrl();

            return $this->respondOk('Tạo đường dẫn thành công', [
                'id' => $result['id'],
                'url' => $result['url'],
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getInfoVideo($uploadId)
    {
        try {
            $result =  $this->videoUploadService->getVideoInfoFromMux($uploadId);

            if (!$result || !is_array($result)) {
                return $this->respondError('Lỗi khi lấy thông tin video');
            }

            $user = Auth::user();

            $exists = Media::query()
                ->where('user_id', $user->id)
                ->where('type', 'video')
                ->where(function ($query) use ($result) {
                    $query->where('asset_id', $result['asset_id'] ?? '')
                        ->orWhere('playback_id', $result['playback_id'] ?? '');
                })
                ->exists();

            if (!$exists) {
                $count = Media::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'video')
                    ->count();

                Media::create([
                    'user_id' => $user->id,
                    'title' => 'Bài giảng ' . ($count + 1),
                    'type' => 'video',
                    'asset_id' => $result['asset_id'] ?? '',
                    'playback_id' => $result['playback_id'] ?? '',
                    'thumbnail' => $result['thumbnail'] ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1744405490/Gemini_Generated_Image_i0dae4i0dae4i0da_pwjntz.jpg',
                ]);
            }

            return $this->respondOk('Lấy dữ liệu thành công', [
                'asset_id' => $result['asset_id'] ?? null,
                'playback_id' => $result['playback_id'] ?? null,
                'duration' => $result['duration'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMedia(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user && !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $query = Media::query();

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('playback_id', 'like', "%{$search}%")
                        ->orWhere('path', 'like', "%{$search}%");
                });
            }

            if ($request->has('type') && !empty($request->type) && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            $orderBy = $request->order_by ?? 'created_at';
            $direction = $request->direction ?? 'desc';
            $query->orderBy($orderBy, $direction);

            $perPage = $request->per_page ?? 12;
            $media = $query->paginate(5);

            return response()->json([
                'data' => $media,
                'meta' => [
                    'total' => $media->total(),
                    'per_page' => $media->perPage(),
                    'current_page' => $media->currentPage(),
                    'last_page' => $media->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function chatBox(Request $request)
    {
        if ($request->input('clear_history', false)) {
            return $this->clearChatHistory();
        }

        $userMessage = $request->input('message', '');
        $context = $request->input('context', 'Chưa có, khi chưa có bạn hãy hỏi lại học viên');
        $timestamp = Carbon::now()->format('[d/m/Y H:i:s]');

        $chatHistory = Session::get('chat_history', []);

        if (empty($chatHistory)) {
            $chatHistory[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => "Bạn là trợ lý dạy học. Bối cảnh hiện tại: $context. Trả lời ngắn gọn, dễ hiểu và tập trung đúng vào nội dung."]
                ]
            ];
        }

        if (empty($userMessage)) return $this->respondError('Bạn chưa nhập nội dung đoạn chat');

        $chatHistory[] = [
            'role' => 'user',
            'parts' => [
                ['text' => "$timestamp Bạn: $userMessage"]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip',
        ])->post(
            'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key=' . env('GOOGLE_STUDIO_KEY'),
            ['contents' => $chatHistory]
        );

        $aiReply = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Không nhận được phản hồi, vui lòng thử lại';

        $chatHistory[] = [
            'role' => 'model',
            'parts' => [
                ['text' => "$timestamp AI: $aiReply"]
            ]
        ];

        Session::put('chat_history', $chatHistory);

        return $this->respondOk('Lấy dữ liệu thành công', [
            'reply' => $aiReply,
            'time' => Carbon::now()->format('H:i')
        ]);
    }

    public function clearChatHistory()
    {
        Session::forget('chat_history');
        return $this->respondNoContent();
    }
}
