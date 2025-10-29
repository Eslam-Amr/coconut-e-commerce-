<?php

namespace App\Services\Api\App\Client\MoneyTransfer;

use App\Models\MoneyTransfer;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MoneyTransferService
{
    use ApiResponseTrait;

    /**
     * Create a new money transfer request
     */
    public function createTransfer(Request $request)
    {
        try {
            $userId = Auth::id();
            $amount = $request->amount;

            // Get user's wallet
            $wallet = Wallet::where('user_id', $userId)->first();
            
            if (!$wallet) {
                return $this->errorResponse('Wallet not found. Please create a wallet first.', [], 404);
            }

            // Check if wallet has sufficient balance
            if ($wallet->balance < $amount) {
                return $this->errorResponse('Insufficient wallet balance. Available balance: ' . $wallet->balance, [], 400);
            }

            DB::beginTransaction();

            try {
                // Create money transfer record
                $transfer = MoneyTransfer::create([
                    'user_id' => $userId,
                    'wallet_id' => $wallet->id,
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'iban' => $request->iban,
                    'amount' => $amount,
                    'status' => 'pending',
                    'transfer_reference' => 'TRF-' . Str::upper(Str::random(8)) . '-' . time(),
                ]);
                // Deduct amount from wallet
                $wallet->decrement('balance', $amount);

                // Create wallet transaction record
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $userId,
                    'amount' => $amount,
                    'transaction_id' => $transfer->transfer_reference,
                    'description' => 'Money transfer to ' . $request->bank_name . ' - ' . $request->account_number,
                    'balance_after' => $wallet->fresh()->balance,
                    'status' => 'completed',
                ]);

                DB::commit();

                return $this->successResponse([
                    'message' => 'Money transfer request created successfully.',
                    'transfer' => $transfer->load(['user', 'wallet']),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create money transfer request', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all money transfers for the authenticated user
     */
    public function getAllTransfers(Request $request)
    {
        try {
            $userId = Auth::id();
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $query = MoneyTransfer::where('user_id', $userId)
                ->with(['user', 'wallet'])
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $transfers = $query->paginate($perPage);

            return $this->successResponse([
                'transfers' => $transfers,
            ]);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve money transfers', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get specific money transfer details
     */
    public function getTransferDetails(Request $request, $transferId)
    {
        try {
            $userId = Auth::id();

            $transfer = MoneyTransfer::where('id', $transferId)
                ->where('user_id', $userId)
                ->with(['user', 'wallet'])
                ->first();

            if (!$transfer) {
                return $this->errorResponse('Money transfer not found.', [], 404);
            }

            return $this->successResponse([
                'transfer' => $transfer,
            ]);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve money transfer details', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel a pending money transfer
     */
    public function cancelTransfer(Request $request, $transferId)
    {
        try {
            $userId = Auth::id();

            $transfer = MoneyTransfer::where('id', $transferId)
                ->where('user_id', $userId)
                ->first();

            if (!$transfer) {
                return $this->errorResponse('Money transfer not found.', [], 404);
            }

            if ($transfer->status !== 'pending') {
                return $this->errorResponse('Only pending transfers can be cancelled.', [], 400);
            }

            DB::beginTransaction();

            try {
                // Update transfer status
                $transfer->update(['status' => 'cancelled']);

                // Refund amount to wallet
                $wallet = $transfer->wallet;
                $wallet->increment('balance', $transfer->amount);

                // Create refund wallet transaction
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $userId,
                    'amount' => $transfer->amount,
                    'transaction_id' => 'REFUND-' . $transfer->transfer_reference,
                    'description' => 'Refund for cancelled transfer to ' . $transfer->bank_name,
                    'balance_after' => $wallet->fresh()->balance,
                    'status' => 'completed',
                ]);

                DB::commit();

                return $this->successResponse([
                    'message' => 'Money transfer cancelled successfully.',
                    'transfer' => $transfer->fresh(),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to cancel money transfer', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
