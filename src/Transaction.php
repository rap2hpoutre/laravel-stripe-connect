<?php


namespace Rap2hpoutre\LaravelStripeConnect;

use Stripe\Account as StripeAccount;
use Stripe\Customer;
use Stripe\Stripe as StripeBase;


class Transaction
{
    private $from, $to, $value, $currency, $to_params;
    private $token;
    private $fee;
    private $from_params;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    public function from($user, $params = [])
    {
        $this->from = $user;
        $this->from_params = $params;
        return $this;
    }

    public function to($user, $params = [])
    {
        $this->to = $user;
        $this->to_params = $params;
        return $this;
    }

    public function amount($value, $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
        return $this;
    }

    public function fee($amount)
    {
        $this->fee = $amount;
        return $this;
    }

    public function create()
    {
        // Prepare vendor
        $vendor = StripeConnect::createAccount($this->to, $this->to_params);
        // Prepare customer
        $customer = StripeConnect::createCustomer($this->token, $this->from, $this->from_params);

        return \Stripe\Charge::create(array(
            "amount" => $this->value,
            "currency" => $this->currency,
            "customer" => $customer->account_id,
            "application_fee" => $this->fee ?? null,
            "destination" => array(
                "account" => $vendor->account_id,
            ),
        ));
    }
}