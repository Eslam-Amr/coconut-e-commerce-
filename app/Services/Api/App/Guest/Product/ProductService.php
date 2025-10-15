<?php

namespace App\Services\Api\App\Guest\Product;

use App\Models\Product;
use App\Services\Utilities\RecommendationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductService
{
    use ApiResponseTrait;

    public function __construct(private RecommendationService $recommendationService)
    {
    }

    /**
     * Get paginated list of active products for guests
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int)($request->query('per_page', 12));
            $perPage = $perPage > 0 ? $perPage : 12;

            $query = Product::query()
                ->where('active', true)
                ->where('total_quantity', '>', 0)
                ->with(['brand.translations', 'category.translations', 'translations'])
                ->withAvg('reviews', 'rating')
                ->withCount(['orderItems', 'wishlists', 'reviews'])
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('order_items_count')
                ->orderByDesc('wishlists_count');

            // Apply filters
            if ($request->filled('category_id')) {
                $query->where('category_id', (int)$request->query('category_id'));
            }
            
            if ($request->filled('brand_id')) {
                $query->where('brand_id', (int)$request->query('brand_id'));
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $products = $query->paginate($perPage);

            return $this->successResponse($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get single product details for guests
     */
    public function show(Product $product)
    {
        try {
            // Check if product is active and in stock
            if (!$product->active || $product->total_quantity <= 0) {
                return $this->notFoundResponse('Product not available');
            }

            $product->load([
                'brand.translations', 
                'category.translations', 
                'media',
                'translations',
                'variants.attributeValues.attribute.translations',
                'variants.attributeValues.translations',
                'reviews.user'
            ])
            ->loadAvg('reviews', 'rating')
            ->loadCount(['orderItems', 'wishlists', 'reviews']);

            return $this->successResponse($product, 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve product', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get product recommendations for guests
     */
    public function recommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            // Get guest recommendations (no user ID)
            $recommended = $this->recommendationService->getRecommendations(null, $limit);
// dd($recommended);
            // Load relationships for better response
            $recommended->load(['brand.translations', 'category.translations', 'translations', 'media'])
                ->loadAvg('reviews', 'rating')
                ->loadCount(['orderItems', 'wishlists', 'reviews']);

            return $this->successResponse($recommended, 'Recommendations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get trending products (most sold recently)
     */
    public function trending(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 8));
            $limit = $limit > 0 ? $limit : 8;

            $products = $this->recommendationService->getTrendingProducts($limit);

            return $this->successResponse($products, 'Trending products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve trending products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get featured products (high-rated products)
     */
    public function featured(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 8));
            $limit = $limit > 0 ? $limit : 8;

            $products = $this->recommendationService->getFeaturedProducts($limit);

            return $this->successResponse($products, 'Featured products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve featured products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get most ordered products (with fallback) for guests
     */
    public function mostOrdered(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 8));
            $limit = $limit > 0 ? $limit : 8;

            $products = $this->recommendationService->getMostOrderedProducts($limit);

            return $this->successResponse($products, 'Most ordered products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve most ordered products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get top-rated products (with fallback) for guests
     */
    public function topRated(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 8));
            $limit = $limit > 0 ? $limit : 8;

            $products = $this->recommendationService->getTopRatedProducts($limit);

            return $this->successResponse($products, 'Top rated products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve top rated products', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Get related products based on category and brand
     */
    public function related(Product $product, Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 6));
            $limit = $limit > 0 ? $limit : 6;

            $products = $this->recommendationService->getRelatedProducts($product, $limit);

            return $this->successResponse($products, 'Related products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve related products', ['error' => $e->getMessage()]);
        }
    }
}
