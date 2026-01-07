<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class TwoFactorService
{
    /**
     * Note: This service requires pragmarx/google2fa-laravel package
     * Install it with: composer require pragmarx/google2fa-laravel
     * For now, using a basic implementation that can be enhanced
     */

    /**
     * Generate a new secret key for TOTP
     * Note: Requires pragmarx/google2fa-laravel package
     */
    public function generateSecret(): string
    {
        if (class_exists(\PragmaRX\Google2FA\Google2FA::class)) {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            return $google2fa->generateSecretKey();
        }

        return bin2hex(random_bytes(16));
    }

    /**
     * Generate QR code URL for authenticator app
     * Note: Requires pragmarx/google2fa-laravel package
     */
    public function getQRCodeUrl(User $user, string $secret): string
    {
        if (class_exists(\PragmaRX\Google2FA\Google2FA::class)) {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $companyName = config('app.name', 'MarketPulse');
            $companyEmail = $user->email;

            return $google2fa->getQRCodeUrl(
                $companyName,
                $companyEmail,
                $secret
            );
        }

        return 'otpauth://totp/' . urlencode(config('app.name')) . ':' . urlencode($user->email) . '?secret=' . $secret . '&issuer=' . urlencode(config('app.name'));
    }

    /**
     * Verify TOTP code
     * Note: Requires pragmarx/google2fa-laravel package
     */
    public function verify(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        if (class_exists(\PragmaRX\Google2FA\Google2FA::class)) {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $secret = Crypt::decryptString($user->two_factor_secret);
            return $google2fa->verifyKey($secret, $code);
        }

        return false;
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    /**
     * Verify recovery code and remove it if valid
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);

        if (!in_array($code, $recoveryCodes)) {
            return false;
        }

        $recoveryCodes = array_values(array_diff($recoveryCodes, [$code]));
        $user->update([
            'two_factor_recovery_codes' => $recoveryCodes ? Crypt::encrypt(json_encode($recoveryCodes)) : null,
        ]);

        return true;
    }

    /**
     * Enable two-factor authentication for user
     */
    public function enable(User $user, string $secret, array $recoveryCodes): void
    {
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => Crypt::encrypt($secret),
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Disable two-factor authentication for user
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}

