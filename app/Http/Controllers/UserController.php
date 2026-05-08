<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    
    public function index(Request $request)
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => $this->userService->getAll($request->all()),
            'filters' => $request->only(['search', 'role_id', 'status']),
            'roles' => $this->getAssignableRoles(),
        ]);
    }

    
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => $this->getAssignableRoles(),
        ]);
    }

    
    public function store(Request $request)
    {
        $roleSlugs = $this->getAssignableRoleSlugs();

        $validated = $request->validate([
            
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => ['required', Rule::in($roleSlugs)],
            'is_active' => 'required|boolean',

            
            'phone'       => [
                'required_if:role,customer',
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'phone'),
            ],
            'cccd_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'cccd_number'),
            ],
            'address'     => 'nullable|string',
            'gender'      => 'nullable|in:male,female,other',
        ], [
            'phone.required_if' => 'Khách hàng thì phải có số điện thoại ông Thiện ơi!',
            'email.unique'      => 'Email này có người xài rồi nè.',
            'phone.unique'      => 'Số điện thoại này đã được sử dụng.',
            'cccd_number.unique' => 'Số CCCD/CMND này đã được sử dụng.',
        ]);

        $this->userService->create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã tạo tài khoản và hồ sơ thành công! ✨');
    }

    
    public function edit(User $user)
    {
        
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('customer'),
            'roles' => $this->getAssignableRoles(),
        ]);
    }

    
    public function update(Request $request, User $user)
    {
        $customerId = $user->customer?->id;
        $roleSlugs = $this->getAssignableRoleSlugs();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:8|confirmed',
            'role'      => ['required', Rule::in($roleSlugs)],
            'is_active' => 'required|boolean',

            
            'phone'       => [
                'required_if:role,customer',
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
            'cccd_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'cccd_number')->ignore($customerId),
            ],
            'address'     => 'nullable|string',
            'gender'      => 'nullable|in:male,female,other',
        ], [
            'phone.required_if' => 'Khách hàng thì phải có số điện thoại ông Thiện ơi!',
            'email.unique'      => 'Email này có người xài rồi nè.',
            'phone.unique'      => 'Số điện thoại này đã được sử dụng.',
            'cccd_number.unique' => 'Số CCCD/CMND này đã được sử dụng.',
        ]);

        $this->userService->update($user, $validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã cập nhật thông tin tài khoản! 🔄');
    }

    
    public function destroy(User $user)
    {
        try {
            $this->userService->delete($user);
            return redirect()->route('admin.users.index')
                ->with('success', 'Đã xóa tài khoản vĩnh viễn.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function getAssignableRoles()
    {
        return Role::query()
            ->where('is_active', true)
            ->orderByDesc('level')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'level']);
    }

    private function getAssignableRoleSlugs(): array
    {
        $slugs = $this->getAssignableRoles()->pluck('slug')->filter()->values()->all();

        if (empty($slugs)) {
            return ['customer'];
        }

        return $slugs;
    }
}
