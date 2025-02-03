<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

        // Check if the email is already used by a merchant
        if (Merchant::whereHas('user', fn($query) => $query->where('email', $email))->exists()) {
            throw new AffiliateCreateException("Email is already registered as a merchant.");
        }

        // 🚨 Prevent registering an affiliate if the email is already in use by an existing affiliate
        if (Affiliate::whereHas('user', fn($query) => $query->where('email', $email))->exists()) {
            throw new AffiliateCreateException("Email is already registered as an affiliate.");
        }

        return DB::transaction(function () use ($merchant, $email, $name, $commissionRate) {
            // Create or retrieve the user
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => bcrypt('password'),
                'type' => 'affiliate',
                ] // Default password for new users
            );

            // Call external API to create a discount code
            $apiService = app(ApiService::class);
            $discountData = $apiService->createDiscountCode($merchant);

            // Create the affiliate record
            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'commission_rate' => $commissionRate,
                'discount_code' => $discountData['code']
            ]);

            // Send email notification
            Mail::to($email)->send(new AffiliateCreated($affiliate));

            return $affiliate;
        });
    }
}
