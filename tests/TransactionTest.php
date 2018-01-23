<?php
namespace Rap2hpoutre\LaravelStripeConnect\Tests;

use PHPUnit\Framework\TestCase;
use Rap2hpoutre\LaravelStripeConnect\StripeConnect;
use Rap2hpoutre\LaravelStripeConnect\Transaction;

/**
 * Class TransactionTest
 * @package Rap2hpoutre\LaravelStripeConnect\Tests
 */
class TransactionTest extends TestCase
{
    public function testSetters()
    {
        $transaction = StripeConnect::transaction()->fee(35)->amount(1000, 'usd');
        $this->assertInstanceOf(Transaction::class, $transaction);
    }
}
