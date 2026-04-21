<?php

namespace App\Factory\Prepayment;

class PrepaymentFactoryResolver
{
    public function __construct(private WixPrepaymentFactory $wixFactory) {}

    public function resolve(string $shopType): PrepaymentFactoryInterface
    {
        return match ($shopType) {
            'wix' => $this->wixFactory,
            default => throw new \InvalidArgumentException("Shop type non supporté: $shopType"),
        };
    }
}
