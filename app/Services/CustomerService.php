<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function getAll($filters = [])
    {
        return Customer::with('user')
            ->withCount('bookings')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('cccd_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when(($filters['type'] ?? null) === 'online', function ($query) {
                $query->whereNotNull('user_id');
            })
            ->when(($filters['type'] ?? null) === 'walk_in', function ($query) {
                $query->whereNull('user_id');
            })
            ->when($filters['gender'] ?? null, function ($query, $gender) {
                $query->where('gender', $gender);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    
    public function createWalkIn(array $data)
    {
        return Customer::create([
            'full_name'   => $data['full_name'],
            'phone'       => $data['phone'],
            'cccd_number' => $data['cccd_number'] ?? null,
            'email'       => $data['email'] ?? null,
            'birthday'    => $data['birthday'] ?? null,
            'gender'      => $data['gender'] ?? 'other',
            'address'     => $data['address'] ?? null,
            'user_id'     => null,
        ]);
    }

    
    public function syncFromUser(User $user)
    {
        return Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'email'     => $user->email,
            ]
        );
    }

    
    public function update(Customer $customer, array $data)
    {
        return DB::transaction(function () use ($customer, $data) {
            
            $customer->update($data);

            
            if ($customer->user_id) {
                $customer->user()->update([
                    'name'  => $data['full_name'] ?? $customer->full_name,
                    'email' => $data['email'] ?? $customer->email,
                ]);
            }

            return $customer;
        });
    }

    
    public function delete(Customer $customer)
    {
        return DB::transaction(function () use ($customer) {
            
            
            return $customer->delete();
        });
    }

    public function updateStats(Customer $customer, $amount)
    {
        $customer->increment('total_bookings');
        $customer->increment('total_spent', $amount);
    }

    public function getBookingHistory(Customer $customer, $filters = [])
    {
        return $customer->bookings()
            ->with(['bookingRooms.room', 'bookingRooms.definition'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('booking_code', 'like', "%{$search}%")
                        ->orWhereHas('bookingRooms.room', function ($roomQuery) use ($search) {
                            $roomQuery->where('room_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->through(function ($booking) {
                $firstRoom = $booking->bookingRooms->first();

                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'source' => $booking->source,
                    'status' => $booking->status,
                    'room_number' => $firstRoom?->room?->room_number,
                    'room_type_name' => $firstRoom?->definition?->name ?? '-',
                    'check_in_expected' => optional($booking->check_in_expected)->toISOString(),
                    'check_out_expected' => optional($booking->check_out_expected)->toISOString(),
                    'total_price' => (float) ($booking->total_amount ?? 0),
                ];
            })
            ->withQueryString();
    }
}
