<?php

namespace App\Http\Controllers\Admin;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DiscountController extends Controller
{
     public function index()
    {
        $discounts = Discount::all()->map(function ($discount) {
            return [
                'id' => $discount->id,
                'code' => $discount->code,
                'discount_type' => $discount->discount_type,
                'discount_value' => $discount->discount_value,
                'description' => $discount->description,
                'min_order_amount' => $discount->min_order_amount,
                'usage_limit' => $discount->usage_limit,
                'used_count' => $discount->used_count,
                'expires_at' => $discount->expires_at,
                'is_active' => $discount->is_active,
                'created_at' => $discount->created_at,
                'updated_at' => $discount->updated_at,
            ];
        });

        return response()->json(['success' => true, 'data' => $discounts]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:discounts,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric',
            'description' => 'nullable|string',
            'min_order_amount' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'expires_at' => 'nullable|date',
        ]);

        $discount = Discount::create([
            ...$data,
            'used_count' => 0,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $discount]);
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $data = $request->validate([
            'code' => 'required|string|unique:discounts,code,' . $discount->id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric',
            'description' => 'nullable|string',
            'min_order_amount' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'expires_at' => 'nullable|date',
        ]);

        $discount->update($data);

        return response()->json(['success' => true, 'data' => $discount]);
    }
    public function destroy($id)
{
    $discount = Discount::find($id);

    if (!$discount) {
        return response()->json([
            'success' => false,
            'message' => 'Discount code not found'
        ], 404);
    }

    $discount->delete();

    return response()->json([
        'success' => true,
        'message' => 'Discount code deleted successfully'
    ]);
}
public function applyDiscount(Request $request)
{
    $request->validate([
        'discount_code' => 'required|string',
        'package_id' => 'required|exists:packages,id',
    ]);

    $user = $request->user();
    $package = Package::where('id', $request->package_id)
        ->where('user_id', $user->id)
        ->first();

    if (!$package) {
        return response()->json([
            'success' => false,
            'message' => 'Package not found or not authorized',
        ], 404);
    }

    $discount = Discount::where('code', $request->discount_code)
        ->where('is_active', true)
        ->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
        })
        ->first();

    if (!$discount || ($discount->usage_limit !== null && $discount->used_count >= $discount->usage_limit)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid discount code or discount has expired',
        ], 400);
    }

    if ($discount->min_order_amount && $package->price < $discount->min_order_amount) {
        return response()->json([
            'success' => false,
            'message' => 'Package price is below minimum amount required for this discount',
        ], 400);
    }

    $originalPrice = $package->price;
    $discountAmount = 0;

    if ($discount->discount_type === 'percentage') {
        $discountAmount = ($originalPrice * $discount->discount_value) / 100;
    } elseif ($discount->discount_type === 'fixed') {
        $discountAmount = $discount->discount_value;
    }

    $finalPrice = max(0, $originalPrice - $discountAmount);

    // Update the package
    $package->price = $finalPrice;
    $package->discount_applied = $discount->code;
    $package->save();

    // Update discount usage count
    $discount->increment('used_count');

    return response()->json([
        'success' => true,
        'data' => [
            'discount_applied' => true,
            'original_price' => (float) $originalPrice,
            'discount_amount' => (float) $discountAmount,
            'final_price' => (float) $finalPrice,
            'discount_code' => $discount->code,
            'discount_type' => $discount->discount_type,
        ],
        'message' => 'Discount code applied successfully'
    ]);
}
}
