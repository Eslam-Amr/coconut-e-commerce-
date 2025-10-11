<?php

namespace App\Http\Controllers\Api\Dashboard\Product;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Product\ProductAttributeRequest;
use App\Models\ProductAttribute;
use App\Services\Api\Dashboard\Product\ProductAttributeService;
use Illuminate\Http\Request;

class ProductAttributeController extends GenericCrudController
{
    public function __construct(ProductAttributeService $productAttributeService)
    {
        parent::__construct(
            $productAttributeService,
            ProductAttributeRequest::class,
            ProductAttribute::class
        );
    }

    /**
     * Get attributes for a specific product
     */
    public function getByProduct(Request $request, $productId)
    {
        return $this->service->getByProduct($productId, $request->all());
    }

    /**
     * Bulk assign attributes to a product
     */
    public function bulkAssign(Request $request, $productId)
    {
        return $this->service->bulkAssign($productId, $request->all());
    }

    /**
     * Remove all attributes from a product
     */
    public function removeAll(Request $request, $productId)
    {
        return $this->service->removeAll($productId);
    }

    /**
     * Get available attributes for a product's category
     */
    public function getAvailableAttributes(Request $request, $productId)
    {
        return $this->service->getAvailableAttributes($productId);
    }
}
