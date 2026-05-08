<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $customers = $this->customerService->getAll($request->all());

        $customers->getCollection()->transform(function ($customer) {
            return [
                'id'             => $customer->id,
                'full_name'      => $customer->display_name,
                'phone'          => $customer->phone ?? 'Chưa cập nhật',
                'cccd_number'    => $customer->cccd_number ?? '---',
                'email'          => $customer->display_email,
                'total_bookings' => (int) ($customer->bookings_count ?? 0),
                'total_spent'    => number_format($customer->total_spent, 0, ',', '.') . ' ₫',
                'type'           => $customer->user_id ? 'Online' : 'Tại quầy',
                'gender'         => $this->formatGender($customer->gender),
            ];
        });

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters'   => $request->only(['search', 'type', 'gender'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'   => 'required|string|max:255',
            'phone'       => 'required|string|max:20|unique:customers,phone',
            'cccd_number' => 'nullable|string|max:20|unique:customers,cccd_number',
            'email'       => 'nullable|email|max:255',
            'birthday'    => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'address'     => 'nullable|string',
        ]);

        $this->customerService->createWalkIn($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Đã thêm hồ sơ khách hàng tại quầy thành công!');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function history(Request $request, Customer $customer)
    {
        $customer->loadCount('bookings');
        $bookings = $this->customerService->getBookingHistory($customer, $request->only(['search']));

        return Inertia::render('Admin/Customers/BookingHistory', [
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->display_name,
                'phone' => $customer->phone,
                'email' => $customer->display_email,
            ],
            'bookings' => $bookings,
            'stats' => [
                'total_visits' => (int) ($customer->bookings_count ?? 0),
                'total_spent' => (float) ($customer->total_spent ?? 0),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'full_name'   => 'required|string|max:255',
            'phone'       => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'cccd_number' => 'nullable|string|max:20|unique:customers,cccd_number,' . $customer->id,
            'email'       => 'nullable|email|max:255',
            'birthday'    => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'address'     => 'nullable|string',
        ]);

        
        $this->customerService->update($customer, $validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Đồng bộ thông tin khách hàng và tài khoản thành công!');
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);
        return redirect()->route('admin.customers.index')
            ->with('success', 'Đã xóa hồ sơ khách hàng.');
    }

    private function formatGender($gender) {
        $map = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];
        return $map[$gender] ?? '---';
    }
}
