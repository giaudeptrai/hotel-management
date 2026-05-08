<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        $user = $this->user();
        $customerId = optional($user?->customer)->id;
        $isCustomerProfile = $user && ($user->role === 'customer' || $customerId);

        if ($isCustomerProfile) {
            $rules['phone'] = [
                'required',
                'string',
                'max:20',
                Rule::unique(Customer::class, 'phone')->ignore($customerId),
            ];
            $rules['cccd_number'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique(Customer::class, 'cccd_number')->ignore($customerId),
            ];
            $rules['birthday'] = ['nullable', 'date'];
            $rules['gender'] = ['nullable', Rule::in(['male', 'female', 'other'])];
            $rules['address'] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }
}
