<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'sort']);

        $servicesQuery = Service::query()
            ->where('is_active', true)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            });

        match ($filters['sort'] ?? 'featured') {
            'price_asc' => $servicesQuery->orderBy('price'),
            'price_desc' => $servicesQuery->orderByDesc('price'),
            'name_asc' => $servicesQuery->orderBy('name'),
            default => $servicesQuery->latest(),
        };

        $summary = [
            'total' => Service::query()->where('is_active', true)->count(),
            'avg_price' => (int) round((float) Service::query()->where('is_active', true)->avg('price')),
            'types' => Service::query()->where('is_active', true)->distinct('type')->count('type'),
        ];

        return Inertia::render('Client/Services/Index', [
            'services' => $servicesQuery->paginate(12)->withQueryString(),
            'types' => Service::query()
                ->where('is_active', true)
                ->whereNotNull('type')
                ->select('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->values(),
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }
}
