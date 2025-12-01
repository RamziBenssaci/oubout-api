<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\BuyForMeRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BuyForMeRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_url' => 'nullable|url',
            'description' => 'required|string|min:10',
            'estimated_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'special_instructions' => 'nullable|string',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validated->errors()
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('buy_for_me_images', 'public');
        }

        $requestModel = BuyForMeRequest::create([
            'user_id' => $request->user()->id,
            'product_name' => $request->product_name,
            'product_url' => $request->product_url,
            'description' => $request->description,
            'estimated_price' => $request->estimated_price,
            'currency' => $request->currency,
            'quantity' => $request->quantity,
            'shipping_address' => $request->shipping_address,
            'special_instructions' => $request->special_instructions,
            'product_image_path' => $imagePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                ...$requestModel->toArray(),
                'product_image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
            ],
            'message' => 'Buy for me request created successfully'
        ]);
    }

    public function index(Request $request)
    {
        $query = BuyForMeRequest::where('user_id', $request->user()->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $limit = $request->get('limit', 10);
        $requests = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'requests' => $requests->items(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'total_pages' => $requests->lastPage(),
                    'total_items' => $requests->total(),
                    'items_per_page' => $requests->perPage(),
                ]
            ]
        ]);
    }

    public function show($id, Request $request)
    {
        $requestModel = BuyForMeRequest::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$requestModel) {
            return response()->json([
                'success' => false,
                'message' => 'Buy for me request not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                ...$requestModel->toArray(),
                'product_image_url' => $requestModel->product_image_path ? asset('storage/' . $requestModel->product_image_path) : null,
            ]
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $validated = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,purchased,shipped,delivered,cancelled',
            'actual_price' => 'nullable|numeric|min:0',
            'tracking_number' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validated->errors()
            ], 422);
        }

        $requestModel = BuyForMeRequest::find($id);

        if (!$requestModel) {
            return response()->json([
                'success' => false,
                'message' => 'Buy for me request not found'
            ], 404);
        }

        $requestModel->update([
            'status' => $request->status,
            'actual_price' => $request->actual_price,
            'tracking_number' => $request->tracking_number,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $requestModel->id,
                'status' => $requestModel->status,
                'actual_price' => $requestModel->actual_price,
                'tracking_number' => $requestModel->tracking_number,
                'admin_notes' => $requestModel->admin_notes,
                'updated_at' => $requestModel->updated_at,
            ],
            'message' => 'Buy for me request status updated successfully'
        ]);
    }
}
