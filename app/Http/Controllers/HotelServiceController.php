<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\HotelServiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HotelServiceController extends Controller
{
    protected $hotelService;

    public function __construct(HotelServiceService $hotelService)
    {
        $this->hotelService = $hotelService;
    }

    
    public function index(Request $request)
    {
        return Inertia::render('Admin/Services/Index', [
            'services' => $this->hotelService->getAll($request->all()),
            'filters'  => $request->only(['search', 'type', 'status'])
        ]);
    }

    
    public function create()
    {
        return Inertia::render('Admin/Services/Create');
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|string|max:50', 
            'price'     => 'required|numeric|min:0',
            'unit'      => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $this->hotelService->create($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Đã thêm dịch vụ mới vào Menu!');
    }

    
    public function edit(Service $service)
    {
        return Inertia::render('Admin/Services/Edit', [
            'service' => $service
        ]);
    }

    
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|string|max:50', 
            'price'     => 'required|numeric|min:0',
            'unit'      => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $this->hotelService->update($service, $validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Cập nhật bảng giá thành công!');
    }

    
    public function destroy(Service $service)
    {
        $deleted = $this->hotelService->delete($service);

        if (!$deleted) {
            return back()->with('warning', 'Dịch vụ này đã có trong đơn đặt. Hệ thống đã tạm ngừng kinh doanh món này để bảo vệ lịch sử.');
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Đã xóa dịch vụ khỏi menu!');
    }
}
