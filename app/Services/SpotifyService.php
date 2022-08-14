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
    const retryTimes = 1;

    public function onAuthed(Request $request)
    {
        $storedState = $request->session()->pull('state');

        $code = $request->input('code');
        $state = $request->input('state');

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

    public function getAccessToken($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;

        if (is_null($token)) {
            return null;
        }

        if ($this->isExpired($user)) {
            return $this->refreshAccessToken($user);
        }

        return $token->access_token;
    }

    public function refreshAccessToken($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;

        if (is_null($token)) {
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
        $token->fill($accessTokenInfo);
        $user->spotifyToken()->save($token);

        return $token->access_token;
    }

    public function isExpired($user)
    {
        if (is_null($user)) {
            return false;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return false;
        }
        if (Carbon::now()->diffInSeconds($token->updated_at) > ($token->expires_in - 60)) {
            return true;
        }

        return false;
    }

    static protected function retry($callback)
    {
        $time = self::retryTimes + 1;

        while ($time > 0) {
            $time--;
            try {
                return $callback();
            } catch (\Exception $ex) {
                if ($time < 1) {
                    throw $ex;
                }
            }
        }

        return null;
    }

    protected function getApiRequest($user, $apiName, $query = [])
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $access_token = $token->access_token;
        if ($this->isExpired($user)) {
            $access_token = $this->refreshAccessToken($user);
        }

        return self::retry(function() use ($user, $access_token, $apiName, $query) {
            $res = Http::withToken($access_token)->get(self::endpoint(self::apiBaseUrl, $apiName, $query));
            if (!$res->successful()) {
                if ($res->status() === 401) {
                    $access_token = $this->refreshAccessToken($user);
                    throw new \Exception('', $res->status());
                }
            }

            return $res;
        });
    }

    protected function putApiRequest($user, $apiName, $query = [], $data = [])
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $access_token = $token->access_token;
        if ($this->isExpired($user)) {
            $access_token = $this->refreshAccessToken($user);
        }

        return self::retry(function() use ($user, $access_token, $apiName, $query, $data) {
            $res = Http::withToken($access_token)->put(self::endpoint(self::apiBaseUrl, $apiName, $query), (Object)$data);
            if (!$res->successful()) {
                if ($res->status() === 401) {
                    $access_token = $this->refreshAccessToken($user);
                    throw new \Exception('', $res->status());
                }
            }

            return $res;
        });
    }

    public function getState($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $res = $this->getApiRequest($user, 'me/player');
        if (!$res->successful()) {
            echo 'get device error.....';
            dd($res);
        }

        $state = json_decode($res->body(), true);

        return $state;
    }

    public function getAudioAnalysis($user, $idTrack /* = '11dFghVXANMlKmJXsNCbNl' */)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        //
        $apiName = "audio-analysis/{$idTrack}";

        $res = $this->getApiRequest($user, $apiName);
        if (!$res->successful()) {
            echo 'get audio-analysis error.....';
            dd($res);
        }

        return json_decode($res->body(), true);

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


        $res = $this->getApiRequest($user, 'me/player/devices');
        if (!$res->successful()) {
            echo 'get device error.....';
            dd($res);
        }

        $devices = json_decode($res->body(), true);
        return $devices;
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

        $res = $this->putApiRequest($user, 'me/player', [], [
            'device_ids' => [$idDevice],
            'play' => false,
        ]);

        if (!$res->successful()) {
            echo 'set dieve err !';
            dd($res);
            return false;
        }

        return true;
    }

    public function togglePlay($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $state = $this->getState($user);
        $device = $state['device'] ?? null;
        $isPlaying = $state['is_playing'] ?? false;

        if ($isPlaying) {
            return $this->pause($user);
        }
        return $this->play($user);

    }

    public function play($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $res = $this->putApiRequest($user, 'me/player/play', [], []);
        if (!$res->successful()) {
            dd($res->body());
            return false;
        }
        return true;
    }

    public function pause($user)
    {
        if (is_null($user)) {
            return null;
        }

        $token = $user->spotifyToken;
        if (is_null($token)) {
            return null;
        }

        $res = $this->putApiRequest($user, 'me/player/pause', [], []);
        if (!$res->successful()) {
            dd($res);
            return false;
        }
        return true;
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