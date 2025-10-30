<?php

namespace App\Services\Api\App\Client\SearchHistory;

use App\Models\SearchHistory;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchHistoryService
{
    use ApiResponseTrait;

    /**
     * Get user's search history
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            
            // Check if limit is provided for recent searches
            if ($request->has('limit')) {
                $limit = (int)($request->query('limit', 10));
                $limit = $limit > 0 ? $limit : 10;
                
                $searchHistory = SearchHistory::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                // Paginated results for all history
                $perPage = (int)($request->query('per_page', 20));
                $perPage = $perPage > 0 ? $perPage : 20;
                
                $searchHistory = SearchHistory::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            }

            return $this->successResponse($searchHistory, __('messages.search_history_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_retrieve_search_history'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete search history - all or specific item
     */
    public function delete(Request $request, $id = null)
    {
        try {
            $userId = Auth::id();
            
            if ($id) {
                // Delete specific item
                $searchHistory = SearchHistory::where('user_id', $userId)
                    ->where('id', $id)
                    ->first();

                if (!$searchHistory) {
                    return $this->notFoundResponse(__('messages.search_history_item_not_found'));
                }

                $searchHistory->delete();
                return $this->successResponse(null, __('messages.search_history_item_deleted_successfully'));
            } else {
                // Delete all user's search history
                SearchHistory::where('user_id', $userId)->delete();
                return $this->successResponse(null, __('messages.all_search_history_cleared_successfully'));
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_delete_search_history'), ['error' => $e->getMessage()]);
        }
    }
}
