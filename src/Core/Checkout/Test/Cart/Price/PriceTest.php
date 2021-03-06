<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Cart\Price;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Price\Struct\Price;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;

class PriceTest extends TestCase
{
    /**
     * @dataProvider addCases
     *
     * @param Price $a
     * @param Price $b
     * @param Price $expected
     */
    public function testAdd(Price $a, Price $b, Price $expected): void
    {
        $a->add($b);
        static::assertEquals($expected->getQuantity(), $a->getQuantity());
        static::assertEquals($expected->getUnitPrice(), $a->getUnitPrice());
        static::assertEquals($expected->getUnitPrice(), $a->getUnitPrice());
        static::assertEquals($expected->getTotalPrice(), $a->getTotalPrice());
        static::assertEquals($expected->getTaxRules(), $a->getTaxRules());
        static::assertEquals($expected->getCalculatedTaxes(), $a->getCalculatedTaxes());
        static::assertEquals($expected, $a);
    }

    /**
     * @dataProvider subCases
     *
     * @param Price $a
     * @param Price $b
     * @param Price $expected
     */
    public function testSub(Price $a, Price $b, Price $expected): void
    {
        $a->sub($b);
        static::assertEquals($expected->getQuantity(), $a->getQuantity());
        static::assertEquals($expected->getUnitPrice(), $a->getUnitPrice());
        static::assertEquals($expected->getUnitPrice(), $a->getUnitPrice());
        static::assertEquals($expected->getTotalPrice(), $a->getTotalPrice());
        static::assertEquals($expected->getTaxRules(), $a->getTaxRules());
        static::assertEquals($expected->getCalculatedTaxes(), $a->getCalculatedTaxes());
        static::assertEquals($expected, $a);
    }

    public function addCases(): array
    {
        return [
            [
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new Price(2, 2, new CalculatedTaxCollection(), new TaxRuleCollection()),
            ],
            [
                new Price(1, 1, new CalculatedTaxCollection([new CalculatedTax(0.55, 19, 1)]), new TaxRuleCollection()),
                new Price(1, 1, new CalculatedTaxCollection([new CalculatedTax(0.55, 19, 1)]), new TaxRuleCollection()),
                new Price(2, 2, new CalculatedTaxCollection([new CalculatedTax(1.10, 19, 2)]), new TaxRuleCollection()),
            ],
            [
                new Price(1, 1, new CalculatedTaxCollection([new CalculatedTax(0.55, 19, 1)]), new TaxRuleCollection()),
                new Price(-0.5, -0.5, new CalculatedTaxCollection([new CalculatedTax(-0.5, 19, -0.5)]), new TaxRuleCollection()),
                new Price(0.5, 0.5, new CalculatedTaxCollection([new CalculatedTax(0.05, 19, 0.5)]), new TaxRuleCollection()),
            ],
        ];
    }

    public function subCases(): array
    {
        return [
            [
                new Price(2, 2, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            ],
        ];
    }
}
