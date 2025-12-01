<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

use App\Http\Controllers\SuiteController;
use App\Http\Controllers\AdminChatController;

use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\User\ProfileController;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\InsuranceAdminController;

use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\Api\BuyForMeRequestController;

use App\Http\Controllers\Admin\BuyForMeRequestAdminController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;



Route::middleware('api.token')->group(function () {
    Route::get('/chat/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
});

// Admin
Route::middleware(['api.token'])->group(function () {
    Route::get('/admin/chat/users', [AdminChatController::class, 'getUsers']);
    Route::get('/admin/chat/messages', [AdminChatController::class, 'getMessages']);
    Route::post('/admin/chat/messages', [AdminChatController::class, 'sendMessage']);
});





// CUSTOMER ENDPOINT
// Customer routes
Route::middleware('api.token')->group(function () {
    Route::post('/packages/{id}/insurance', [InsuranceController::class, 'insure']);
    Route::get('/packages/insured', [InsuranceController::class, 'showInsured']);
});

// Admin routes
Route::middleware('api.token')->group(function () {
    Route::get('/admin/insured-packages', [InsuranceAdminController::class, 'getAllInsuredPackages']);
    Route::delete('/admin/packages/{id}/insurance', [InsuranceAdminController::class, 'removeInsurance']);
});



// ADMIN ENDPOINTS
Route::prefix('admin')->middleware(['api.token'])->group(function () {
    Route::put('/insured-packages/{id}/claim', [InsuranceAdminController::class, 'processClaim']);
    Route::get('/insurance/statistics', [InsuranceAdminController::class, 'getStatistics']);
});
// Admin
Route::middleware(['api.token'])->group(function () {
    Route::post('/admin/notifications', [NotificationController::class, 'sendToClient']);
});

// Authenticated User
Route::middleware(['api.token'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

Route::middleware(['api.token'])->prefix('admin')->group(function () {
    Route::get('/buy-for-me-requests', [BuyForMeRequestAdminController::class, 'index']);
    Route::get('/buy-for-me-requests/stats', [BuyForMeRequestAdminController::class, 'stats']);
});
 Route::put('/buy-for-me-requests/{id}/status', [BuyForMeRequestAdminController::class, 'updateStatus']);

Route::prefix('admin/auth')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
});
Route::options('/{any}', function (Request $request) {
    return response()->json([], 204);
})->where('any', '.*');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api.token')->group(function () {
    Route::post('/buy-for-me-requests', [BuyForMeRequestController::class, 'store']);
    Route::get('/buy-for-me-requests', [BuyForMeRequestController::class, 'index']);
    Route::get('/buy-for-me-requests/{id}', [BuyForMeRequestController::class, 'show']);
});

Route::middleware(['api.token', 'admin'])->group(function () {
    Route::put('/admin/buy-for-me-requests/{id}/status', [BuyForMeRequestController::class, 'updateStatus']);
});
Route::middleware('api.token')->group(function () {
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user', [ProfileController::class, 'update']);
    Route::put('/user/password', [ProfileController::class, 'changePassword']);
     Route::get('/addresses', [ProfileController::class, 'getAddresses']);
    Route::post('/addresses', [ProfileController::class, 'createAddress']);
    Route::put('/addresses/{id}', [ProfileController::class, 'updateAddress']);
    Route::delete('/addresses/{id}', [ProfileController::class, 'deleteAddress']);
    Route::get('/packages', [SuiteController::class, 'getPackages']);
    Route::get('/shipments', [SuiteController::class, 'getShipments']);
    Route::get('/archive', [SuiteController::class, 'getArchive']);
    Route::get('/archive/returned', [SuiteController::class, 'getReturnedPackages']);
    Route::put('/user/location', [ProfileController::class, 'updateLocation']);
    Route::get('/packages/all', [SuiteController::class, 'getAllUserPackages']);
    Route::post('/discounts/apply', [SuiteController::class, 'applyDiscount']);
    Route::get('/auth/verify', [LoginController::class, 'verifyClient']);

});


Route::middleware('api.token')->post('/auth/logout', [LoginController::class, 'logout']);


Route::middleware(['api.token'])->prefix('admin')->group(function () {
    Route::get('/clients', [AdminController::class, 'getClients']);                      // Get all clients (with optional ?search)
    Route::get('/stats', [AdminController::class, 'getStats']);                          // Admin dashboard stats
    Route::post('/packages', [AdminController::class, 'createClientPackage']);           // Create package for client (from body client_id)
    Route::get('/clients/{client}/packages', [AdminController::class, 'getClientPackages']); // Get client packages
    Route::get('/shipments', [AdminController::class, 'getAllShipments']);               // Get all shipments
    Route::put('/packages/{package}/status', [AdminController::class, 'updatePackageStatus']); 
     Route::put('/profile', [AdminController::class, 'updateProfile']);
    Route::put('/password', [AdminController::class, 'changePassword']);// Update package status
});
Route::middleware(['api.token'])->prefix('admin')->group(function () {
    Route::get('/discounts', [DiscountController::class, 'index']);       // Get all discounts
    Route::post('/discounts', [DiscountController::class, 'store']);      // Create new discount
    Route::put('/discounts/{id}', [DiscountController::class, 'update']); // Update existing discount
    Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);

});

Route::middleware('api.token')->get('/admin/auth/verify', [AdminController::class, 'verifyAuth']);
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [LoginController::class, 'login']);


// Admin (requires is_admin middleware)
Route::middleware(['api.token'])->prefix('admin')->group(function () {
    Route::get('/shipping-addresses', [ShippingAddressController::class, 'index']);
    Route::post('/shipping-addresses', [ShippingAddressController::class, 'store']);
    Route::put('/shipping-addresses/{id}', [ShippingAddressController::class, 'update']);
    Route::delete('/shipping-addresses/{id}', [ShippingAddressController::class, 'destroy']);
});

// Customer view
Route::middleware(['api.token'])->group(function () {
    Route::get('/shipping-locations', [ShippingAddressController::class, 'index']);
    Route::get('/shipping-locations/{id}', [ShippingAddressController::class, 'show']);
});

Route::middleware('api.token')->group(function () {
    Route::post('/packages/{package}/return-request', [SuiteController::class, 'requestReturn']);
    Route::post('/packages/{package}/shipping-request', [SuiteController::class, 'requestShipping']);
});
