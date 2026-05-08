<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomReviewController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        $customer = $this->resolveCustomerFromUser($request);

        if (!$user || !$customer || $booking->customer_id !== $customer->id) {
            abort(403);
        }

        if ($booking->status !== 'checked_out') {
            return back()->withErrors(['review' => 'Bạn chỉ có thể đánh giá sau khi đã hoàn tất lưu trú.']);
        }

        if ($booking->review()->exists()) {
            return back()->withErrors(['review' => 'Đơn này đã có đánh giá trước đó.']);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $roomDefinitionId = $booking->bookingRooms()->value('room_definition_id');

        if (!$roomDefinitionId) {
            return back()->withErrors(['review' => 'Không tìm thấy hạng phòng để đánh giá.']);
        }

        RoomReview::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'room_definition_id' => $roomDefinitionId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Cảm ơn bạn đã gửi đánh giá.');
    }

    private function resolveCustomerFromUser(Request $request): ?Customer
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $directCustomer = $user->customer;
        if ($directCustomer) {
            return $directCustomer;
        }

        if (!$user->email) {
            return null;
        }

        $matchedCustomer = Customer::query()
            ->whereNull('user_id')
            ->where('email', $user->email)
            ->latest()
            ->first();

        if ($matchedCustomer) {
            $matchedCustomer->update(['user_id' => $user->id]);
        }

        return $matchedCustomer;
    }
}
