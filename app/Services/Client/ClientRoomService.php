<?php

namespace App\Services\Client;

use App\Models\RoomCategory;
use App\Models\RoomDefinition;
use App\Models\RoomType;

class ClientRoomService
{
    public function __construct(private readonly ClientRoomAvailabilityService $availabilityService)
    {
    }

    public function getIndexPayload(array $filters): array
    {
        [$checkInDate, $checkOutDate] = $this->availabilityService->resolveStayRange($filters['check_in'] ?? null, $filters['check_out'] ?? null);
        $guests = (int) ($filters['guests'] ?? 1);

        $roomsQuery = RoomDefinition::query()->with(['type', 'category'])->withAvg('reviews', 'rating')->withCount('reviews');
        $this->availabilityService->applyAvailabilityCountsToQuery($roomsQuery, $checkInDate, $checkOutDate);

        if (!empty($filters['category_id'])) {
            $roomsQuery->where('room_category_id', $filters['category_id']);
        }

        if (!empty($filters['room_type_id'])) {
            $roomsQuery->where('room_type_id', $filters['room_type_id']);
        }

        if ($guests > 0) {
            $roomsQuery->whereHas('type', function ($typeQuery) use ($guests) {
                $typeQuery->whereRaw('(capacity_adult + capacity_child) >= ?', [$guests]);
            });
        }

        if (($filters['sort'] ?? '') === 'price_desc') {
            $roomsQuery->orderByDesc('base_price');
        } else {
            $roomsQuery->orderBy('base_price');
        }

        $rooms = $roomsQuery->get();
        $rooms->each(fn($room) => $this->availabilityService->attachBookableFlag($room));
        $rooms->each(fn($room) => $room->setAttribute('rating_summary', [
            'average' => round((float) ($room->reviews_avg_rating ?? 0), 1),
            'count' => (int) ($room->reviews_count ?? 0),
        ]));

        $roomTypes = RoomType::query()->orderBy('name')->get();
        $categories = RoomCategory::query()->orderBy('name')->get();

        $meta = [
            'total_rooms' => $rooms->sum('rooms_count'),
            'total_definitions' => $rooms->count(),
            'lowest_price' => (float) ($rooms->min('base_price') ?? 0),
            'highest_price' => (float) ($rooms->max('base_price') ?? 0),
        ];

        return [
            'rooms' => $rooms,
            'filters' => $filters,
            'roomTypes' => $roomTypes,
            'categories' => $categories,
            'meta' => $meta,
        ];
    }

    public function getShowPayload(RoomDefinition $room, array $filters): array
    {
        [$checkInDate, $checkOutDate] = $this->availabilityService->resolveStayRange($filters['check_in'] ?? null, $filters['check_out'] ?? null);

        $room->load(['type', 'category', 'amenities']);
        $this->availabilityService->applyAvailabilityCountsToRoom($room, $checkInDate, $checkOutDate);
        $this->availabilityService->attachBookableFlag($room);

        $room->loadCount('reviews');
        $room->loadAvg('reviews', 'rating');

        $recentReviews = $room->reviews()
            ->with(['customer.user'])
            ->latest()
            ->paginate(6, ['*'], 'review_page')
            ->withQueryString();

        $recentReviews->getCollection()->transform(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'customer_name' => $review->customer?->display_name ?? 'Khách hàng',
                    'created_at' => optional($review->created_at)?->toDateString(),
                ];
            });

        $relatedRoomsQuery = RoomDefinition::query()
            ->with(['type', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('id', '!=', $room->id)
            ->when($room->room_category_id, function ($query) use ($room) {
                $query->where('room_category_id', $room->room_category_id);
            })
            ->orderBy('base_price')
            ->take(3);

        $this->availabilityService->applyAvailabilityCountsToQuery($relatedRoomsQuery, $checkInDate, $checkOutDate);

        $relatedRooms = $relatedRoomsQuery->get();
        $relatedRooms->each(fn($item) => $this->availabilityService->attachBookableFlag($item));
        $relatedRooms->each(function ($item) {
            $item->setAttribute('rating_summary', [
                'average' => round((float) ($item->reviews_avg_rating ?? 0), 1),
                'count' => (int) ($item->reviews_count ?? 0),
            ]);
        });

        return [
            'room' => $room,
            'filters' => $filters,
            'relatedRooms' => $relatedRooms,
            'reviewSummary' => [
                'average' => round((float) ($room->reviews_avg_rating ?? 0), 1),
                'count' => (int) ($room->reviews_count ?? 0),
            ],
            'recentReviews' => $recentReviews,
        ];
    }
}
