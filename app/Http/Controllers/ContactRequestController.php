<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactRequestController extends Controller
{
    private const STATUSES = ['new', 'in_progress', 'resolved', 'closed'];

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $search = trim((string) ($filters['search'] ?? ''));
        $status = (string) ($filters['status'] ?? '');

        $contactRequestsQuery = ContactRequest::query()
            ->with(['user:id,name,email'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('subject', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%');
                });
            })
            ->when(in_array($status, self::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->latest();

        return Inertia::render('Admin/ContactRequests/Index', [
            'contactRequests' => $contactRequestsQuery->paginate(15)->withQueryString(),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'stats' => [
                'total' => ContactRequest::count(),
                'new' => ContactRequest::where('status', 'new')->count(),
                'in_progress' => ContactRequest::where('status', 'in_progress')->count(),
                'resolved' => ContactRequest::where('status', 'resolved')->count(),
                'closed' => ContactRequest::where('status', 'closed')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
        ]);

        $contactRequest->status = $validated['status'];
        $contactRequest->handled_at = in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null;
        $contactRequest->save();

        return back()->with('success', 'Đã cập nhật trạng thái yêu cầu hỗ trợ.');
    }
}
