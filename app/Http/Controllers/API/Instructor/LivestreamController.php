<?php

namespace App\Http\Controllers\API\Instructor;

use App\Events\LiveChatMessageSent;
use App\Events\UserJoinedLiveSession;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\LiveStream\StoreLiveSchedule;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use App\Models\LiveStreamCredential;
use App\Models\Message;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LivestreamController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

    const FOLDER = 'live-schedules';

    public function index()
    {
        try {
            $liveSessions = LiveSession::query()
                ->with('instructor')
                ->where(function ($query) {
                    $query->where('status', 'Đang diễn ra')
                        ->orWhere(function ($query) {
                            $query->where('status', 'Sắp diễn ra')
                                ->where('start_time', '>', now());
                        });
                })
                ->orderBy('start_time', 'asc')
                ->get();

            if (empty($liveSessions)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            return $this->respondOk('Danh sách phiên live trên hệ thống', $liveSessions);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function show(string $id)
    {
        try {
            $lessonSessionInfo = LiveSession::query()
                ->with([
                    'instructor',
                    'conversation.messages' => function ($query) {
                        $query->orderBy('created_at', 'asc')->limit(20);
                    },
                    'participants' => function ($query) {
                        $query->select('user_id', 'live_session_id', 'role');
                    },
                    'conversation.users' => function ($query) {
                        $query->select('conversation_id', 'user_id', 'is_blocked');
                    },
                ])
                ->where(function ($query) {
                    $query->where('status', 'Đang diễn ra')
                        ->orWhere(function ($query) {
                            $query->where('status', 'Sắp diễn ra')
                                ->where('start_time', '>', now());
                        });
                })
                ->find($id);

            if (empty($lessonSessionInfo)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            return $this->respondOk('Thông tin phiên live', $lessonSessionInfo);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function getLivestreams(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $query = LiveSession::query()
                ->where('instructor_id', $user->id)
                ->orderBy('start_time', 'desc');

            if ($request->has('fromDate')) {
                $query->whereDate('created_at', '>=', $request->input('fromDate'));
            }
            if ($request->has('toDate')) {
                $query->whereDate('created_at', '<=', $request->input('toDate'));
            }

            $liveSessions = $query->get();

            if (empty($liveSessions)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            return $this->respondOk('Danh sách phiên live của: ' . $user->name, $liveSessions);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function startLivestream(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $existingLiveSession = LiveSession::query()
                ->where('instructor_id', $user->id)
                ->whereIn('status', ['Đang diễn ra', 'Sắp diễn ra'])
                ->first();

            if ($existingLiveSession) {
                return $this->respondError('Bạn đang có phiên live chưa kết thúc');
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'nullable|date',
            ]);

            $stream = $this->liveStream($validated['title']);

            $data = [
                'instructor_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_time' => $validated['start_time'] ?? now(),
                'stream_key' => $stream['stream_key'],
                'mux_playback_id' => $stream['playback_id'],
            ];

            $liveSession = LiveSession::query()->create($data);

            $conversation = Conversation::query()->firstOrCreate([
                'owner_id' => Auth::id(),
                'name' => $validated['title'],
                'type' => 'group',
                'status' => 1,
                'conversationable_type' => LiveSession::class,
                'conversationable_id' => $liveSession->id,
            ]);

            LiveSessionParticipant::query()->create([
                'user_id' => $user->id,
                'live_session_id' => $liveSession->id,
                'role' => 'host',
                'joined_at' => now()
            ]);

            ConversationUser::query()->firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'is_blocked' => false,
                'last_read_at' => now()
            ]);

            DB::commit();
            return $this->respondCreated('Tạo phiên live thành công', $liveSession);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function getStreamKey()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $liveStreamCredential = LiveStreamCredential::query()
                ->where('instructor_id', $user->id)
                ->first();

            if (empty($liveStreamCredential)) {
                return $this->respondNotFound('Không tìm thấy mã sự kiện phát sóng');
            }

            return $this->respondOk('Mã sự kiện phát sóng', $liveStreamCredential);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function generateStreamKey(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }


            $existing = LiveStreamCredential::query()->where('instructor_id', $user->id)->first();

            if ($existing) {
                return $this->respondError('Bạn đã có mã sự kiện trực tiếp');
            }

            $stream = $this->liveStream('Mã sự kiện phát sóng của giảng viên ' . $user->name);

            Log::info('Stream key generated: ' . json_encode($stream));

            if (empty($stream)) {
                return $this->respondError('Không tìm thấy nã sự kiện phát sống');
            }

            $data = [
                'instructor_id' => $user->id,
                'stream_key' => $stream['stream_key'],
                'mux_playback_id' => $stream['playback_id'],
                'mux_stream_id' => $stream['stream_id'],
            ];

            $liveStreamCredential = LiveStreamCredential::query()->create($data);

            DB::commit();
            return $this->respondCreated('Tạo phiên live thành công',   $liveStreamCredential);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function liveStream($streamName)
    {
        $httpClient = new \GuzzleHttp\Client();

        $response = $httpClient->post('https://api.mux.com/video/v1/live-streams', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(env('MUX_TOKEN_ID') . ':' . env('MUX_TOKEN_SECRET')),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'playback_policy' => ['public'],
                'new_asset_settings' => ['playback_policy' => 'public'],
                'name' => $streamName,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'stream_key' => $data['data']['stream_key'],
            'stream_id' => $data['data']['id'],
            'playback_id' => $data['data']['playback_ids'][0]['id'],
        ];
    }

    public function getLiveSchedules(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $liveSchedules = LiveSession::query()
                ->where('instructor_id', $user->id)
                ->orderBy('starts_at', 'desc')
                ->get()->map(function ($schedule) {
                    if ($schedule->thumbnail) {
                        $schedule->thumbnail = Storage::url($schedule->thumbnail);
                    }
                    return $schedule;
                });

            if ($liveSchedules->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy lịch phát sóng');
            }

            return $this->respondOk('Danh sách lịch phát sóng', $liveSchedules);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function getLiveSchedule(string $code)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $liveSchedule = LiveSession::query()
                ->with([
                    'instructor',
                    'liveStreamCredential',
                    'conversation.messages' => function ($query) {
                        $query->select('id', 'conversation_id', 'sender_id', 'content', 'created_at')
                            ->orderBy('created_at', 'desc')->limit(20);
                        $query->with(['sender' => function ($query) {
                            $query->select('id', 'name', 'avatar');
                        }]);
                    },
                    'participants' => function ($query) {
                        $query->select('user_id', 'live_session_id', 'role');
                    },
                    'conversation.users' => function ($query) {
                        $query->select('conversation_id', 'user_id', 'is_blocked');
                    },
                ])
                ->where('instructor_id', $user->id)
                ->where('code', $code)
                ->first();

            if (empty($liveSchedule)) {
                return $this->respondNotFound('Không tìm thấy lịch phát sóng');
            }

            if ($liveSchedule->thumbnail) {
                $liveSchedule->thumbnail = Storage::url($liveSchedule->thumbnail);
            }

            return $this->respondOk('Thông tin lịch phát sóng', $liveSchedule);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function createLiveSchedule(StoreLiveSchedule $request)
    {
        try {
            $data = $request->validated();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $liveStreamCredential = LiveStreamCredential::query()
                ->where('instructor_id', $user->id)
                ->first();

            if (empty($liveStreamCredential)) {
                return $this->respondNotFound('Không tìm thấy mã sự kiện phát sóng');
            }

            $data['code'] = Str::uuid()->toString();
            $data['instructor_id'] = $user->id;
            $data['starts_at'] = Carbon::parse($data['starts_at'])->tz('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
            $data['live_stream_credential_id'] = $liveStreamCredential->id;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadToLocal($request->file('thumbnail'), self::FOLDER);
            }

            $liveSession = LiveSession::create($data);

            return $this->respondOk('Tạo lịch phát sóng thành công', $liveSession);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    protected function createTemporaryUser($liveSessionId)
    {
        return User::query()->create([
            'code' => Str::random(10),
            'name' => 'Khách ' . Str::random(5),
            'email' => Str::random(10) . '@temporary.coursemely.com',
            'password' => Str::random(10),
            'is_temporary' => true,
            'temp_live_session_id' => $liveSessionId
        ]);
    }
}
