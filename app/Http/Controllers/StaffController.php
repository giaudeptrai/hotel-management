<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Role;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    protected $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index(Request $request)
    {
        $staffs = $this->staffService->getAll($request->only(['search', 'status', 'role_id']));

        return Inertia::render('Admin/Staff/Index', [
            'staffs' => $staffs->withQueryString(),
            'filters' => $request->only(['search', 'status', 'role_id']),
            'roles' => Role::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Staff/Create', [
            'roles' => Role::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6|confirmed',
            'role_id'   => 'required|exists:roles,id',
            'phone'     => 'nullable|string|max:20',
            'cccd'      => 'nullable|string|max:20',
        ]);

        $this->staffService->createStaff($validated);

        return redirect()->route('admin.staffs.index')->with('success', 'Thêm nhân viên thành công mĩ mãn!');
    }

    public function edit($id)
    {
        $staff = Staff::with('user')->findOrFail($id);

        return Inertia::render('Admin/Staff/Edit', [
            'staff' => $staff,
            'roles' => Role::select('id', 'name')->get()
        ]);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',

            'email'     => 'required|email|unique:users,email,' . $staff->user_id,
            'password'  => 'nullable|min:6|confirmed',
            'role_id'   => 'required|exists:roles,id',
            'phone'     => 'nullable|string|max:20',
            'cccd'      => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        $this->staffService->updateStaff($id, $validated);

        return redirect()->route('admin.staffs.index')->with('success', 'Cập nhật hồ sơ cái rẹt!');
    }

    public function destroy($id)
    {
        $this->staffService->deleteStaff($id);

        return back()->with('success', 'Đã cho nhân viên nghỉ việc thành công!');
    }
}
