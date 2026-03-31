<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => bcrypt(Str::random(16))
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;
        //jika akan di integrasikan
        // $frontendUrl = env('FRONTEND_URL');
        // return redirect($frontendUrl . '/oauth/callback?token=' . $token);

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
