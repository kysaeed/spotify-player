<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Services\SpotifyService;

class LoginControler extends Controller
{

    public function login(Request $request, SpotifyService $spotify)
    {
        $params = $spotify->getLoginParams();

        $request->session()->put('state', $params['state']);
        return redirect($params['redirectUrl']);
    }

    public function accessToken(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $tokenInfo = $user->spotifyToken;

        $client_id = config('spotify.client_id');
        $client_secret = config('spotify.client_secret');
        $auth = base64_encode($client_id . ':' . $client_secret);
        $res = Http::asForm()->acceptJson()->withHeaders([
            'Authorization' => 'Basic ' . $auth,
        ])->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokenInfo->refresh_token,
        ]);

        if (!$res->successful()) {
            Auth::logout();
            return redirect()->route(('guest'));
        }

        $accessTokenInfo = json_decode($res->body(), true);
        $tokenInfo->fill($accessTokenInfo);

        $user->spotifyToken()->save($tokenInfo);

        return [
            'token' => $tokenInfo->access_token,
        ];
    }

    public function callback(Request $request, SpotifyService $spotify)
    {
        $spotify->onAuthed($request);
        return redirect()->route('top');
    }

}
