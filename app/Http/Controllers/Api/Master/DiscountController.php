<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // GET /api/master/discounts (pagination 10)
    public function index()
    {
        $discounts = Discount::with('store')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List discounts',
            'data' => $discounts,
        ]);
    }

    // POST /api/master/discounts
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:discounts,code',
            'name' => 'required|string|max:100',
            'discount_type' => 'required|in:PERCENT,FIXED',
            'discount_value' => 'required|numeric|min:0',
            'store_id' => 'nullable|exists:stores,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $discount = Discount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'data' => $discount,
        ], 201);
    }

    // GET /api/master/discounts/{id} (preview)
    public function show($id)
    {
        $discount = Discount::with('store')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Discount detail',
            'data' => $discount,
        ]);
    }

    // PUT /api/master/discounts/{id}
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:discounts,code,' . $discount->id,
            'name' => 'required|string|max:100',
            'discount_type' => 'required|in:PERCENT,FIXED',
            'discount_value' => 'required|numeric|min:0',
            'store_id' => 'nullable|exists:stores,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $discount->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Discount updated successfully',
            'data' => $discount,
        ]);
    }

    // DELETE /api/master/discounts/{id}
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully',
        ]);
    }
}
