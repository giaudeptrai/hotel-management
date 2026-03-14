<?php

namespace App\Services;
use App\Models\Room;

class RoomService
{
    public function getRoomMatrix()
    {
        $rooms = Room::with('roomType')
        ->orderBy('floor')
        ->orderBy('room_number')
        ->get();

        return $rooms->groupBy('floor');
    }
    
    // lễ tân cập nhật trạng thái phòng sau khi khách trả phòng
    public function updateRoomStatus($roomId, $status)
    {
        $room = Room::findOrFail($roomId);
        $room->update(['status' => $status]);
        $room->save();
    }

    // app/Services/RoomService.php

    public function getDashboardStats()
    {
        return [
            'availableRooms'        => \App\Models\Room::where('status', 'available')->count(),
            'occupiedRooms'         => \App\Models\Room::where('status', 'occupied')->count(),
            'roomsNeedingCleaning'  => \App\Models\Room::where('status', 'cleaning')->count(),
            'dailyRevenue'          => '0đ', // Tạm thời để 0, sau này làm bảng Booking mình tính sau
        ];
    }

}