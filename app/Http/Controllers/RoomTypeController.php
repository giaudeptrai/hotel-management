<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RoomTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomTypeController extends Controller
{
    protected $roomTypeService;

    public function __construct(RoomTypeService $roomTypeService)
    {
        $this->roomTypeService = $roomTypeService;
    }

    /**
     * Hiển thị danh sách loại phòng
     */
    public function index()
    {
        return Inertia::render('Admin/RoomTypes/Index', [
            'roomTypes' => $this->roomTypeService->getAllForAdmin()
        ]);
    }

    /**
     * Trang tạo mới (Nếu Giàu dùng trang riêng thay vì Modal)
     */
    public function create()
    {
        return Inertia::render('Admin/RoomTypes/Create');
    }

    /**
     * Lưu dữ liệu mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name',
            'capacity_adult' => 'required|integer|min:1',
            'capacity_child' => 'required|integer|min:0',
        ]);

        $this->roomTypeService->createRoomType($validated);

        return redirect()->route('admin.room-types.index')
            ->with('success', 'Đã thêm loại phòng mới thành công!');
    }

    /**
     * Xóa loại phòng
     */
    public function destroy($id)
    {
        $this->roomTypeService->deleteRoomType($id);

        return redirect()->back()
            ->with('success', 'Đã xóa loại phòng thành công!');
    }

    public function edit($id)
    {
        $roomType = $this->roomTypeService->findById($id);
        return Inertia::render('Admin/RoomTypes/Edit', [
            'roomType' => $roomType
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name,' . $id,
            'capacity_adult' => 'required|integer|min:1',
            'capacity_child' => 'required|integer|min:0',
        ]);

        $this->roomTypeService->updateRoomType($id, $validated);

        return redirect()->route('admin.room-types.index')
            ->with('success', 'Đã cập nhật loại phòng thành công!');
    }
}