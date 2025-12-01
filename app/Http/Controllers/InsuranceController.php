<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InsuranceController extends Controller
{public function insure(Request $request, $id)
{
    $package = Package::find($id);

    if (!$package) {
        return response()->json([
            'success' => false,
            'message' => 'Package not found'
        ], 404);
    }

    if ($package->insurance_value !== null) {
        return response()->json([
            'success' => false,
            'message' => 'Package is already insured'
        ], 400);
    }

    $request->validate([
        'insurance_value' => 'required|numeric|min:0.01'
    ]);
$package->insurance = true;
    $package->insurance_value = $request->input('insurance_value');
    $package->save();

    return response()->json([
        'success' => true,
        'message' => 'Package insured successfully',
        'data' => $package
    ]);
}

}
