<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart\Cart;
use Shopware\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryDate;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryInformation;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Processor;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\CheckoutContext;
use Shopware\Core\Checkout\Context\CheckoutContextFactory;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class ProcessorTest extends TestCase
{
    use KernelTestBehaviour;

    /**
     * @var CheckoutContextFactory
     */
    private $factory;

    /**
     * @var CheckoutContext
     */
    private $context;

    /**
     * @var Processor
     */
    private $processor;

    protected function setUp()
    {
        $this->processor = $this->getContainer()->get(Processor::class);
        $this->factory = $this->getContainer()->get(CheckoutContextFactory::class);
        $this->context = $this->factory->create(Uuid::uuid4()->getHex(), Defaults::SALES_CHANNEL);
    }

    public function testAddOwnLineItem(): void
    {
        $cart = new Cart('test', 'test');

        $cart->add(
            (new LineItem('A', 'test'))
                ->setPriceDefinition(new QuantityPriceDefinition(100, new TaxRuleCollection()))
        );

        $calculated = $this->processor->process($cart, $this->context);

        self::assertCount(1, $calculated->getLineItems());
        self::assertTrue($calculated->has('A'));
        self::assertSame(100.0, $calculated->get('A')->getPrice()->getTotalPrice());
    }

    public function testDeliveryCreatedForDeliverableLineItem(): void
    {
        $cart = new Cart('test', 'test');

        $cart->add(
            (new LineItem('A', 'test'))
                ->setPriceDefinition(new QuantityPriceDefinition(100, new TaxRuleCollection()))
                ->setDeliveryInformation(
                    new DeliveryInformation(
                        100,
                        100,
                        new DeliveryDate(new \DateTime(), new \DateTime()),
                        new DeliveryDate(new \DateTime(), new \DateTime())
                    )
                )
        );

        $calculated = $this->processor->process($cart, $this->context);

        self::assertCount(1, $calculated->getLineItems());
        self::assertTrue($calculated->has('A'));
        self::assertSame(100.0, $calculated->get('A')->getPrice()->getTotalPrice());

        self::assertCount(1, $calculated->getDeliveries());

        /** @var Delivery $delivery */
        $delivery = $calculated->getDeliveries()->first();
        self::assertTrue($delivery->getPositions()->getLineItems()->has('A'));
    }
}
