<?php

namespace App\Services\Client;

use App\Models\RoomDefinition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ClientRoomAvailabilityService
{
    public function resolveStayRange(?string $checkIn, ?string $checkOut): array
    {
        if (!$checkIn || !$checkOut) {
            return [null, null];
        }

        try {
            $checkInDate = Carbon::parse($checkIn)->startOfDay();
            $checkOutDate = Carbon::parse($checkOut)->startOfDay();

            if ($checkOutDate->lessThanOrEqualTo($checkInDate)) {
                return [null, null];
            }

            return [$checkInDate, $checkOutDate];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }

    public function applyAvailabilityCountsToQuery(Builder $query, ?Carbon $checkInDate, ?Carbon $checkOutDate): Builder
    {
        return $query->withCount([
            'rooms' => fn($roomQuery) => $roomQuery->where('is_active', true),
            'rooms as available_rooms_count' => function ($roomQuery) use ($checkInDate, $checkOutDate) {
                $roomQuery->where('is_active', true);

                if ($checkInDate && $checkOutDate) {
                    $roomQuery->whereDoesntHave('bookingRooms.booking', function ($bookingQuery) use ($checkInDate, $checkOutDate) {
                        $this->applyConflictScope($bookingQuery, $checkInDate, $checkOutDate);
                    });
                }
            },
        ]);
    }

    public function applyAvailabilityCountsToRoom(RoomDefinition $room, ?Carbon $checkInDate, ?Carbon $checkOutDate): void
    {
        $room->loadCount([
            'rooms' => fn($roomQuery) => $roomQuery->where('is_active', true),
            'rooms as available_rooms_count' => function ($roomQuery) use ($checkInDate, $checkOutDate) {
                $roomQuery->where('is_active', true);

                if ($checkInDate && $checkOutDate) {
                    $roomQuery->whereDoesntHave('bookingRooms.booking', function ($bookingQuery) use ($checkInDate, $checkOutDate) {
                        $this->applyConflictScope($bookingQuery, $checkInDate, $checkOutDate);
                    });
                }
            },
        ]);
    }

    public function attachBookableFlag(RoomDefinition $room): void
    {
        $room->setAttribute('is_bookable', (int) ($room->rooms_count ?? 0) > 0 && (int) ($room->available_rooms_count ?? 0) > 0);
    }

    public function applyConflictScope(Builder $bookingQuery, Carbon $checkInDate, Carbon $checkOutDate): void
    {
        $bookingQuery
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('check_in_expected', '<', $checkOutDate)
                    ->where('check_out_expected', '>', $checkInDate);
            });
    }
}
