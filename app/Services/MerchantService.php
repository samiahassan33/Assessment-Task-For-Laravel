<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */

    public function register(array $data): Merchant
    {
        // TODO: Complete this method
       // dump('Registering merchant with data:', $data);

        return Merchant::create([
            'user_id'=> Arr::get($data, 'user_id', auth()->id()),
            'domain' => $data['domain'],
            'display_name' => $data['display_name'],
        ]);
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        $merchant = $user->merchant;
    
    if ($merchant) {
        $merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['display_name'],
        ]);
    }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        return Merchant::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        $orders = Order::where('affiliate_id', $affiliate->id)
        ->where('payout_status', Order::STATUS_UNPAID)
        ->get();

    foreach ($orders as $order) {
        Queue::push(new PayoutOrderJob($order)); 
    }
    }
}
