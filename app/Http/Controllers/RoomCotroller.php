<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RoomService;
use Inertia\Inertia;

class RoomCotroller extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function matrix()
    {
        $roomMatrix = $this->roomService->getRoomMatrix();
        return Inertia::render('Admin/Rooms/Matrix', [
            'roomMatrix' => $roomMatrix
        ]);
    }

    public function updateStatus(Request $request, $roomId)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,cleaning,maintenance',
        ]);

        $this->roomService->updateRoomStatus($roomId, $request->input('status'));

        return redirect()->back()->with('success', 'Room status updated successfully.');
    }
}
