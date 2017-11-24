<?php


namespace Rap2hpoutre\LaravelStripeConnect;

use Stripe\Account as StripeAccount;
use Stripe\Customer;
use Stripe\Stripe as StripeBase;


class StripeConnect
{

    private static function prepare()
    {
        StripeBase::setApiKey(config('stripe.key'));
    }

    private static function getStripeModel($user)
    {
        $s = Stripe::where('user_id', $user->id)->first();
        if (!$s) {
            $s = new Stripe();
            $s->user_id = $user->id;
            $s->save();
        }
        return $s;
    }

    public static function createAccount($to, $params = [])
    {
        self::prepare();
        $vendor = self::getStripeModel($to);
        if (!$vendor->account_id) {
            $vendor->account_id = StripeAccount::create(array_merge([
                "type" => "custom",
            ], $params))->id;
            $vendor->save();
        }
        return $vendor;
    }

    public static function createCustomer($token, $from, $params = [])
    {
        self::prepare();
        $customer = self::getStripeModel($from);
        if (!$customer->customer_id) {
            $customer->account_id = Customer::create(array_merge([
                "email" => $from->email,
                'source' => $token,
            ], $params))->id;
            $customer->save();
        }
        return $customer;
    }

    public function transaction()
    {
        return new Transaction;
    }
}