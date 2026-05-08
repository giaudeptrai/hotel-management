<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\RoomDefinition;
use App\Models\RoomType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $featuredRooms = RoomDefinition::query()
            ->with(['type', 'category'])
            ->withCount('rooms')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('base_price')
            ->take(6)
            ->get();

        $stats = [
            'room_definitions' => RoomDefinition::count(),
            'room_categories' => RoomCategory::count(),
            'room_types' => RoomType::count(),
            'active_rooms' => Room::query()->where('is_active', true)->count(),
        ];

        $roomTypes = RoomType::query()
            ->withCount('roomDefinitions')
            ->orderBy('name')
            ->get();

        $categories = RoomCategory::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'featuredRooms' => $featuredRooms,
            'stats' => $stats,
            'roomTypes' => $roomTypes,
            'categories' => $categories,
        ]);
    }
}
