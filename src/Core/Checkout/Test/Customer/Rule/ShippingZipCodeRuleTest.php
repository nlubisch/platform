<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Customer\Rule;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\Rule\ShippingZipCodeRule;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Read\ReadCriteria;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\WriteStackException;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Validation\ConstraintViolationException;

class ShippingZipCodeRuleTest extends TestCase
{
    use KernelTestBehaviour,
        DatabaseTransactionBehaviour;

    /**
     * @var RepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RepositoryInterface
     */
    private $conditionRepository;

    /**
     * @var Context
     */
    private $context;

    protected function setUp()
    {
        $this->ruleRepository = $this->getContainer()->get('rule.repository');
        $this->conditionRepository = $this->getContainer()->get('rule_condition.repository');
        $this->context = Context::createDefaultContext();
    }

    public function testValidateWithMissingZipCodes()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingZipCodeRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(ShippingZipCodeRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (zipCodes)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithEmptyZipCodes()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingZipCodeRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'zipCodes' => [],
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(ShippingZipCodeRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (zipCodes)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithStringZipCodes()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingZipCodeRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'zipCodes' => '12345',
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(ShippingZipCodeRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (zipCodes)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('This value should be of type array.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithInvalidArrayZipCodes()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingZipCodeRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'zipCodes' => [true, 3.1, null, '12345'],
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(3, $exception->getViolations());
                static::assertStringStartsWith(ShippingZipCodeRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (zipCodes)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('This value "1" should be of type string.', $exception->getViolations()->get(0)->getMessage());
                static::assertSame('This value "3.1" should be of type string.', $exception->getViolations()->get(1)->getMessage());
                static::assertSame('This value "" should be of type string.', $exception->getViolations()->get(2)->getMessage());
            }
        }
    }

    public function testIfRuleIsConsistent()
    {
        $ruleId = Uuid::uuid4()->getHex();
        $this->ruleRepository->create(
            [['id' => $ruleId, 'name' => 'Demo rule', 'priority' => 1]],
            Context::createDefaultContext()
        );

        $id = Uuid::uuid4()->getHex();
        $this->conditionRepository->create([
            [
                'id' => $id,
                'type' => ShippingZipCodeRule::class,
                'ruleId' => $ruleId,
                'value' => [
                    'zipCodes' => ['12345', '54321'],
                ],
            ],
        ], $this->context);

        static::assertNotNull($this->conditionRepository->read(new ReadCriteria([$id]), $this->context)->get($id));
    }
}
