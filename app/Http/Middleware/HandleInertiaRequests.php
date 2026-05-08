<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    
    protected $rootView = 'app';

    
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => fn () => $this->getRolePermissions($request),
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'onlineBookingAlert' => fn() => $this->getOnlineBookingAlert($request),
        ];
    }

    private function getOnlineBookingAlert(Request $request): array
    {
        if (!$request->user() || (!$request->routeIs('admin.*') && !$request->routeIs('dashboard'))) {
            return [
                'pending_count' => 0,
                'latest' => [],
            ];
        }

        $pendingQuery = Booking::query()
            ->where('source', 'online')
            ->where('status', 'pending');

        $latest = (clone $pendingQuery)
            ->with('customer:id,full_name,phone')
            ->latest()
            ->limit(5)
            ->get(['id', 'booking_code', 'customer_id', 'created_at']);

        return [
            'pending_count' => (clone $pendingQuery)->count(),
            'latest' => $latest,
        ];
    }

    private function getRolePermissions(Request $request): array
    {
        $user = $request->user();

        if (!$user || !$user->role_id) {
            return [];
        }

        if ($user->role === 'admin') {
            return ['*'];
        }

        $permissions = $user->roleRelation?->permissions;

        return is_array($permissions) ? $permissions : [];
    }
}
