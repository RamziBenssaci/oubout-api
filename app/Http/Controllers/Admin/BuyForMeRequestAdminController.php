<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\BuyForMeRequest;
use App\Http\Controllers\Controller;

class BuyForMeRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = BuyForMeRequest::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%$search%");
                  });
            });
        }

        $limit = $request->get('limit', 20);

        $requests = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Buy for me requests retrieved successfully',
            'data' => $requests->items(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'total_pages' => $requests->lastPage(),
                'total_items' => $requests->total(),
                'per_page' => $requests->perPage(),
            ]
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|',
            'actual_price' => 'nullable|numeric|min:0',
            'tracking_number' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $buyRequest = BuyForMeRequest::find($id);

        if (!$buyRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Buy for me request not found'
            ], 404);
        }

        // Optional: Prevent invalid transitions
        if ($buyRequest->status === 'delivered' || $buyRequest->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition',
                'errors' => [
                    'status' => ['Cannot change from delivered or cancelled']
                ]
            ], 400);
        }

        $buyRequest->update([
            'status' => $request->status,
            'actual_price' => $request->actual_price ?? $buyRequest->actual_price,
            'tracking_number' => $request->tracking_number,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buy for me request updated successfully',
            'data' => $buyRequest->load('user'),
        ]);
    }

    public function stats()
    {
        $total = BuyForMeRequest::count();
        $pending = BuyForMeRequest::where('status', 'pending')->count();
        $processing = BuyForMeRequest::where('status', 'processing')->count();
        $purchased = BuyForMeRequest::where('status', 'purchased')->count();
        $shipped = BuyForMeRequest::where('status', 'shipped')->count();
        $delivered = BuyForMeRequest::where('status', 'delivered')->count();
        $cancelled = BuyForMeRequest::where('status', 'cancelled')->count();
        $revenue = BuyForMeRequest::whereNotNull('actual_price')->sum('actual_price');
        $average = BuyForMeRequest::whereNotNull('actual_price')->avg('actual_price');

        $topProducts = BuyForMeRequest::selectRaw('product_name, COUNT(*) as count, SUM(actual_price) as total_value')
            ->groupBy('product_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Statistics retrieved successfully',
            'data' => [
                'total_requests' => $total,
                'pending_requests' => $pending,
                'processing_requests' => $processing,
                'completed_requests' => $delivered,
                'cancelled_requests' => $cancelled,
                'total_revenue' => round($revenue, 2),
                'average_order_value' => round($average, 2),
                'top_products' => $topProducts,
            ]
        ]);
    }
}
