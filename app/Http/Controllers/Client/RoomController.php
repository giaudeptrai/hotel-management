<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\RoomDefinition;
use Illuminate\Http\Request;
use App\Services\Client\ClientRoomService;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function __construct(private readonly ClientRoomService $roomService)
    {
    }

    
    public function index(Request $request)
    {
        $filters = $request->only(['check_in', 'check_out', 'guests', 'category_id', 'room_type_id', 'sort']);
        return Inertia::render('Client/Rooms/Index', $this->roomService->getIndexPayload($filters));
    }

    
    public function show(RoomDefinition $room, Request $request)
    {
        $filters = $request->only(['check_in', 'check_out', 'guests']);
        return Inertia::render('Client/Rooms/Show', $this->roomService->getShowPayload($room, $filters));
    }
}
