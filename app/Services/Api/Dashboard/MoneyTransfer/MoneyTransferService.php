<?php

namespace App\Services\Api\Dashboard\MoneyTransfer;

use App\Models\MoneyTransfer;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyTransferService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $status = $request->get('status');
            $perPage = (int) $request->get('per_page', 15);

            $query = MoneyTransfer::with(['user', 'wallet'])->orderByDesc('created_at');
            if ($status) {
                $query->where('status', $status);
            }

            return $this->successResponse([
                'transfers' => $query->paginate($perPage)
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_list_money_transfers'), ['error' => $e->getMessage()]);
        }
    }

    public function show(MoneyTransfer $transfer)
    {
        try {
            $transfer->load(['user', 'wallet']);
            return $this->successResponse(['transfer' => $transfer]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_get_money_transfer'), ['error' => $e->getMessage()]);
        }
    }

  

    public function update(MoneyTransfer $transfer, array $data)
    {
        try {
            $newStatus = $data['status'] ?? null;
            if ($newStatus === null) {
                return $this->errorResponse(__('messages.status_required'), [], 422);
            }

            $allowed = ['completed','failed','cancelled'];
            if (!in_array($newStatus, $allowed, true)) {
                return $this->errorResponse(__('messages.invalid_status'), [], 422);
            }

            if ($transfer->status !== 'pending') {
                return $this->errorResponse(__('messages.only_pending_transfers_can_be_updated'), [], 400);
            }

            DB::transaction(function () use ($transfer, $newStatus) {
                if (in_array($newStatus, ['failed', 'cancelled'], true)) {
                    $wallet = $transfer->wallet;
                    if ($wallet) {
                        $wallet->increment('balance', $transfer->amount);

                        \App\Models\WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $transfer->user_id,
                            'amount' => $transfer->amount,
                            'transaction_id' => 'REFUND-' . $transfer->transfer_reference,
                            'description' => 'Refund for money transfer ' . $transfer->transfer_reference,
                            'balance_after' => $wallet->fresh()->balance,
                            'status' => 'completed',
                        ]);
                    }
                }

                $transfer->update(['status' => $newStatus]);
            });

            return $this->successResponse([
                'message' => __('messages.transfer_status_updated'),
                'transfer' => $transfer->fresh()
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_update_transfer_status'), ['error' => $e->getMessage()]);
        }
    }
}


