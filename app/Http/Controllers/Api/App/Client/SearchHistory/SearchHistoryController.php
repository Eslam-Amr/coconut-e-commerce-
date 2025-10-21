<?php

namespace App\Http\Controllers\Api\App\Client\SearchHistory;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Client\SearchHistory\SearchHistoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class SearchHistoryController extends Controller implements HasMiddleware
{
    public function __construct(private SearchHistoryService $searchHistoryService)
    {
    }

    /**
     * Get middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'client',
        ];
    }

    /**
     * Get user's search history
     */
    public function index(Request $request)
    {
        return $this->searchHistoryService->index($request);
    }

    /**
     * Delete search history - all or specific item
     */
    public function delete(Request $request, $id = null)
    {
        return $this->searchHistoryService->delete($request, $id);
    }
}
