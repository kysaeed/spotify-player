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

    public function accessToken(Request $request, SpotifyService $spotify)
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $acccessToken = $spotify->getAccessToken($user);

        return [
            'token' => $acccessToken,
        ];
    }

    public function callback(Request $request, SpotifyService $spotify)
    {
        $user = $spotify->onAuthed($request);
        if ($user) {
            Auth::login($user);
        }

        return redirect()->route('top');
    }

}
