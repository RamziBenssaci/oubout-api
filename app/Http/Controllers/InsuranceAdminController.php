<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InsuranceAdminController extends Controller
{
  public function getAllInsuredPackages()
{
    $packages = Package::with('user:id,name')
        ->where('insurance', true)
        ->get()
        ->map(function ($package) {
            return [
                'user_id' => $package->user_id,
                'tracking_number' => $package->tracking_number,
                'description' => $package->description,
                'country' => $package->country,
                'origin_country' => $package->origin_country,
                'destination_country' => $package->destination_country,
                'weight' => $package->weight,
                'status' => $package->status,
                'estimated_delivery' => $package->estimated_delivery,
                'shipping_method' => $package->shipping_method,
                'price' => $package->price,
                'client_name' => optional($package->user)->name,
            ];
        });

    return response()->json([
        'success' => true,
        'message' => 'Insured packages retrieved successfully',
        'data' => $packages,
    ]);
}

    public function getStatistics()
{
    $totalInsured = Package::where('insurance', true)->count();
    $totalInsuranceValue = Package::where('insurance', true)->sum('insurance_value');

    return response()->json([
        'success' => true,
        'data' => [
            'total_insured_packages' => $totalInsured,
            'total_insurance_value' => $totalInsuranceValue
        ]
    ]);
}

}
