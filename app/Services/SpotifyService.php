<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class SpotifyService
{
    const baseUrl = 'https://accounts.spotify.com/';


    public function onAuthed(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $storedState = $request->session()->pull('state');

        if ($state !== $storedState) {
            // return null;
            echo 'state error <br />';
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
            echo 'token req error <br />';
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
        $user->password = $userId;


        $token = $user->spotifyToken()->firstOrNew();
        $token->fill($info);

        $user->save();
        $user->spotifyToken()->save($token);

    }

    public function getLoginParams()
    {
        $state = bin2hex(openssl_random_pseudo_bytes(16));

        $url = self::endpoint('authorize', [
            'response_type' => 'code',
            'client_id' => config('spotify.client_id'),
            'scope' => 'user-read-private user-read-email user-modify-playback-state user-read-playback-state streaming',
            'redirect_uri' => url('callback'),
            'state' => $state,
        ]);


        return [
            'state' => $state,
            'redirectUrl' => $url,
        ];
    }

    public function isExpired()
    {

        return false;
    }

    public function refreshAccessToken($user)
    {
        $tokenInfo = $user->spotifyToken;

        if (is_null($tokenInfo)) {
            return null;
        }

        $spotifyToken = $user->spotifyToken;

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
        $tokenInfo->fill($accessTokenInfo);
        $user->spotifyToken()->save($tokenInfo);

        return $tokenInfo;
    }

    static function endpoint($e, $query = [])
    {
        $url = self::baseUrl . $e;

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}