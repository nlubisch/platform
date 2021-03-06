<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\DiscountSurcharge\Rule\Context;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart\Cart;
use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Checkout\CheckoutContext;
use Shopware\Core\Checkout\Customer\CustomerStruct;
use Shopware\Core\Checkout\Customer\Rule\IsNewCustomerRule;

class IsNewCustomerRuleTest extends TestCase
{
    public function testIsNewCustomer(): void
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(Cart::class);

        $customer = new CustomerStruct();
        $customer->setFirstLogin(new \DateTime());

        $context = $this->createMock(CheckoutContext::class);

        $context
            ->method('getCustomer')
            ->will(static::returnValue($customer));

        static::assertTrue(
            $rule->match(new CartRuleScope($cart, $context))->matches()
        );
    }

    public function testIsNotNewCustomer(): void
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(Cart::class);

        $customer = new CustomerStruct();
        $customer->setFirstLogin(
            (new \DateTime())->sub(
                new \DateInterval('P' . 10 . 'D')
            )
        );

        $context = $this->createMock(CheckoutContext::class);

        $context
            ->method('getCustomer')
            ->will(static::returnValue($customer));

        static::assertFalse(
            $rule->match(new CartRuleScope($cart, $context))->matches()
        );
    }

    public function testWithFutureDate(): void
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(Cart::class);

        $customer = new CustomerStruct();
        $customer->setFirstLogin(
            (new \DateTime())->add(
                new \DateInterval('P' . 10 . 'D')
            )
        );

        $context = $this->createMock(CheckoutContext::class);

        $context
            ->method('getCustomer')
            ->will(static::returnValue($customer));

        static::assertFalse(
            $rule->match(new CartRuleScope($cart, $context))->matches()
        );
    }

    public function testWithoutCustomer(): void
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(Cart::class);

        $context = $this->createMock(CheckoutContext::class);

        $context
            ->method('getCustomer')
            ->will(static::returnValue(null));

        static::assertFalse(
            $rule->match(new CartRuleScope($cart, $context))->matches()
        );
    }
}
