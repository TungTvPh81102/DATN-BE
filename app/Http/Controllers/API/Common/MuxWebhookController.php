<?php

namespace App\Http\Controllers\API\Common;

use App\Events\AlreadyLiveEvent;
use App\Events\LiveSessionStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use App\Models\LiveStreamCredential;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MuxWebhookController extends Controller
{
    use ApiResponseTrait, LoggableTrait;

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        $eventType = $payload['type'] ?? null;

        Log::info('Received Mux webhook', ['payload' => $payload]);

        try {
            switch ($eventType) {
                case 'video.live_stream.connected':
                    return $this->handleStreamConnected($payload);

                case 'video.live_stream.idle':
                    return $this->handleStreamIdle($payload);

                case 'video.live_stream.disconnected':
                    return $this->handleStreamDisconnected($payload);

                case 'video.asset.ready':
                    return $this->handleAssetReady($payload);

                case 'video.asset.created':
                    return $this->handleAssetCreated($payload);

                case 'video.asset.live_stream_completed':
                    return $this->handleLiveStreamCompleted($payload);

                default:
                    Log::info('Không tìm thấy Event type của Mux', ['type' => $eventType]);
                    return $this->respondError('Không tìm thấy Event type của Mux', [
                        'status' => 'ignored'
                    ]);
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function handleStreamConnected($payload)
    {
        try {
            $streamId = $payload['data']['id'] ?? null;

            if (!$streamId) {
                return $this->respondError('Không tìm thấy Stream ID');
            }

            $credential = LiveStreamCredential::where('mux_stream_id', $streamId)->first();

            if (!$credential) {
                return $this->respondError('Không tìm thấy mã streamKey trong hệ thống');
            }

            $instructorId = $credential->instructor_id;

            $existingLiveSession = LiveSession::where('instructor_id', $instructorId)
                ->where('status', 'live')
                ->first();

            if ($existingLiveSession) {
                $this->disableMuxStream($streamId);

                return $this->respondError('Bạn đang có một phiên live đang hoạt động');
            }

            $scheduledSession = LiveSession::where('instructor_id', $instructorId)
                ->where('status', 'upcoming')
                ->where('starts_at', '>=', Carbon::now())
                ->orderBy('starts_at', 'asc')
                ->first();

            if ($scheduledSession) {
                $scheduledSession->update([
                    'status' => 'live',
                    'actual_start_time' => Carbon::now(),
                ]);

                $conversation = Conversation::query()->firstOrCreate([
                    'owner_id' =>  $instructorId,
                    'name' => $scheduledSession->title ?? 'Buổi học trực tuyến của giảng viên ' . $instructorId . ' - ' . Carbon::now()->format('d/m/Y H:i'),
                    'type' => 'group',
                    'status' => 1,
                    'conversationable_type' => LiveSession::class,
                    'conversationable_id' => $scheduledSession->id,
                ]);

                $existingParticipant = LiveSessionParticipant::query()->where([
                    'user_id' => $instructorId,
                    'live_session_id' =>    $scheduledSession->id,
                ])->first();

                if (!$existingParticipant) {
                    LiveSessionParticipant::query()->create([
                        'user_id' => $instructorId,
                        'live_session_id' =>    $scheduledSession->id,
                        'role' => 'moderator',
                        'joined_at' => now()
                    ]);
                }
                
                $conversationUser = ConversationUser::query()->where([
                    'conversation_id' => $conversation->id,
                    'user_id' => $instructorId,
                ])->first();

                if (!$conversationUser) {
                    ConversationUser::query()->create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $instructorId,
                        'is_blocked' => false,
                        'last_read_at' => now()
                    ]);
                }

                event(new LiveSessionStatusChanged($scheduledSession->id, 'live', [
                    'session' => $scheduledSession->toArray(),
                    'started_at' => Carbon::now(),
                    'playback_id' => $credential->mux_playback_id,
                ]));
            } else {
                return $this->respondError('Không tìm thấy phiên sự kiện đã lên lịch');
            }

            return $this->respondOk('Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function handleStreamIdle($payload)
    {
        try {
            $streamId = $payload['data']['id'] ?? null;

            if (!$streamId) {
                return $this->respondError('Không tìm thấy Stream ID');
            }

            $credential = LiveStreamCredential::where('mux_stream_id', $streamId)->first();

            if (!$credential) {
                return $this->respondError('Không tìm thấy mã streamKey trong hệ thống');
            }

            return $this->respondOk('Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function handleStreamDisconnected($payload)
    {
        try {
            $streamId = $payload['data']['id'] ?? null;
            $accessId = $payload['data']['active_asset_id'] ?? null;

            if (!$streamId) {
                return response()->json(['status' => 'error', 'message' => 'Stream ID missing'], 400);
            }

            $credential = LiveStreamCredential::where('mux_stream_id', $streamId)->first();

            if (!$credential) {
                return $this->respondError('Không tìm thấy mã streamKey trong hệ thống');
            }

            $activeSession = LiveSession::where('instructor_id', $credential->instructor_id)
                ->where('status', 'live')
                // ->where('actual_start_time', '<=', Carbon::now())
                ->first();

            Log::info('Sự kiện', ['liveSession' => $activeSession]);

            if ($activeSession) {
                $activeSession->update([
                    'status' => 'ended',
                    'actual_end_time' => Carbon::now(),
                    'recording_asset_id' =>   $accessId,
                ]);

                event(new LiveSessionStatusChanged($activeSession->id, 'ended', [
                    'session' => $activeSession->toArray(),
                    'ended_at' => Carbon::now()
                ]));
            }

            return $this->respondOk('Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function handleAssetCreated($payload)
    {
        try {
            $assetId = $payload['data']['id'] ?? null;
            $assetData = $payload['data'] ?? [];

            if (!$assetId) {
                return $this->respondError('Không tìm thấy Asset ID');
            }

            $liveSessions = LiveSession::query()
                ->where('instructor_id', 2)
                ->where('status', 'ended')
                ->orderBy('actual_end_time', 'desc')
                ->get();

            foreach ($liveSessions as $session) {
                $credential = $session->credential;

                if ($credential && isset($assetData['master_access']) && $assetData['master_access'] == 'none') {
                    $session->update([
                        'recording_asset_id' => $assetId,
                    ]);

                    Log::info('Recording asset created for session', [
                        'session_id' => $session->id,
                        'asset_id' => $assetId
                    ]);

                    break;
                }
            }

            return $this->respondOk('Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function handleAssetReady($payload)
    {
        try {
            $assetId = $payload['data']['id'] ?? null;
            $assetData = $payload['data'] ?? [];

            if (!$assetId) {
                return $this->respondError('Không tìm thấy Asset ID');
            }

            $liveSession = LiveSession::where('recording_asset_id', $assetId)->first();

            if (!$liveSession) {
                return $this->respondError('Không tìm thấy phiên live nào với Asset ID này');
            }

            $playbackId = null;
            if (isset($assetData['playback_ids']) && is_array($assetData['playback_ids']) && count($assetData['playback_ids']) > 0) {
                $playbackId = $assetData['playback_ids'][0]['id'] ?? null;
            }

            $liveSession->update([
                'recording_playback_id' => $playbackId,
                'recording_url' => $playbackId ? "https://stream.mux.com/{$playbackId}.m3u8" : null,
                'duration' => $assetData['duration'] ?? null,
            ]);

            event(new LiveSessionStatusChanged($liveSession->id, 'recorded', [
                'session' => $liveSession->toArray(),
                'playback_id' => $playbackId,
                'duration' => $assetData['duration'] ?? null
            ]));


            return $this->respondOk('Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }


    private function handleLiveStreamCompleted($payload)
    {
        try {
            $assetId = $payload['data']['id'] ?? null;
            $assetData = $payload['data'] ?? [];
            $viewersCount = $payload['data']['viewers_count'] ?? 0;

            if (!$assetId) {
                return $this->respondError('Không tìm thấy Asset ID');
            }

            $liveSession = LiveSession::where('recording_asset_id', $assetId)->first();

            if (!$liveSession) {
                return $this->respondError('Không tìm thấy phiên live nào với Asset ID này');
            }

            $playbackId = null;
            if (isset($assetData['playback_ids']) && is_array($assetData['playback_ids']) && count($assetData['playback_ids']) > 0) {
                $playbackId = $assetData['playback_ids'][0]['id'] ?? null;
            }

            $liveSession->update([
                'recording_playback_id' => $playbackId,
                'recording_url' => $playbackId ? "https://stream.mux.com/{$playbackId}.m3u8" : null,
                'duration' => $assetData['duration'] ?? null,
                'viewers_count' => $viewersCount,
            ]);

            event(new LiveSessionStatusChanged($liveSession->id, 'completed', [
                'session' => $liveSession->toArray(),
            ]));

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function disableMuxStream($streamId)
    {
        try {
            $muxTokenId = config('services.mux.token_id');
            $muxTokenSecret = config('services.mux.token_secret');

            $config = new \MuxPhp\Configuration();
            $config->setUsername($muxTokenId);
            $config->setPassword($muxTokenSecret);

            $httpClient = new \GuzzleHttp\Client();

            $muxClient = new \MuxPhp\Api\LiveStreamsApi($httpClient, $config);

            $muxClient->disableLiveStream($streamId);

            Log::info('Đã disable stream Mux thành công', ['stream_id' => $streamId]);
        } catch (\Exception $e) {
            Log::error('Lỗi disable Mux stream', ['error' => $e->getMessage()]);
        }
    }
}
