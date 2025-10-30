<?php

namespace App\Services\Api\Dashboard\FlashSale;

use App\Models\FlashSale;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class FlashSaleService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = FlashSale::query();

            if ($request->filled('search')) {
                $query->where('title', 'like', "%{$request->get('search')}%");
            }

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->filled('start_date_from')) {
                $query->whereDate('start_date', '>=', $request->date('start_date_from'));
            }
            if ($request->filled('start_date_to')) {
                $query->whereDate('start_date', '<=', $request->date('start_date_to'));
            }
            if ($request->filled('end_date_from')) {
                $query->whereDate('end_date', '>=', $request->date('end_date_from'));
            }
            if ($request->filled('end_date_to')) {
                $query->whereDate('end_date', '<=', $request->date('end_date_to'));
            }

           
            $perPage = $request->integer('per_page', 15);
            $items = $query->paginate($perPage);
            return $this->successResponse($items, __('messages.flash_sales_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $flashSale = FlashSale::create($data);
            return $this->successResponse($flashSale, __('messages.flash_sale_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(FlashSale $flashSale)
    {
        try {
            return $this->successResponse($flashSale, __('messages.flash_sale_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(FlashSale $flashSale, array $data)
    {
        try {
            $flashSale->update($data);
            return $this->successResponse($flashSale, __('messages.flash_sale_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(FlashSale $flashSale)
    {
        try {
            $flashSale->delete();
            return $this->successResponse(null, __('messages.flash_sale_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }
}


