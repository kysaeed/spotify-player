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

        ///////


        if (!$user->spotifyToken) {
            return redirect()->route('guest');
        }

        return view('content');
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


    public function state(Request $request, SpotifyService $spotify)
    {
        $user = Auth::user();
        $state = $spotify->getState($user);

        return $state;
    }

    public function device(Request $request, SpotifyService $spotify)
    {
        $device_id = $request->input('device');
        $user = Auth::user();
        $devices = $spotify->device($user);

        $device = null;
        foreach ($devices['devices'] as $d) {
            if ($d['id'] === $device_id) {
                $device = $d;
            }
        }

        if ($device) {
            $spotify->setDevice($user, $device_id);
        }

        return $devices;
    }

    public function deviceList(Request $request, SpotifyService $spotify)
    {
        $user = Auth::user();
        $devices = $spotify->device($user);

        return $devices;
    }

    public function play(Request $request, SpotifyService $spotify)
    {
        $user = Auth::user();
        return $spotify->togglePlay($user);

    }

    public function next(Request $request, SpotifyService $spotify)
    {

    }

    public function prev(Request $request, SpotifyService $spotify)
    {

    }
}
