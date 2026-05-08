<?php

namespace App\Services;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function getAll($filters = [])
    {
        return User::query()
            ->with('roleRelation:id,slug,name')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_id'] ?? null, function ($query, $roleId) {
                $query->where('role_id', $roleId);
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['status']);
            })
            ->where('id', '!=', auth()->id())
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function findById($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'role'      => $data['role'] ?? 'customer',
                'is_active' => $data['is_active'] ?? true,
            ]);

            if ($user->role === 'customer') {
                Customer::create([
                    'user_id'     => $user->id,
                    'full_name'   => $user->name,
                    'email'       => $user->email,
                    'phone'       => $data['phone'] ?? null,
                    'cccd_number' => $data['cccd_number'] ?? null,
                    'address'     => $data['address'] ?? null,
                    'gender'      => $data['gender'] ?? null,
                ]);
            }

            return $user;
        });
    }

    
    public function update(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            if ($user->role === 'customer') {
                
                
                Customer::updateOrCreate(
                    ['user_id' => $user->id], 
                    [
                        'full_name'   => $user->name,
                        'email'       => $user->email,
                        'phone'       => $data['phone'] ?? ($user->customer->phone ?? null),
                        'cccd_number' => $data['cccd_number'] ?? ($user->customer->cccd_number ?? null),
                        'address'     => $data['address'] ?? ($user->customer->address ?? null),
                        'gender'      => $data['gender'] ?? ($user->customer->gender ?? null),
                    ]
                );
            }

            return $user;
        });
    }

    public function delete(User $user)
    {
        if ($user->id === auth()->id()) {
            throw new \Exception('Không thể tự xóa tài khoản của chính mình!');
        }

        return $user->delete();
    }

    public function toggleStatus(User $user)
    {
        return $user->update([
            'is_active' => !$user->is_active
        ]);
    }
}
