<?php

namespace App\Services\Payment;

use App\Models\Merchant;
use App\Services\Payment\Adapters\StripeAdapter;
use App\Services\Payment\Contracts\PaymentProcessorInterface;

class PaymentProcessorFactory
{
    public static function make(Merchant $merchant): PaymentProcessorInterface
    {
        return match ($merchant->processor_type) {
            'stripe' => new StripeAdapter(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            ),
            // Future adapters:
            // 'adyen' => new AdyenAdapter($merchant),
            // 'square' => new SquareAdapter($merchant),
            default => throw new \InvalidArgumentException("Unsupported processor: {$merchant->processor_type}"),
        };
    }
}
