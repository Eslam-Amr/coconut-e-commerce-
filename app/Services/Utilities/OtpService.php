<?php

namespace App\Services\Utilities;

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Default OTP expiration time in minutes
     */
    private const DEFAULT_EXPIRY_MINUTES = 1;

    /**
     * Maximum number of digits in OTP code
     */
    public const MAX_NUMBER_OF_OTP = 4;

    /**
     * Generate a new OTP for a phone number
     *
     * @param string $phone
     * @param int $expiryMinutes
     * @return Otp|null
     */
    public function generate(string $phone, int $expiryMinutes = self::DEFAULT_EXPIRY_MINUTES): ?Otp
    {
        try {
            // Delete any existing OTPs for this phone
            $this->deleteOldOtps($phone);

            // Generate a new OTP code
            $otpCode = $this->generateOtpCode();

            // Create the OTP record
            $otp = Otp::create([
                'phone' => $phone,
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
            ]);

            Log::info("OTP generated for phone {$phone}");

            return $otp;
        } catch (\Exception $e) {
            Log::error("Failed to generate OTP for phone {$phone}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify an OTP code
     *
     * @param string $phone
     * @param string $otpCode
     * @return array
     */
    public function isVerifyForgetPassword(string $otpCode): bool
    {
        return Otp::where('is_verified', true)
            ->where('otp_code', $otpCode)
            ->exists();
    }
    public function verifyForgetPassword(string $phone, string $otpCode): array
    {
        try {
            // Find the OTP record
            $otp = Otp::where('phone', $phone)
                ->where('otp_code', $otpCode)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otp) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                    'code' => 'INVALID_OTP'
                ];
            }

            // Check if OTP is expired
            if ($otp->isExpired()) {
                $otp->delete();
                return [
                    'success' => false,
                    'message' => 'OTP has expired',
                    'code' => 'OTP_EXPIRED'
                ];
            }

            $otp->update(['is_verified' => 1]);
            // dd($otp->update(['is_verified' => 1]));

            Log::info("OTP verified successfully for phone {$phone}");

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'code' => 'SUCCESS'
            ];
        } catch (\Exception $e) {
            Log::error("Failed to verify OTP for phone {$phone}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during verification',
                'code' => 'VERIFICATION_ERROR'
            ];
        }
    }
    public function verify(string $phone, string $otpCode): array
    {
        try {
            // Find the OTP record
            $otp = Otp::where('phone', $phone)
                ->where('otp_code', $otpCode)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otp) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                    'code' => 'INVALID_OTP'
                ];
            }

            // Check if OTP is expired
            if ($otp->isExpired()) {
                $otp->delete();
                return [
                    'success' => false,
                    'message' => 'OTP has expired',
                    'code' => 'OTP_EXPIRED'
                ];
            }

            // Mark user as verified and delete OTP
            User::where('phone', $phone)->update(['email_verified_at' => Carbon::now()]);
            $otp->delete();

            Log::info("OTP verified successfully for phone {$phone}");

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'code' => 'SUCCESS'
            ];
        } catch (\Exception $e) {
            Log::error("Failed to verify OTP for phone {$phone}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during verification',
                'code' => 'VERIFICATION_ERROR'
            ];
        }
    }

    /**
     * Delete old OTPs for a phone number
     *
     * @param string $phone
     * @return int
     */
    public function deleteOldOtps(string $phone): int
    {
        try {
            $query = Otp::where('phone', $phone);

            // Delete expired OTPs
            $deletedCount = $query->delete();

            Log::info("Deleted {$deletedCount} old OTPs for phone {$phone}");

            return $deletedCount;
        } catch (\Exception $e) {
            Log::error("Failed to delete old OTPs for phone {$phone}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active OTP for a phone number
     *
     * @param string $phone
     * @return Otp|null
     */
    public function getActiveOtp(string $phone): ?Otp
    {
        return Otp::where('phone', $phone)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Resend OTP (delete old and generate new)
     *
     * @param string $phone
     * @param int $expiryMinutes
     * @return Otp|null
     */
    public function resendOtp(string $phone, int $expiryMinutes = self::DEFAULT_EXPIRY_MINUTES): ?Otp
    {
        return $this->generate($phone, $expiryMinutes);
    }

    /**
     * Generate a random OTP code
     *
     * @param int $length
     * @return string
     */
    private function generateOtpCode(int $length = self::MAX_NUMBER_OF_OTP): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }
}
