<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        if (!isset($data['order_id']) || empty($data['order_id'])) {
            throw new \Exception("Missing or invalid 'order_id' in input data");
        }
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
    
    if (!$merchant) {
        throw new \Exception('Merchant not found');
    }

    if (Order::where('external_order_id', $data['order_id'])->exists()) {
        return; // Avoid duplicate orders
    }

    $affiliate = Affiliate::where('discount_code', $data['discount_code'])
        ->where('merchant_id', $merchant->id)
        ->first();

        return Order::create([
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate?->id,
            'subtotal' => $data['subtotal_price'],
            'commission_owed' => $affiliate ? $data['subtotal_price'] * $affiliate->commission_rate : 0,
            'external_order_id' => (string) $data['order_id'], // Ensure it's properly stored
        ]);
    
    }
}
