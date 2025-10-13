<?php

namespace App\Http\Controllers\Api\App\Guest\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Utilities\RecommendationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private RecommendationService $recommendationService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int)($request->query('per_page', 12));
        $perPage = $perPage > 0 ? $perPage : 12;

        $query = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_items_count')
            ->orderByDesc('wishlists_count');

        if ($request->filled('category_id')) {
            $query->where('category_id', (int)$request->query('category_id'));
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int)$request->query('brand_id'));
        }

        $products = $query->paginate($perPage);

        return $this->successResponsePaginated($products);
    }

    public function show(Product $product)
    {
        if (!$product->active || $product->total_quantity <= 0) {
            return $this->notFoundResponse();
        }

        $product->load(['brand', 'category', 'media'])
            ->loadAvg('reviews', 'rating')
            ->loadCount(['orderItems', 'wishlists', 'reviews']);

        return $this->successResponse($product);
    }

    public function recommendations(Request $request)
    {
        $limit = (int)($request->query('limit', 12));
        $limit = $limit > 0 ? $limit : 12;

        // Guest recommendations (no user id). If a user_id is explicitly provided, it will be ignored here.
        $recommended = $this->recommendationService->getRecommendations(null, $limit);

        return $this->successResponse($recommended);
    }
}



