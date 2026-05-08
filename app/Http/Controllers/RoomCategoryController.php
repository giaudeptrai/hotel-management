<?php

namespace App\Http\Controllers;

use App\Services\RoomCategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomCategoryController extends Controller
{
    protected $roomCategoryService;

    public function __construct(RoomCategoryService $roomCategoryService)
    {
        $this->roomCategoryService = $roomCategoryService;
    }

    public function index(Request $request)
    {
        
        $categories = $this->roomCategoryService->getAll($request->only(['search']));
        return Inertia::render('Admin/RoomCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/RoomCategories/Create');
    }

    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_categories,name',
            'description' => 'nullable|string',
        ]);

        $this->roomCategoryService->create($validated);

        return redirect()->route('admin.room-categories.index')
            ->with('success', 'Đã thêm hạng phòng mới thành công!');
    }

    public function edit($id)
    {
        $category = $this->roomCategoryService->findById($id);
        return Inertia::render('Admin/RoomCategories/Edit', [
            'category' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            
            'name' => 'required|string|max:255|unique:room_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $this->roomCategoryService->update($id, $validated);

        return redirect()->route('admin.room-categories.index')
            ->with('success', 'Đã cập nhật hạng phòng thành công!');
    }

    public function destroy($id)
    {
        $this->roomCategoryService->delete($id);
        return redirect()->route('admin.room-categories.index')
            ->with('success', 'Đã xóa hạng phòng thành công!');
    }
}
