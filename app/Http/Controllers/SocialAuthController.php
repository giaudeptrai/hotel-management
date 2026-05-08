<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    
    public function redirect()
    {
        try {
            Log::info('Google redirect started');
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            Log::error('Google redirect failed', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            return redirect()->route('login')->withErrors(['error' => 'Không thể kết nối Google. ' . $e->getMessage()]);
        }
    }

    
    public function callback()
    {
        try {
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (InvalidStateException $e) {
                
                $googleUser = Socialite::driver('google')->stateless()->user();
            }

            $user = User::firstOrNew(['email' => $googleUser->email]);

            if (!$user->exists) {
                $user->password = bcrypt(Str::random(32));
            }

            $user->name = $googleUser->name;
            $user->google_id = $googleUser->id;
            
            $user->role = 'customer';
            $user->is_active = true;
            $user->save();

            $customer = Customer::where('user_id', $user->id)->first();
            $fallbackPhone = 'g' . substr(str_replace('-', '', $user->id), 0, 19);

            Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $user->name,
                    'phone' => $customer?->phone ?? $fallbackPhone,
                    'email' => $user->email,
                ]
            );

            Auth::login($user);

            return redirect()->to('/')->with('success', 'Đăng nhập thành công!');
        } catch (\Throwable $e) {
            Log::error('Google login failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Lỗi đăng nhập Google. Vui lòng thử lại.',
            ]);
        }
    }
}
