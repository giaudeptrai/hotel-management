<?php

namespace App\Http\Controllers;

use App\Services\AmenitiesService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AmenitiesController extends Controller
{
    protected $amenitiesService;

    public function __construct(AmenitiesService $amenitiesService)
    {
        $this->amenitiesService = $amenitiesService;
    }

    public function index()
    {
        return Inertia::render('Admin/Amenities/Index', [
            'amenities' => $this->amenitiesService->getAll()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Amenities/Create');
    }

    public function store(Request $request)
    {
        // 1. Chỉ validate Tên và File tệp, bỏ hoàn toàn icon_link
        $validated = $request->validate([
            'name' => 'required|unique:amenities,name',
            'icon_file' => 'required|image|max:2048', // Bắt buộc chọn ảnh khi tạo mới
        ]);

        $data = ['name' => $validated['name']];

        // 2. Xử lý upload file vào Docker storage
        if ($request->hasFile('icon_file')) {
            $path = $request->file('icon_file')->store('amenities', 'public');
            $data['icon'] = Storage::url($path); 
        }

        $this->amenitiesService->create($data);

        return redirect()->route('admin.amenities.index')
            ->with('success', 'Thêm tiện ích thành công!');
    }

    public function edit($id)
    {
        return Inertia::render('Admin/Amenities/Edit', [
            'amenity' => $this->amenitiesService->findById($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        // 3. Update thì icon_file là nullable (nếu không chọn thì giữ ảnh cũ)
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities,name,' . $id,
            'icon_file' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $validated['name']];

        if ($request->hasFile('icon_file')) {
            $path = $request->file('icon_file')->store('amenities', 'public');
            $data['icon'] = Storage::url($path);
        }

        $this->amenitiesService->update($id, $data);

        return redirect()->route('admin.amenities.index')
            ->with('success', 'Cập nhật tiện ích thành công!');
    }

    public function destroy($id)
    {
        $this->amenitiesService->delete($id);
        
        return redirect()->route('admin.amenities.index')
            ->with('success', 'Đã xóa và dọn dẹp bộ nhớ thành công!');
    }
}