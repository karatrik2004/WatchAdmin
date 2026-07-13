<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $request = request();
        $query = Vendor::query();

        $search = (object) null;

        if ($request->filled('search_text')) {
            $q = $request->input('search_text');
            $query->where(function($w) use ($q) {
                $w->where('first_name', 'like', "%$q%")
                  ->orWhere('last_name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('company_name', 'like', "%$q%");
            });
            $search->search_text = $q;
        }

        if ($request->filled('vendor_type')) {
            $query->where('vendor_type', $request->input('vendor_type'));
            $search->vendor_type = $request->input('vendor_type');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'), 'and');
            $search->start_date = $request->input('start_date');
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'), 'and');
            $search->end_date = $request->input('end_date');
        }

        $perPage = $request->input('showrecord') ?: array_key_first(config('constants.SHOW_RECORD'));

        // CSV export handling
        if ($request->input('export') === 'csv') {
            $fileName = 'vendors_' . date('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $columns = ['ID', 'Vendor Type', 'Company Name', 'First Name', 'Last Name', 'Email', 'Contact Number', 'ABN', 'Address', 'City', 'State', 'Country', 'Zipcode', 'Director Name', 'Created At'];

            $callback = function () use ($query, $columns) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);

                $query->orderBy('created_at', 'desc')->chunk(200, function ($vendors) use ($handle) {
                    foreach ($vendors as $vendor) {
                        fputcsv($handle, [
                            $vendor->id,
                            $vendor->vendor_type,
                            $vendor->company_name,
                            $vendor->first_name,
                            $vendor->last_name,
                            $vendor->email,
                            $vendor->contact_number,
                            $vendor->abn,
                            $vendor->address,
                            $vendor->city,
                            $vendor->state,
                            $vendor->country,
                            $vendor->zipcode,
                            $vendor->director_name,
                            optional($vendor->created_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        $vendors = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('admin.vendors.index', compact('vendors', 'search'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {

        $rules = [
            'vendor_type' => 'required|in:individual,company',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string|max:2000',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zipcode' => 'required|string|max:50',
        ];

        if ($request->input('vendor_type') === 'individual') {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['company_name'] = 'nullable|string|max:255';
            $rules['abn'] = 'nullable|string|max:255';
            $rules['dealer_licence_number'] = 'nullable|string|max:255';
            $rules['director_name'] = 'nullable|string|max:255';
        } else {
            $rules['company_name'] = 'required|string|max:255';
            $rules['abn'] = 'required|string|max:255';
            $rules['dealer_licence_number'] = 'required|string|max:255';
            $rules['director_name'] = 'required|string|max:255';
            $rules['first_name'] = 'nullable|string|max:255';
            $rules['last_name'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $data = [
            'vendor_type' => $validated['vendor_type'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'country' => $validated['country'],
            'zipcode' => $validated['zipcode'],
            'abn' => $validated['abn'] ?? null,
            'dealer_licence_number' => $validated['dealer_licence_number'] ?? null,
            'director_name' => $validated['director_name'] ?? null,
        ];

        if ($validated['vendor_type'] === 'individual') {
            $data['company_name'] = null;
            $data['abn'] = null;
            $data['dealer_licence_number'] = null;
            $data['director_name'] = null;
        } else {
            $data['first_name'] = null;
            $data['last_name'] = null;
        }

        Vendor::create($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);

        return view('admin.vendors.edit', compact('vendor'));
    }

    public function show($id)
    {
        $vendor = Vendor::findOrFail($id);

        return view('admin.vendors.view', compact('vendor'));
    }

    public function update(Request $request, $id)
    {

        $rules = [
            'vendor_type' => 'required|in:individual,company',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string|max:2000',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zipcode' => 'required|string|max:50',
        ];

        if ($request->input('vendor_type') === 'individual') {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['company_name'] = 'nullable|string|max:255';
            $rules['abn'] = 'nullable|string|max:255';
            $rules['dealer_licence_number'] = 'nullable|string|max:255';
            $rules['director_name'] = 'nullable|string|max:255';
        } else {
            $rules['company_name'] = 'required|string|max:255';
            $rules['abn'] = 'required|string|max:255';
            $rules['dealer_licence_number'] = 'required|string|max:255';
            $rules['director_name'] = 'required|string|max:255';
            $rules['first_name'] = 'nullable|string|max:255';
            $rules['last_name'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $vendor = Vendor::findOrFail($id);

        $data = [
            'vendor_type' => $validated['vendor_type'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'country' => $validated['country'],
            'zipcode' => $validated['zipcode'],
            'abn' => $validated['abn'] ?? null,
            'dealer_licence_number' => $validated['dealer_licence_number'] ?? null,
            'director_name' => $validated['director_name'] ?? null,
        ];

        if ($validated['vendor_type'] === 'individual') {
            $data['company_name'] = null;
            $data['abn'] = null;
            $data['dealer_licence_number'] = null;
            $data['director_name'] = null;
        } else {
            $data['first_name'] = null;
            $data['last_name'] = null;
        }

        $vendor->update($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}