<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search_text', ''));
        $perPage = (int) ($request->input('showrecord') ?: array_key_first(config('constants.SHOW_RECORD')));

        if (!array_key_exists((string) $perPage, config('constants.SHOW_RECORD', []))) {
            $perPage = (int) array_key_first(config('constants.SHOW_RECORD'));
        }

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('buyer_name', 'like', "%{$search}%")
                        ->orWhere('buyer_email', 'like', "%{$search}%")
                        ->orWhere('buyer_address', 'like', "%{$search}%")
                        ->orWhere('buyer_city', 'like', "%{$search}%")
                        ->orWhere('buyer_state', 'like', "%{$search}%")
                        ->orWhere('buyer_country', 'like', "%{$search}%")
                        ->orWhere('buyer_zipcode', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search', 'perPage'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Customer::create($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        return view('admin.customers.view', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules());

        $customer = Customer::findOrFail($id);
        $customer->update($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'nullable|email|max:255',
            'buyer_address' => 'required|string|max:255',
            'buyer_city' => 'required|string|max:255',
            'buyer_state' => 'required|string|max:255',
            'buyer_country' => 'required|string|max:255',
            'buyer_zipcode' => 'required|string|max:50',
        ];
    }
}
