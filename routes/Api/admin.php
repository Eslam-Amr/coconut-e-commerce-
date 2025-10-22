<?php

use App\Http\Controllers\Api\Dashboard\Auth\Login\LoginController;
use App\Http\Controllers\Api\Dashboard\Auth\Logout\LogoutController;
use App\Http\Controllers\Api\Dashboard\Attribute\AttributeController;
use App\Http\Controllers\Api\Dashboard\Attribute\AttributeValue\AttributeValueController;
use App\Http\Controllers\Api\Dashboard\Category\CategoryController;
use App\Http\Controllers\Api\Dashboard\Banner\BannerController;
use App\Http\Controllers\Api\Dashboard\Slider\SliderController;
use App\Http\Controllers\Api\Dashboard\Brand\BrandController;
use App\Http\Controllers\Api\Dashboard\Voucher\VoucherController;
use App\Http\Controllers\Api\Dashboard\Product\ProductController;
use App\Http\Controllers\Api\Dashboard\Product\ProductVariantController;
use App\Http\Controllers\Api\Dashboard\Product\ProductAttributeController;
use App\Services\Api\Dashboard\Product\VariantAttributeService;
use App\Http\Controllers\Api\Dashboard\FlashSale\FlashSaleController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Dashboard\Country\CountryController;
use App\Http\Controllers\Api\Dashboard\City\CityController;
use App\Http\Controllers\Api\Dashboard\District\DistrictController;
use App\Http\Controllers\Api\Dashboard\Admin\AdminController;
use App\Http\Controllers\Api\Dashboard\Role\RoleController;
use App\Http\Controllers\Api\Dashboard\Permission\PermissionController;
use App\Http\Controllers\Api\Dashboard\Order\OrderController;
use App\Http\Controllers\Api\Dashboard\Refund\RefundController;
use App\Http\Controllers\Api\Dashboard\StaticPage\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout']);

// Attribute routes
Route::apiResource('attributes', AttributeController::class);

// Attribute Value routes
Route::apiResource('attribute-values', AttributeValueController::class);

// Category routes
Route::apiResource('categories', CategoryController::class);

// Toggle active route (bypasses validation)
Route::put('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive']);

// Banner routes
Route::apiResource('banners', BannerController::class);
Route::put('banners/{banner}/toggle-active', [BannerController::class, 'toggleActive']);

// Slider routes
Route::apiResource('sliders', SliderController::class);
Route::put('sliders/{slider}/toggle-active', [SliderController::class, 'toggleActive']);

// Brand routes
Route::apiResource('brands', BrandController::class);
Route::put('brands/{brand}/toggle-active', [BrandController::class, 'toggleActive']);

// Product routes
Route::apiResource('products', ProductController::class);
Route::put('products/{product}/toggle-active', [ProductController::class, 'toggleActive']);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Product Variant routes
Route::apiResource('product-variants', ProductVariantController::class);
Route::put('product-variants/{product_variant}/toggle-active', [ProductVariantController::class, 'toggleActive']);
Route::post('product-variants/{product_variant}/assign-attributes', [ProductVariantController::class, 'assignAttributes']);
Route::delete('product-variants/{product_variant}/remove-attributes', [ProductVariantController::class, 'removeAttributes']);
Route::get('product-variants/{product_variant}/with-attributes', [ProductVariantController::class, 'showWithAttributes']);
Route::post('product-variants/{product_variant}/attach-attribute', [ProductVariantController::class, 'attachAttributeValue']);

// Product Attribute routes
Route::apiResource('product-attributes', ProductAttributeController::class);
Route::get('products/{product}/attributes', [ProductAttributeController::class, 'getByProduct']);
Route::post('products/{product}/attributes/bulk-assign', [ProductAttributeController::class, 'bulkAssign']);
Route::delete('products/{product}/attributes/remove-all', [ProductAttributeController::class, 'removeAll']);
Route::get('products/{product}/available-attributes', [ProductAttributeController::class, 'getAvailableAttributes']);

// Variant Attribute Management routes
// Route::post('products/{product}/create-variants', function ($productId, VariantAttributeService $service, Request $request) {
//     return $service->createVariantsForCombinations($productId, $request->all());
// });
// Route::post('products/{product}/validate-combinations', function ($productId, VariantAttributeService $service, Request $request) {
//     return $service->validateVariantCombinations($productId, $request->input('combinations', []));
// });
// Route::get('products/{product}/variant-summary', function ($productId, VariantAttributeService $service) {
//     return $service->getVariantSummary($productId);
// });
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Voucher routes
Route::apiResource('vouchers', VoucherController::class);
Route::put('vouchers/{voucher}/toggle-active', [VoucherController::class, 'toggleActive']);

// Flash Sale routes
Route::apiResource('flash-sales', FlashSaleController::class);
Route::put('flash-sales/{flash_sale}/toggle-active', [FlashSaleController::class, 'toggleActive']);

// Location routes
Route::apiResource('countries', CountryController::class);
Route::apiResource('cities', CityController::class);
Route::apiResource('districts', DistrictController::class);

// Admin Management routes
Route::apiResource('admins', AdminController::class);
Route::post('admins/{admin}/assign-role', [AdminController::class, 'assignRole']);
Route::delete('admins/{admin}/remove-role', [AdminController::class, 'removeRole']);
Route::post('admins/{admin}/grant-permission', [AdminController::class, 'grantPermission']);
Route::delete('admins/{admin}/revoke-permission', [AdminController::class, 'revokePermission']);
Route::delete('admins/{admin}/remove-direct-permission', [AdminController::class, 'removeDirectPermission']);
Route::put('admins/{admin}/toggle-active', [AdminController::class, 'toggleActive']);
Route::get('available-roles', [AdminController::class, 'getAvailableRoles']);
Route::get('available-permissions', [AdminController::class, 'getAvailablePermissions']);

// Role Management routes
Route::apiResource('roles', RoleController::class);
Route::post('roles/{role}/assign-permission', [RoleController::class, 'assignPermission']);
Route::delete('roles/{role}/remove-permission', [RoleController::class, 'removePermission']);
Route::get('roles/{role}/available-permissions', [RoleController::class, 'getAvailablePermissions']);

// Permission Management routes
Route::apiResource('permissions', PermissionController::class);

// Order Management routes
Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/{order}', [OrderController::class, 'show']);
Route::put('orders/{order}/change-status', [OrderController::class, 'changeStatus']);
Route::get('orders-stats', [OrderController::class, 'getStats']);
Route::get('order-statuses', [OrderController::class, 'getStatuses']);

// Refund Management routes
Route::get('refunds', [RefundController::class, 'index']);
Route::get('refunds/{refund}', [RefundController::class, 'show']);
Route::put('refunds/{refund}/update-status', [RefundController::class, 'updateStatus']);
Route::get('refunds-stats', [RefundController::class, 'getStats']);

// Static Page Management routes
Route::apiResource('static-pages', StaticPageController::class);
// Route::post('categories/{category}/toggle-active', function($category) {
//     try {
//         // If we received a string (ID), resolve the model
//         if (is_string($category)) {
//             $category = \App\Models\Category::findOrFail($category);
//         }
        
//         $category->update([
//             'active' => !$category->active,
//         ]);
        
//         $category->load(['parent', 'children']);
        
//         return response()->json([
//             'success' => true,
//             'message' => 'Category status toggled successfully',
//             'data' => $category
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to toggle category status',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// });