<?php

namespace App\Services\Api\Dashboard\Voucher;

use App\Models\Voucher;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class VoucherService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Voucher::query();

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('code', 'like', "%{$search}%");
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
            $vouchers = $query->paginate($perPage);

            return $this->successResponse($vouchers, 'Vouchers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve vouchers', ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $voucher = Voucher::create($data);
            return $this->successResponse($voucher, 'Voucher created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create voucher', ['error' => $e->getMessage()]);
        }
    }

    public function show(Voucher $voucher)
    {
        try {
            return $this->successResponse($voucher, 'Voucher retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve voucher', ['error' => $e->getMessage()]);
        }
    }

    public function update(Voucher $voucher, array $data)
    {
        try {
            $voucher->update($data);
            return $this->successResponse($voucher, 'Voucher updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update voucher', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Voucher $voucher)
    {
        try {
            $voucher->delete();
            return $this->successResponse(null, 'Voucher deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete voucher', ['error' => $e->getMessage()]);
        }
    }
}


