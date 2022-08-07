<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Services\SpotifyService;

class PlayerController extends Controller
{

    public function index(Request $request, SpotifyService $spotify)
    {

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('guest');
        }
        $spotifyToken = $user->spotifyToken;
        if (is_null($spotifyToken)) {
            return redirect()->route('guest');
        }

        $spotify->refreshAccessToken($user);


        $tokenInfo = json_decode($user->spotify_token, true);

        $client_id = config('spotify.client_id');
        $client_secret = config('spotify.client_secret');
        $auth = base64_encode($client_id . ':' . $client_secret);
        $res = Http::asForm()->acceptJson()->withHeaders([
            'Authorization' => 'Basic ' . $auth,
        ])->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $spotifyToken->refresh_token,
        ]);

        if (!$res->successful()) {
            Auth::logout();
            return redirect()->route(('guest'));
        }

        $accessTokenInfo = json_decode($res->body(), true);
        $spotifyToken->access_token = $accessTokenInfo['access_token'];


        $user->spotify_token = json_encode($tokenInfo);
        $spotifyToken->save();

        return view('content', [
            'token' => $spotifyToken->access_token,
        ]);
    }

    public function guest()
    {

        return view('guest');
    }

    public function trackInfo(Request $request)
    {

        $user = Auth::user();

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return 'has not token';
        }

        $res = Http::withToken($token->access_token)->get('https://api.spotify.com/v1/me/player/currently-playing');
        if (!$res->successful()) {
            echo 'get track error.....';
            dd($res);
        }

        $track = json_decode($res->body(), true);


        return $track;
    }

    public function device(Request $request)
    {
        $device_id = $request->input('device');

        $user = Auth::user();
        $token = $user->spotifyToken;
        if (is_null($token)) {
            return 'has not token';
        }

        // $info = json_decode($user->spotify_token, true);
        $res = Http::withToken($token->access_token)->get('https://api.spotify.com/v1/me/player/devices');
        if (!$res->successful()) {
            echo 'get device error.....';
            dd($res);
        }

        $devices = json_decode($res->body(), true);
        $device = null;
        foreach ($devices['devices'] as $d) {
            if ($d['id'] === $device_id) {
                $device = $d;
            }
        }

        if ($device) {

            $res = Http::withToken($token->access_token)->put('https://api.spotify.com/v1/me/player', [
                'device_ids' => [$device['id']],
                'play' => false,
            ]);
            if (!$res->successful()) {
                echo 'device change error';
                dd($res);
            }

        }

        return $devices;
    }
}
