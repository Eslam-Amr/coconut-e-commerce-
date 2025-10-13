<?php

namespace App\Http\Controllers\Api\Dashboard\Product;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Product\ProductVariantRequest;
use App\Models\ProductVariant;
use App\Services\Api\Dashboard\Product\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProductVariantController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'product_variants.view',
        'show' => 'product_variants.view',
        'store' => 'product_variants.create',
        'update' => 'product_variants.update',
        'destroy' => 'product_variants.delete',
        'assignAttributes' => 'product_variants.assign_attributes',
        'removeAttributes' => 'product_variants.remove_attributes',
        'showWithAttributes' => 'product_variants.view_with_attributes',
    ];
    protected static $middleware = ['admin'];

    public function __construct(ProductVariantService $productVariantService)
    {
        parent::__construct(
            $productVariantService,
            ProductVariantRequest::class,
            ProductVariant::class
        );
    }

    /**
     * Assign attribute values to a product variant
     */
    public function assignAttributes(Request $request, ProductVariant $productVariant)
    {
        return $this->service->assignAttributes($productVariant, $request->all());
    }

    /**
     * Remove attribute values from a product variant
     */
    public function removeAttributes(Request $request, ProductVariant $productVariant)
    {
        return $this->service->removeAttributes($productVariant, $request->all());
    }

    /**
     * Get variant with its attributes
     */
    public function showWithAttributes(ProductVariant $productVariant)
    {
        return $this->service->showWithAttributes($productVariant);
    }
}
