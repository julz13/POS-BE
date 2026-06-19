<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    public function index(Request $request)
    {
        $customers = Customer::where('status', 'active')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'phone' => 'required|unique:customers',
            'email' => 'nullable|email|unique:customers',
            'address' => 'nullable|string',
            'customer_type' => 'in:regular,vip,credit,wholesale',
            'credit_limit' => 'numeric|min:0',
        ]);

        // Generate customer code
        $lastCode = Customer::latest('id')->first();
        $nextId = ($lastCode ? (int)substr($lastCode->code, -4) + 1 : 1);
        $validated['code'] = 'CUST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create($validated);

        return $this->successResponse($customer, 'Customer created successfully', 201);
    }

    public function show(Customer $customer)
    {
        return $this->successResponse($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'max:100',
            'last_name' => 'max:100',
            'phone' => 'unique:customers,phone,' . $customer->id,
            'email' => 'email|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
            'customer_type' => 'in:regular,vip,credit,wholesale',
            'status' => 'in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return $this->successResponse($customer, 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return $this->successResponse(null, 'Customer deleted successfully');
    }

    public function search($term)
    {
        $customers = Customer::where('status', 'active')
            ->where(function ($query) use ($term) {
                $query->where('first_name', 'like', "%$term%")
                    ->orWhere('last_name', 'like', "%$term%")
                    ->orWhere('phone', 'like', "%$term%")
                    ->orWhere('code', 'like', "%$term%");
            })
            ->limit(10)
            ->get();

        return $this->successResponse($customers);
    }

    public function updateCredit(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'credit_limit' => 'numeric|min:0',
            'credit_amount' => 'numeric',
        ]);

        if (isset($validated['credit_limit'])) {
            $customer->credit_limit = $validated['credit_limit'];
        }

        if (isset($validated['credit_amount'])) {
            $customer->current_balance += $validated['credit_amount'];
        }

        $customer->save();

        return $this->successResponse($customer, 'Customer credit updated successfully');
    }
}
