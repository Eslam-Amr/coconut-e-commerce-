<?php

namespace App\Http\Controllers\Api\Dashboard\Product;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Product\ProductVariantRequest;
use App\Models\ProductVariant;
use App\Services\Api\Dashboard\Product\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends GenericCrudController
{
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
