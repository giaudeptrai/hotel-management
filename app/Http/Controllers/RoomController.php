<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomDefinition;
use Illuminate\Http\Request;
use App\Services\RoomService;
use Inertia\Inertia;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }
    public function index()
    {
        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => $this->roomService->getAll(),
        ]);
    }
    public function create()
    {
        return Inertia::render('Admin/Rooms/Create',[
            'roomDefinitions' => \App\Models\RoomDefinition::with('category','type')->get(),
        ]);
    }
    public function store(Request $request){
        $validated = $request->validate([
            'room_number'=> 'required|unique:rooms,room_number',
            'room_definition_id' => 'required|exists:room_definitions,id',
            'floor' => 'required|integer',
            'status' => 'required|in:available,occupied,cleaning,maintenance',
            'is_active' => 'required|boolean',
        ]);

        $this->roomService->create($validated);
        return redirect()->route('admin.rooms.index')->with('success', 'đã tạo phòng mới');
    }
    public function edit($id){
        $room = \App\Models\Room::with('definition.category','definition.type')->findOrFail($id);
        return Inertia::render('Admin/Rooms/Edit',[
            'room' => $room,
            'roomDefinitions' => \App\Models\RoomDefinition::with('category','type')->get(),
        ]);
    }
    public function update(Request $request, $id){
        $validated = $request->validate([
            'room_number'=> 'required|unique:rooms,room_number,' . $id,
            'room_definition_id' => 'required|exists:room_definitions,id',
            'floor' => 'required|integer',
            'status' => 'required|in:available,occupied,cleaning,maintenance',
            'is_active' => 'required|boolean',
        ]);

        $this->roomService->update($id, $validated);
        return redirect()->route('admin.rooms.index')->with('success', 'đã cập nhật phòng thành công');
    }
    public function destroy($id){
        $this->roomService->delete($id);
        return redirect()->route('admin.rooms.index')->with('success', 'đã xóa phòng thành công');
    }
}
