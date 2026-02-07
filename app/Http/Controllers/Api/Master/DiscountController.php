<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    // GET /api/master/discounts
    public function index(Request $request)
    {
        $discounts = $this->discountService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List discounts',
            'data'    => $discounts,
        ]);
    }

    // POST /api/master/discounts
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'discount_type'   => 'required|in:PERCENT,FIXED',
            'discount_value'  => 'required|numeric|min:0',
            'store_id'        => 'nullable|exists:stores,id',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_active'       => 'boolean',
        ]);

        $discount = $this->discountService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'data'    => $discount,
        ], 201);
    }

    // GET /api/master/discounts/{id}
    public function show($id)
    {
        $discount = $this->discountService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Discount detail',
            'data'    => $discount,
        ]);
    }

    // PUT /api/master/discounts/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'discount_type'   => 'required|in:PERCENT,FIXED',
            'discount_value'  => 'required|numeric|min:0',
            'store_id'        => 'nullable|exists:stores,id',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_active'       => 'boolean',
        ]);

        $discount = $this->discountService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Discount updated successfully',
            'data'    => $discount,
        ]);
    }

    // DELETE /api/master/discounts/{id}
    public function destroy($id)
    {
        $this->discountService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully',
        ]);
    }

    // PUT /api/master/discounts/{id}/restore
    public function restore($id)
    {
        $discount = $this->discountService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Discount restored successfully',
            'data'    => $discount,
        ]);
    }
}
