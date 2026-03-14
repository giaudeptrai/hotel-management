<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RoomTypeService;
use Inertia\Inertia;

class RoomTypeController extends Controller
{
    protected $roomTypeService;

    public function __construct(RoomTypeService $roomTypeService)
    {
        $this->roomTypeService = $roomTypeService;
    }

    public function index()
    {
        $roomTypes = $this->roomTypeService->getActiveRoomTypes();
        return Inertia::render('Admin/RoomTypes/Index', [
            'roomTypes' => $roomTypes
        ]);
    }

    public function show($slug)
    {
        $roomType = $this->roomTypeService->getRoomTypeSlug($slug);
        return Inertia::render('Admin/RoomTypes/Show', [
            'roomType' => $roomType
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/RoomTypes/Create');
    }

    public function store(Request $request){
        $vlaidated = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_night' => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'max_adults' => 'required|integer',
            'max_children' => 'required|integer',
            'bed_type' => 'nullable|string|max:255',
            'view' => 'nullable|string|max:255',
            'area' => 'nullable|integer',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array'
        ]);
        $this->roomTypeService->createRoomType($validated);
        return redirect()->route('admin.room-types.index')->with('success', 'Loại phòng mới đã được tạo thành công!');
    }
}
