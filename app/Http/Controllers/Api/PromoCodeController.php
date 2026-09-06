<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckPromoCodeRequest;
use App\Http\Requests\StorePromoCodeRequest;
use App\Http\Resources\PromoCodeResource;
use App\Services\PromoCodeService;

class PromoCodeController extends Controller
{
    public function __construct(
        private PromoCodeService $promoCodes,
    ) {}

    public function store(StorePromoCodeRequest $request)
    {
        $promoCode = $this->promoCodes->issueForFirstOrder($request->validated('email'));

        return new PromoCodeResource($promoCode);
    }

    public function check(CheckPromoCodeRequest $request)
    {
        $promoCode = $this->promoCodes->validate(
            $request->validated('promo_code'),
            $request->validated('email'),
        );

        return new PromoCodeResource($promoCode);
    }
}
