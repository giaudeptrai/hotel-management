<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffService
{
    
    public function getAll($filters = [])
    {
        $query = Staff::with(['user.role']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('is_active', (bool) $filters['status']);
        }

        if (!empty($filters['role_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('role_id', $filters['role_id']);
            });
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    
    public function createStaff(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            $user = User::create([
                'id'        => Str::uuid(), 
                'name'      => $data['full_name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'role_id'   => $data['role_id'],
                'is_active' => true,
            ]);

            
            return Staff::create([
                'user_id'    => $user->id,
                'staff_code' => 'NV-' . strtoupper(Str::random(5)), 
                'full_name'  => $data['full_name'],
                'phone'      => $data['phone'] ?? null,
                'cccd'       => $data['cccd'] ?? null,
                'is_active'  => true,
            ]);
        });
    }

    
    public function updateStaff($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $staff = Staff::findOrFail($id);
            $user = $staff->user;

            
            $userData = [
                'name'      => $data['full_name'],
                'email'     => $data['email'],
                'role_id'   => $data['role_id'],
                'is_active' => $data['is_active'] ?? true,
            ];

            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }
            $user->update($userData);

            
            $staff->update([
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'] ?? null,
                'cccd'      => $data['cccd'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $staff;
        });
    }

    
    public function deleteStaff($id)
    {
        $staff = Staff::findOrFail($id);

        
        if ($staff->user) {
            $staff->user->delete();
        } else {
            $staff->delete();
        }
    }
}
