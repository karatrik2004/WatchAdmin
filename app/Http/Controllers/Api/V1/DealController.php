<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Deal;

class DealController extends Controller
{
    /**
     * Display a listing of the deals.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $deals = Deal::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $deals  = $deals->where('model_number', 'LIKE', "%$search%");
        }

        if ($request->filled('deal_status')) {
            $deals = $deals->where('deal_status', $request->input('deal_status'));
        }

        $perPage = $request->input('per_page', 10);
        $deals   = $deals->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $deals,
        ]);
    }

    /**
     * Store a newly created deal.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_number'   => 'required|string|max:255',
            'serial_number'  => 'required|string|max:255|unique:deals,serial_number',
            'material_watch' => 'required|string|max:255',
            'condition'      => 'required|string',
            'year'           => 'required|integer|min:1900|max:' . date('Y'),
            'full_set'       => 'required',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'country_id'     => 'nullable|integer',
            'state_id'       => 'nullable|integer',
            'city_id'        => 'nullable|integer',
            'address'        => 'nullable|string|max:255',
            'zipcode'        => 'nullable|string',
            'deal_status'    => 'nullable|integer|in:1,2,3,4',
            'status'         => 'nullable|in:0,1',
        ]);

        $deal = Deal::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
            'data'    => $deal,
        ], 201);
    }

    /**
     * Display the specified deal.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $deal = Deal::find($id);

        if (empty($deal)) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $deal,
        ]);
    }

    /**
     * Update the specified deal.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $deal = Deal::find($id);

        if (empty($deal)) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found',
            ], 404);
        }

        $validated = $request->validate([
            'model_number'   => 'required|string|max:255',
            'serial_number'  => 'required|string|max:255|unique:deals,serial_number,' . $id,
            'material_watch' => 'required|string|max:255',
            'condition'      => 'required|string',
            'year'           => 'required|integer|min:1900|max:' . date('Y'),
            'full_set'       => 'required',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'country_id'     => 'nullable|integer',
            'state_id'       => 'nullable|integer',
            'city_id'        => 'nullable|integer',
            'address'        => 'nullable|string|max:255',
            'zipcode'        => 'nullable|string',
            'deal_status'    => 'nullable|integer|in:1,2,3,4',
            'status'         => 'nullable|in:0,1',
        ]);

        $deal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
            'data'    => $deal,
        ]);
    }

    /**
     * Remove the specified deal.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $deal = Deal::find($id);

        if (empty($deal)) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found',
            ], 404);
        }

        $deal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully',
        ]);
    }
}
