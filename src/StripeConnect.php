<?php


namespace Rap2hpoutre\LaravelStripeConnect;

use Stripe\Account as StripeAccount;
use Stripe\Customer;
use Stripe\Stripe as StripeBase;


/**
 * Class StripeConnect
 * @package Rap2hpoutre\LaravelStripeConnect
 */
class StripeConnect
{

    /**
     *
     */
    private static function prepare()
    {
        StripeBase::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @param $user
     * @return Stripe
     */
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

    /**
     * @param $to
     * @param array $params
     * @return Stripe
     */
    public static function createAccount($to, $params = [])
    {
        self::prepare();
        $vendor = self::getStripeModel($to);
        if (!$vendor->account_id) {
            $vendor->account_id = StripeAccount::create(array_merge([
                "type" => "custom",
                "email" => $to->email,
            ], $params))->id;
            $vendor->save();
        }
        return $vendor;
    }

    /**
     * @param $token
     * @param $from
     * @param array $params
     * @return Stripe
     */
    public static function createCustomer($token, $from, $params = [])
    {
        self::prepare();
        $customer = self::getStripeModel($from);
        if (!$customer->customer_id) {
            $customer->customer_id = Customer::create(array_merge([
                "email" => $from->email,
                'source' => $token,
            ], $params))->id;
            $customer->save();
        }
        return $customer;
    }

    /**
     * @param null $token
     * @return Transaction
     */
    public static function transaction($token = null)
    {
        return new Transaction($token);
    }
}