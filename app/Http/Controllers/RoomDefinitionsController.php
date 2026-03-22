<?php

namespace App\Http\Controllers;

use App\Models\RoomDefinition;
use App\Models\Amenity;
use App\Models\RoomCategory;
use App\Models\RoomType;
use App\Services\RoomDefinitionsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class RoomDefinitionsController extends Controller
{
    protected $service;
    
    public function __construct(RoomDefinitionsService $service) {
        $this->service = $service;
    }

    public function index() {
        $definitions = $this->service->getAll();
        return Inertia::render('Admin/RoomDefinitions/Index', [
            'definitions' => $definitions,
        ]);
    }

    public function create() {
        $categories = RoomCategory::all();
        $roomTypes = RoomType::all();
        $amenities = Amenity::all();
        return Inertia::render('Admin/RoomDefinitions/Create', [
            'categories' => $categories,
            'roomTypes' => $roomTypes,
            'amenities' => $amenities,
        ]);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_category_id' => 'required|exists:room_categories,id',
            'room_type_id' => 'required|exists:room_types,id',
            'base_price' => 'required|numeric|min:0',
            'area' => 'required|integer|min:1',
            'view' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|max:2048', // Mỗi ảnh tối đa 2MB
            'amenity_ids' => 'array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('room_definitions', 'public');
                $imagesPaths[] = Storage::url($path);
            }
        }
        $validated['images'] = $imagesPaths;
        $this->service->create($validated);

        return redirect()->route('admin.room-definitions.index')
            ->with('success', 'Đã thêm định nghĩa phòng mới thành công!');
    }

    public function edit($id) {
        $definition = $this->service->findById($id);
        $categories = RoomCategory::all();
        $roomTypes = RoomType::all();
        $amenities = Amenity::all();
        return Inertia::render('Admin/RoomDefinitions/Edit', [
            'definition' => $definition,
            'categories' => $categories,
            'roomTypes' => $roomTypes,
            'amenities' => $amenities,
        ]);
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_category_id' => 'required|exists:room_categories,id',
            'room_type_id' => 'required|exists:room_types,id',
            'base_price' => 'required|numeric|min:0',
            'area' => 'required|integer|min:1',
            'view' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|max:2048', // Mỗi ảnh tối đa 2MB
            'amenity_ids' => 'array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('room_definitions', 'public');
                $imagesPaths[] = Storage::url($path);
            }
        }
        if (!empty($imagesPaths)) {
            $validated['images'] = $imagesPaths;
        }
        $this->service->update($id, $validated);

        return redirect()->route('admin.room-definitions.index')
            ->with('success', 'Đã cập nhật định nghĩa phòng thành công!');
    }

    public function destroy($id) {
        $this->service->delete($id);
        return redirect()->back()
            ->with('success', 'Đã xóa định nghĩa phòng thành công!');
    }
}
