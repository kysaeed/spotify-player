<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Auth;

use App\Models\User;

class LoginControler extends Controller
{

    public function login(Request $request)
    {
        $state = bin2hex(openssl_random_pseudo_bytes(16));
        $request->session()->put('state', $state);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('spotify.client_id'),
            'scope' => 'user-read-private user-read-email user-modify-playback-state streaming',
            'redirect_uri' => url('callback'),
            'state' => $state,
        ]);

        $url = 'https://accounts.spotify.com/authorize';
        if (!empty($query)) {
            $url = "{$url}?{$query}";
        }

        return redirect($url);
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $storedState = $request->session()->pull('state');

        if ($state !== $storedState) {
            dd($state, $storedState);
        }

        $client_id = config('spotify.client_id');
        $client_secret = config('spotify.client_secret');

        $auth = base64_encode($client_id . ':' . $client_secret);
        $res = Http::asForm()->acceptJson()->withHeaders([
            'Authorization' => 'Basic ' . $auth,
        ])->post('https://accounts.spotify.com/api/token', [
            'code' => $code,
            'redirect_uri' => url('callback'),
            'grant_type' => 'authorization_code',
        ]);

        if (!$res->successful()) {
            dd($res);
        }

        $info = json_decode($res->body(), true);


        $res = Http::withToken($info['access_token'])->get('https://api.spotify.com/v1/me');
        if (!$res->successful()) {
            dd($res);
        }

        $me = json_decode($res->body(), true);

        $userId = $me['id'];

        $user = User::where('email', $me['email'])->first();

        if (!$user) {
            $user = new User();
        }
        $user->name = $me['display_name'];
        $user->email = $me['email'];
        $user->password = $userId; // todo:
        $user->spotify_token = json_encode($info);

        $user->save();

        Auth::login($user, true);


        return redirect()->route('top');
    }

}
