<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Package;
use App\Models\Discount;
use App\Models\Shipment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SuiteController extends Controller
{
   public function getPackages(Request $request)
{
    $user = auth()->user();

    $query = $user->packages();

  
    return response()->json([
        'success' => true,
        'data' => $query->get()
    ]);
}


    public function getShipments(Request $request)
    {
        $user =auth()->user();

        $query = $user->packages();

         if ($request->has('shipping_method')) {
        $normalizedShipping = Str::slug($request->shipping_method, '-');
        $query->where('shipping_method', $normalizedShipping);
    }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

 public function getReturnedPackages(Request $request)
    {
        $user = auth()->user();

        $query = $user->packages()->where('status', 'returned');

            if ($request->has('shipping_method')) {
        $normalizedShipping = Str::slug($request->shipping_method, '-');
        $query->where('shipping_method', $normalizedShipping);
    }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }
    public function getArchive(Request $request)
    {
        $user = auth()->user();

        $query = $user->packages()->where('status', 'arrived');

            if ($request->has('shipping_method')) {
        $normalizedShipping = Str::slug($request->shipping_method, '-');
        $query->where('shipping_method', $normalizedShipping);
    }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }
public function getAllUserPackages(Request $request)
{
    $user = $request->user();

    $packages = Package::where('user_id', $user->id)
        ->select('id', 'tracking_number', 'description', 'weight', 'status', 'created_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $packages
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
public function requestReturn($packageId, Request $request)
{
    $user = $request->user();
    $package = Package::where('id', $packageId)->where('user_id', $user->id)->first();

    if (!$package) {
        return response()->json([
            'success' => false,
            'message' => 'Package not found'
        ], 404);
    }

    if ($package->status !== 'in_transit') {
        return response()->json([
            'success' => false,
            'message' => "Package is not eligible for return. Only packages with 'in_transit' status can be returned."
        ], 400);
    }

    $package->status = 'returned';
    $package->save();

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $package->id,
            'package_id' => $package->id,
            'status' => $package->status,
            'requested_at' => now()->toIso8601String(),
            'message' => 'Return request submitted successfully'
        ],
        'message' => 'Return request submitted successfully'
    ]);
}

public function requestShipping($packageId, Request $request)
{
    $user = $request->user();
    $package = Package::where('id', $packageId)->where('user_id', $user->id)->first();

    if (!$package) {
        return response()->json([
            'success' => false,
            'message' => 'Package not found'
        ], 404);
    }

    if ($package->status !== 'in_transit') {
        return response()->json([
            'success' => false,
            'message' => "Package is not eligible for shipping request. Only packages with 'in_transit' status can be requested for immediate shipping."
        ], 400);
    }

    $package->status = 'pending';
    $package->save();

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $package->id,
            'package_id' => $package->id,
            'status' => $package->status,
            'requested_at' => now()->toIso8601String(),
            'message' => 'Shipping request submitted successfully'
        ],
        'message' => 'Shipping request submitted successfully'
    ]);
}
}
