<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Http;

class PlayerController extends Controller
{

    public function index(Request $request)
    {

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('guest');
        }

        $tokenInfo = json_decode($user->spotify_token, true);


        $refresh = '';

        $client_id = config('spotify.client_id');
        $client_secret = config('spotify.client_secret');
        $auth = base64_encode($client_id . ':' . $client_secret);
        $res = Http::asForm()->acceptJson()->withHeaders([
            'Authorization' => 'Basic ' . $auth,
        ])->post('https://accounts.spotify.com/api/token', [
            // 'code' => $code,
            // 'redirect_uri' => url('callback'),
            // 'grant_type' => 'authorization_code',

            'grant_type' => 'refresh_token',
            'refresh_token' => $tokenInfo['refresh_token'],
        ]);

        if (!$res->successful()) {
            Auth::logout();
            return redirect()->route(('guest'));
        }

        $accessTokenInfo = json_decode($res->body(), true);



        $tokenInfo['access_token'] = $accessTokenInfo['access_token'];

        $user->spotify_token = json_encode($tokenInfo);
        $user->save();

        return view('content', [
            'token' => $tokenInfo['access_token'],
        ]);
    }

    public function guest()
    {

        return view('guest');
    }
}
