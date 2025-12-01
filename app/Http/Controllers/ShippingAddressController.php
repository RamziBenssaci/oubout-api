<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
class ShippingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ShippingAddress::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100|unique:shipping_addresses',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = ShippingAddress::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Shipping address created successfully'
        ]);
    }

    public function show($id)
    {
        $address = ShippingAddress::find($id);
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping address not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    public function update(Request $request, $id)
    {
        $address = ShippingAddress::find($id);
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping address not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:100|unique:shipping_addresses,title,' . $id,
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $address->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $address,
            'message' => 'Shipping address updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $address = ShippingAddress::find($id);
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping address not found'
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping address deleted successfully'
        ]);
    }
}
