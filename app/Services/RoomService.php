<?php

namespace App\Services;
use App\Models\Room;
use App\Models\RoomDefinition;      
class RoomService
{
    public function getAll(){
        return Room::with('definition.category','definition.type')
            ->orderBy('floor','asc')
            ->orderBy('room_number','asc')
            ->get();
    }
    public function create(array $data){
        return Room::create($data);
    }
    public function update($id, array $data){
        $room = Room::findOrFail($id);
        $room->update($data);
        return $room;
    }
    public function delete($id){
        return Room::destroy($id);
    }
    public function changeStatus($id, $status){
        $room = Room::findOrFail($id);
        $room->update(['status' => $status]);
        return $room;
    }
}