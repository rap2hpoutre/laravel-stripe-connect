<?php


namespace Rap2hpoutre\LaravelStripeConnect;

use Stripe\Account as StripeAccount;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe as StripeBase;
use Stripe\Transfer;

/**
 * Class Transaction
 * @package Rap2hpoutre\LaravelStripeConnect
 */
class Transaction
{
    /**
     * @var
     */
    private $from, $to, $value, $currency, $to_params, $token, $fee, $from_params, $saved_customer, $transfers = [];

    /**
     * Transaction constructor.
     * @param null $token
     */
    public function __construct($token = null)
    {
        $this->token = $token;
    }

    /**
     * Set the Customer.
     *
     * @param $user
     * @param array $params
     * @return $this
     */
    public function from($user, $params = [])
    {
        $this->from = $user;
        $this->from_params = $params;
        return $this;
    }

    /**
     * @return $this
     */
    public function useSavedCustomer()
    {
        $this->saved_customer = true;
        return $this;
    }

    /**
     * Set the Vendor.
     *
     * @param $user
     * @param array $params
     * @return $this
     */
    public function to($user, $params = [])
    {
        $this->to = $user;
        $this->to_params = $params;
        return $this;
    }

    /**
     * The amount of the transaction.
     *
     * @param $value
     * @param $currency
     * @return $this
     */
    public function amount($value, $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
        return $this;
    }

    /**
     * Take your fees here.
     *
     * @param $amount
     * @return $this
     */
    public function fee($amount)
    {
        $this->fee = $amount;
        return $this;
    }

    /**
     * Transfer an amount from the charge to someone.
     *
     * @param $amount
     * @param $receiver
     * @param $currency
     * @param $receiver_params
     * @return $this
     */
    public function transfer($amount, $receiver, $currency = null, $receiver_params = null)
    {
        array_push($this->transfers, [
            'amount' => $amount,
            'currency' => $currency ?? $this->currency,
            'receiver' => $receiver,
            'receiver_params' => $receiver_params
        ]);

        return $this;
    }

    /**
     * Create the transaction: charge customer and credit vendor.
     * This function saves the two accounts.
     *
     * @param array $params
     * @return Charge
     */
    public function create($params = [])
    {
        // Prepare vendor
        $vendor = StripeConnect::createAccount($this->to, $this->to_params);

        // Prepare customer
        if ($this->saved_customer) {
            $customer = StripeConnect::createCustomer($this->token, $this->from, $this->from_params);
            $params["customer"] = $customer->customer_id;
        } else {
            $params["source"] = $this->token;
        }

        $charge =  Charge::create(array_merge([
            "amount" => $this->value,
            "currency" => $this->currency,
            "destination" => [
                "account" => $vendor->account_id,
            ],
            "application_fee" => $this->fee ?? null,
        ], $params));

        // Send the transfers to the respective receiver
        foreach ($this->transfers as $transfer) {
            $receiver = StripeConnect::createAccount($transfer['receiver'], $transfer['receiver_params']);

            Transfer::create([
                'amount' => $transfer['amount'],
                'currency' => $this->currency,
                'destination' => $receiver->account_id,
                'transfer_group' => $charge->transfer_group
            ]);
        }

        return $charge;
    }
}
