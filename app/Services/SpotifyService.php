<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class SpotifyService
{
    const accountBaseUrl = 'https://accounts.spotify.com';
    const apiBaseUrl = 'https://api.spotify.com/v1';
    const scope = 'user-read-private user-read-email user-modify-playback-state user-read-playback-state streaming';


    public function onAuthed(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $storedState = $request->session()->pull('state');

        if ($state !== $storedState) {
            // return null;
            echo 'state error <br />';
            dd($state, $storedState);
            return false;
        }

        $client_id = config('spotify.client_id');
        $client_secret = config('spotify.client_secret');

        $auth = base64_encode($client_id . ':' . $client_secret);
        $res = Http::asForm()->acceptJson()->withHeaders([
            'Authorization' => 'Basic ' . $auth,
        ])->post(self::endpoint(self::accountBaseUrl, 'api/token'), [
            'code' => $code,
            'redirect_uri' => url('callback'),
            'grant_type' => 'authorization_code',
        ]);

        if (!$res->successful()) {
            echo 'token: reponse err';
            dd($res);
            return false;
        }

        $info = json_decode($res->body(), true);


        $res = Http::withToken($info['access_token'])->get('https://api.spotify.com/v1/me');
        if (!$res->successful()) {
            echo 'token req error <br />';
            dd($res);
            return false;
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

        return true;
    }

    public function getLoginParams()
    {
        $state = bin2hex(openssl_random_pseudo_bytes(16));

        $url = self::endpoint(self::accountBaseUrl, 'authorize', [
            'response_type' => 'code',
            'client_id' => config('spotify.client_id'),
            'scope' => self::scope,
            'redirect_uri' => url('callback'),
            'state' => $state,
        ]);

        return [
            'state' => $state,
            'redirectUrl' => $url,
        ];
    }

    // public function

    public function isExpired()
    {



        return false;
    }

    public function device($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $res = Http::withToken($token->access_token)->get(self::endpoint(self::apiBaseUrl, 'me/player/devices'));
        if (!$res->successful()) {
            echo 'get device error.....';
            dd($res);
        }

        $devices = json_decode($res->body(), true);

        return $devices;

        // $device = null;
        // foreach ($devices['devices'] as $d) {
        // }


        // return $device;
    }

    public function setDevice($user, $idDevice)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $res = Http::withToken($token->access_token)->put('https://api.spotify.com/v1/me/player', [
            'device_ids' => [$idDevice],
            'play' => false,
        ]);
        if (!$res->successful()) {
            return false;
        }

        return true;
    }

    public function refreshAccessToken($user)
    {
        if (is_null($user)) {
            return null;
        }

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
            return null;
        }

        $accessTokenInfo = json_decode($res->body(), true);
        $tokenInfo->fill($accessTokenInfo);
        $user->spotifyToken()->save($tokenInfo);

        return $tokenInfo->access_token;
    }

    static function endpoint($base, $e, $query = [])
    {
        $url = "{$base}/{$e}";

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}