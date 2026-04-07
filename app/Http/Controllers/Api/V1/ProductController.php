<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $products = Product::query();

        if ($request->filled('search')) {
            $search   = $request->input('search');
            $products = $products->where('name', 'LIKE', "%$search%")
                                 ->orWhere('brand', 'LIKE', "%$search%")
                                 ->orWhere('model_number', 'LIKE', "%$search%");
        }

        if ($request->filled('status')) {
            $products = $products->where('status', $request->input('status'));
        }

        $perPage  = $request->input('per_page', 10);
        $products = $products->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Store a newly created product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand'        => 'required|string|max:255',
            'model_number' => 'required|string|max:255|unique:products,model_number',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:0,1',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data'    => $product,
        ], 201);
    }

    /**
     * Display the specified product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (empty($product)) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }

    /**
     * Update the specified product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (empty($product)) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand'        => 'required|string|max:255',
            'model_number' => 'required|string|max:255|unique:products,model_number,' . $id,
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:0,1',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => $product,
        ]);
    }

    /**
     * Remove the specified product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (empty($product)) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
