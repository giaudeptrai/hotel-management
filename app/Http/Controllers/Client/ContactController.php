<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $customer = $user?->customer;

        return Inertia::render('Client/Contact/Index', [
            'contact' => [
                'phone' => '0792008096',
                'email' => 'booking@dasherhotel.vn',
                'address' => '123 Nguyễn Văn Cừ, Long Xuyên, An Giang',
                'hotline_display' => '0792 008 096',
            ],
            'prefill' => [
                'name' => $customer?->full_name ?? $user?->name ?? '',
                'phone' => $customer?->phone ?? '',
                'email' => $customer?->email ?? $user?->email ?? '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:15', 'max:2000'],
        ]);

        $subject = trim((string) ($validated['subject'] ?? ''));

        ContactRequest::create([
            ...$validated,
            'subject' => $subject !== '' ? $subject : 'Yêu cầu hỗ trợ chung',
            'user_id' => $request->user()?->id,
            'status' => 'new',
            'source' => 'client_contact_page',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return back()->with('success', 'Yêu cầu của bạn đã được gửi thành công. Lễ tân sẽ liên hệ sớm nhất.');
    }
}
