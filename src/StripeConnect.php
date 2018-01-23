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
        $params = array_merge([
            "type" => "custom",
            "email" => $to->email,
        ], $params);
        return self::create($to, 'account_id', function () use ($params) {
            return Customer::create($params);
        });
    }

    /**
     * @param $token
     * @param $from
     * @param array $params
     * @return Stripe
     */
    public static function createCustomer($token, $from, $params = [])
    {
        $params = array_merge([
            "email" => $from->email,
            'source' => $token,
        ], $params);
        return self::create($from, 'customer_id', function () use ($params) {
            return Customer::create($params);
        });
    }

    /**
     * @param $user
     * @param $id_key
     * @param $callback
     * @return Stripe
     */
    private static function create($user, $id_key, $callback) {
        self::prepare();
        $customer = self::getStripeModel($user);
        if (!$user->$id_key) {
            $user->$id_key = call_user_func($callback)->id;
            $user->save();
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