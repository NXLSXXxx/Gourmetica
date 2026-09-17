<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();

            if ($user) {
                // Update google_id if it's missing (email match)
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar()
                    ]);
                }
            } else {
                $clienteRole = Role::where('slug', 'cliente')->first();
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                    'role_id' => $clienteRole->id,
                ]);
            }

            Auth::login($user);

            return redirect()->route('shop.index');
            
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Error al autenticar con Google: ' . $e->getMessage());
        }
    }
}
