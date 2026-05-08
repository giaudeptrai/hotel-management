<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::query()
            ->withCount('users')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create');
    }

    public function store(Request $request)
    {
        if (!Schema::hasColumn('roles', 'permissions')) {
            return back()->withErrors([
                'permissions' => 'Hệ thống chưa có cột permissions cho bảng roles. Vui lòng chạy migrate trước khi lưu phân quyền.',
            ])->withInput();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:1000',
            'level' => 'nullable|in:high,normal,restricted',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'nullable|string|max:255',
        ]);

        $role = Role::create($this->extractRolePayload($validated));

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã tạo nhóm quyền thành công.');
    }

    public function edit(Role $role)
    {
        $rolePayload = [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => Schema::hasColumn('roles', 'description') ? $role->description : null,
            'level' => Schema::hasColumn('roles', 'level') ? $role->level : 'normal',
            'is_active' => Schema::hasColumn('roles', 'is_active') ? (bool) $role->is_active : true,
            'permissions' => $this->getRolePermissions($role),
        ];

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $rolePayload,
        ]);
    }

    public function show(Role $role)
    {
        return redirect()->route('admin.roles.edit', $role->id);
    }

    public function update(Request $request, Role $role)
    {
        if (!Schema::hasColumn('roles', 'permissions')) {
            return back()->withErrors([
                'permissions' => 'Hệ thống chưa có cột permissions cho bảng roles. Vui lòng chạy migrate trước khi lưu phân quyền.',
            ])->withInput();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:1000',
            'level' => 'nullable|in:high,normal,restricted',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'nullable|string|max:255',
        ]);

        $role->update($this->extractRolePayload($validated));

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã cập nhật nhóm quyền thành công.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return back()->with('success', 'Đã xóa nhóm quyền thành công.');
    }

    private function extractRolePayload(array $validated): array
    {
        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $payload = [
            'name' => $validated['name'],
            'slug' => $slug,
        ];

        if (Schema::hasColumn('roles', 'description')) {
            $payload['description'] = $validated['description'] ?? null;
        }

        if (Schema::hasColumn('roles', 'level')) {
            $payload['level'] = $validated['level'] ?? 'normal';
        }

        if (Schema::hasColumn('roles', 'is_active')) {
            $payload['is_active'] = (bool) ($validated['is_active'] ?? true);
        }

        if (Schema::hasColumn('roles', 'permissions')) {
            $payload['permissions'] = $validated['permissions'] ?? [];
        }

        return $payload;
    }

    private function getRolePermissions(Role $role): array
    {
        if (!Schema::hasColumn('roles', 'permissions')) {
            return [];
        }

        $permissions = $role->permissions;

        if (is_array($permissions)) {
            return $permissions;
        }

        return [];
    }
}
