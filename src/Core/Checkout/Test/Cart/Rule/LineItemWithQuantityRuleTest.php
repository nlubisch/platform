<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Cart\Rule;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Rule\LineItemWithQuantityRule;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Read\ReadCriteria;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\WriteStackException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Validation\ConstraintViolationException;

class LineItemWithQuantityRuleTest extends TestCase
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

    public function testValidateWithMissingParameters()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(2, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (id)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
                static::assertStringEndsWith(' (quantity)', $exception->getViolations()->get(1)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(1)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(1)->getMessage());
            }
        }
    }

    public function testValidateWithoutOptionalOperator()
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
                'type' => LineItemWithQuantityRule::class,
                'ruleId' => $ruleId,
                'value' => [
                    'quantity' => 1,
                    'id' => '0915d54fbf80423c917c61ad5a391b48',
                ],
            ],
        ], $this->context);

        static::assertNotNull($this->conditionRepository->read(new ReadCriteria([$id]), $this->context)->get($id));
    }

    public function testValidateWithMissingQuantity()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (quantity)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithMissingId()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (id)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithInvalidTypeId()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'id' => true,
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (id)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('This value should be of type string.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithInvalidIdUuidFormat()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'id' => '12345',
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (id)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('The string "12345" is not a valid uuid.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithStringQuantity()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'quantity' => '3',
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (quantity)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('ba785a8c-82cb-4283-967c-3cf342181b40', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should be of type int.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testAvailableOperators()
    {
        $ruleId = Uuid::uuid4()->getHex();
        $this->ruleRepository->create(
            [['id' => $ruleId, 'name' => 'Demo rule', 'priority' => 1]],
            Context::createDefaultContext()
        );

        $conditionIdEq = Uuid::uuid4()->getHex();
        $conditionIdNEq = Uuid::uuid4()->getHex();
        $conditionIdLTE = Uuid::uuid4()->getHex();
        $conditionIdGTE = Uuid::uuid4()->getHex();
        $this->conditionRepository->create(
            [
                [
                    'id' => $conditionIdEq,
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => $ruleId,
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
                [
                    'id' => $conditionIdNEq,
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => $ruleId,
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
                [
                    'id' => $conditionIdLTE,
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => $ruleId,
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
                [
                    'id' => $conditionIdGTE,
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => $ruleId,
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'quantity' => 3,
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
            ], $this->context
        );

        static::assertCount(
            4, $this->conditionRepository->read(
            new ReadCriteria([$conditionIdEq, $conditionIdNEq, $conditionIdLTE, $conditionIdGTE]), $this->context
        )
        );
    }

    public function testValidateWithInvalidOperator()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => LineItemWithQuantityRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'id' => '0915d54fbf80423c917c61ad5a391b48',
                        'quantity' => 3,
                        'operator' => 'Invalid',
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(LineItemWithQuantityRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (operator)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('8e179f1b-97aa-4560-a02f-2a8b42e49df7', $exception->getViolations()->get(0)->getCode());
                static::assertSame('The value you selected is not a valid choice.', $exception->getViolations()->get(0)->getMessage());
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
                'type' => LineItemWithQuantityRule::class,
                'ruleId' => $ruleId,
                'value' => [
                    'id' => '0915d54fbf80423c917c61ad5a391b48',
                    'quantity' => 3,
                    'operator' => Rule::OPERATOR_EQ,
                ],
            ],
        ], $this->context);

        static::assertNotNull($this->conditionRepository->read(new ReadCriteria([$id]), $this->context)->get($id));
    }
}
