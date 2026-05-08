<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    private function isCustomerAccount(Request $request): bool
    {
        $user = $request->user();

        return (bool) ($user && ($user->role === 'customer' || $user->customer()->exists()));
    }

    
    public function edit(Request $request): Response
    {
        $authUser = $request->user();
        $customer = $authUser->customer;
        $isCustomerProfile = $authUser->role === 'customer' || (bool) $customer;
        $view = $isCustomerProfile ? 'Client/Profile/Edit' : 'Profile/Edit';

        return Inertia::render($view, [
            'mustVerifyEmail' => $authUser instanceof MustVerifyEmail,
            'status' => session('status'),
            'isCustomerProfile' => $isCustomerProfile,
            'customerProfile' => $isCustomerProfile ? [
                'phone' => $customer?->phone,
                'cccd_number' => $customer?->cccd_number,
                'birthday' => optional($customer?->birthday)->toDateString(),
                'gender' => $customer?->gender,
                'address' => $customer?->address,
            ] : null,
        ]);
    }

    
    public function editPassword(): Response
    {
        $request = request();
        $view = $this->isCustomerAccount($request) ? 'Client/Profile/Password' : 'Profile/Password';

        return Inertia::render($view);
    }

    
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $isCustomerProfile = $user->role === 'customer' || $user->customer()->exists();

        if ($isCustomerProfile) {
            $customer = $user->customer()->firstOrNew([
                'user_id' => $user->id,
            ]);

            $customer->fill([
                'full_name' => $user->name,
                'email' => $user->email,
                'phone' => $validated['phone'] ?? $customer->phone,
                'cccd_number' => $validated['cccd_number'] ?? null,
                'birthday' => $validated['birthday'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            $customer->save();
        }

        return Redirect::route('profile.edit');
    }

    
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
