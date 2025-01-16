<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LivestreamController extends Controller
{

    public function createLiveStream(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
        ]);

        $stream = $this->liveStream($validated['name']);
        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'stream_key' => $stream['stream_key'],
            'playback_id' => $stream['playback_id'],
        ];

        return response()->json(['message' => 'Lớp học được tạo thành công!', 'class' => $data]);
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
            'playback_id' => $data['data']['playback_ids'][0]['id'],
        ];
    }

}
