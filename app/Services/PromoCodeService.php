<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PromoCodeService
{
    public const FIRST_ORDER_DISCOUNT_PERCENT = 10;

    public function issueForFirstOrder(string $email): PromoCode
    {
        $email = $this->normalizeEmail($email);

        if ($this->hasExistingOrder($email)) {
            throw ValidationException::withMessages([
                'email' => __('promo.not_eligible'),
            ]);
        }

        $existing = PromoCode::where('email', $email)
            ->where('source', 'first_order')
            ->first();

        if ($existing) {
            if ($existing->used_count > 0) {
                throw ValidationException::withMessages([
                    'email' => __('promo.already_issued'),
                ]);
            }

            return $existing;
        }

        return PromoCode::create([
            'code' => $this->generateUniqueCode(),
            'email' => $email,
            'discount_type' => 'percentage',
            'discount_value' => self::FIRST_ORDER_DISCOUNT_PERCENT,
            'max_uses' => 1,
            'source' => 'first_order',
        ]);
    }

    public function validate(string $code, string $email): PromoCode
    {
        $email = $this->normalizeEmail($email);

        $promoCode = PromoCode::where('code', $code)->first();

        if (! $promoCode) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo.not_found'),
            ]);
        }

        if ($promoCode->email !== $email) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo.email_mismatch'),
            ]);
        }

        if (! $promoCode->isUsable()) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo.not_usable'),
            ]);
        }

        if ($promoCode->source === 'first_order' && $this->hasExistingOrder($email)) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo.not_eligible'),
            ]);
        }

        return $promoCode;
    }

    public function redeem(PromoCode $promoCode, Order $order): void
    {
        $affected = DB::table('promo_codes')
            ->where('id', $promoCode->id)
            ->whereColumn('used_count', '<', 'max_uses')
            ->increment('used_count');

        if (! $affected) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo.not_usable'),
            ]);
        }

        $order->update(['promo_code_id' => $promoCode->id]);
    }

    /**
     * True if the email has a prior order that actually happened: any
     * offline order (cash/card on delivery is a real commitment the
     * moment it's placed) or an online order whose payment cleared.
     * An abandoned online-payment attempt (no payment_reference) does
     * not count, since nothing was actually purchased.
     */
    private function hasExistingOrder(string $email): bool
    {
        return Order::where('customer_email', $email)
            ->where(function ($query) {
                $query->where('payment_method', '!=', 'online')
                    ->orWhereNotNull('payment_reference');
            })
            ->exists();
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'WELCOME10-' . Str::upper(Str::random(6));
        } while (PromoCode::where('code', $code)->exists());

        return $code;
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
