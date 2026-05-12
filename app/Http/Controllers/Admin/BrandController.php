<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WatchBrand;

class BrandController extends Controller
{
    // List all brands
    public function index()
    {
        $query = WatchBrand::query();
            if (request('name')) {
                $query->where('name', 'like', '%' . request('name') . '%');
            }
            if (request('description')) {
                $query->where('description', 'like', '%' . request('description') . '%');
            }
            $brands = $query->paginate(10);
            return view('admin.brands.index', compact('brands'));
    }

    // Show create form
    public function create()
    {
        return view('admin.brands.create');
    }

    // Store new brand
    public function store(Request $request)
    {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            WatchBrand::create($request->only('name', 'description'));
            return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $brand = WatchBrand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    // Update brand
    public function update(Request $request, $id)
    {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            $brand = WatchBrand::findOrFail($id);
            $brand->update($request->only('name', 'description'));
            return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    // Delete brand
    public function destroy($id)
    {
        $brand = WatchBrand::findOrFail($id);
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }

    // View brand details
    public function show($id)
    {
        $brand = WatchBrand::findOrFail($id);
        return view('admin.brands.view', compact('brand'));
    }
}