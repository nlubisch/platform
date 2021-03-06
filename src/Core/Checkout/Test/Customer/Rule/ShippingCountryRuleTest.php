<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Customer\Rule;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\Rule\ShippingCountryRule;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Read\ReadCriteria;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\WriteStackException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Validation\ConstraintViolationException;

class ShippingCountryRuleTest extends TestCase
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
                    'type' => ShippingCountryRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(ShippingCountryRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (countryIds)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
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
                'type' => ShippingCountryRule::class,
                'ruleId' => $ruleId,
                'value' => [
                    'countryIds' => [Uuid::uuid4()->getHex(), Uuid::uuid4()->getHex()],
                ],
            ],
        ], $this->context);

        static::assertNotNull($this->conditionRepository->read(new ReadCriteria([$id]), $this->context)->get($id));
    }

    public function testValidateWithMissingCountryIds()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingCountryRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
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
                static::assertStringStartsWith(ShippingCountryRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (countryIds)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithEmptyCountryIds()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingCountryRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'operator' => Rule::OPERATOR_EQ,
                        'countryIds' => [],
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(1, $exception->getViolations());
                static::assertStringStartsWith(ShippingCountryRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (countryIds)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('c1051bb4-d103-4f74-8988-acbcafc7fdc3', $exception->getViolations()->get(0)->getCode());
                static::assertSame('This value should not be blank.', $exception->getViolations()->get(0)->getMessage());
            }
        }
    }

    public function testValidateWithInvalidCountryIdsUuid()
    {
        try {
            $this->conditionRepository->create([
                [
                    'type' => ShippingCountryRule::class,
                    'ruleId' => Uuid::uuid4()->getHex(),
                    'value' => [
                        'operator' => Rule::OPERATOR_EQ,
                        'countryIds' => ['INVALID-UUID', true, 3],
                    ],
                ],
            ], $this->context);
            static::fail('Exception was not thrown');
        } catch (WriteStackException $stackException) {
            static::assertGreaterThan(0, count($stackException->getExceptions()));
            /** @var ConstraintViolationException $exception */
            foreach ($stackException->getExceptions() as $exception) {
                static::assertCount(3, $exception->getViolations());
                static::assertStringStartsWith(ShippingCountryRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                static::assertStringEndsWith(' (countryIds)', $exception->getViolations()->get(0)->getPropertyPath());
                static::assertSame('The value "INVALID-UUID" is not a valid uuid.', $exception->getViolations()->get(0)->getMessage());
                static::assertSame('The value "1" is not a valid uuid.', $exception->getViolations()->get(1)->getMessage());
                static::assertSame('The value "3" is not a valid uuid.', $exception->getViolations()->get(2)->getMessage());
            }
        }
    }

    public function testValidateWithValidOperators()
    {
        $ruleId = Uuid::uuid4()->getHex();
        $this->ruleRepository->create(
            [['id' => $ruleId, 'name' => 'Demo rule', 'priority' => 1]],
            Context::createDefaultContext()
        );

        $conditionIdEq = Uuid::uuid4()->getHex();
        $conditionIdNEq = Uuid::uuid4()->getHex();
        $this->conditionRepository->create(
            [
                [
                    'id' => $conditionIdEq,
                    'type' => ShippingCountryRule::class,
                    'ruleId' => $ruleId,
                    'value' => [
                        'countryIds' => [Uuid::uuid4()->getHex(), Uuid::uuid4()->getHex()],
                        'operator' => Rule::OPERATOR_EQ,
                    ],
                ],
                [
                    'id' => $conditionIdNEq,
                    'type' => ShippingCountryRule::class,
                    'ruleId' => $ruleId,
                    'value' => [
                        'countryIds' => [Uuid::uuid4()->getHex(), Uuid::uuid4()->getHex()],
                        'operator' => Rule::OPERATOR_NEQ,
                    ],
                ],
            ], $this->context
        );

        static::assertCount(
            2, $this->conditionRepository->read(
            new ReadCriteria([$conditionIdEq, $conditionIdNEq]), $this->context
        )
        );
    }

    public function testValidateWithInvalidOperators()
    {
        foreach ([Rule::OPERATOR_LTE, Rule::OPERATOR_GTE, 'Invalid'] as $operator) {
            try {
                $this->conditionRepository->create([
                    [
                        'type' => ShippingCountryRule::class,
                        'ruleId' => Uuid::uuid4()->getHex(),
                        'value' => [
                            'countryIds' => [Uuid::uuid4()->getHex(), Uuid::uuid4()->getHex()],
                            'operator' => $operator,
                        ],
                    ],
                ], $this->context);
                static::fail('Exception was not thrown');
            } catch (WriteStackException $stackException) {
                static::assertGreaterThan(0, count($stackException->getExceptions()));
                /** @var ConstraintViolationException $exception */
                foreach ($stackException->getExceptions() as $exception) {
                    static::assertCount(1, $exception->getViolations());
                    static::assertStringStartsWith(ShippingCountryRule::class, $exception->getViolations()->get(0)->getPropertyPath());
                    static::assertStringEndsWith(' (operator)', $exception->getViolations()->get(0)->getPropertyPath());
                    static::assertSame('8e179f1b-97aa-4560-a02f-2a8b42e49df7', $exception->getViolations()->get(0)->getCode());
                    static::assertSame('The value you selected is not a valid choice.', $exception->getViolations()->get(0)->getMessage());
                }
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
                'type' => ShippingCountryRule::class,
                'ruleId' => $ruleId,
                'value' => [
                    'operator' => Rule::OPERATOR_EQ,
                    'countryIds' => [Uuid::uuid4()->getHex(), Uuid::uuid4()->getHex()],
                ],
            ],
        ], $this->context);

        static::assertNotNull($this->conditionRepository->read(new ReadCriteria([$id]), $this->context)->get($id));
    }
}
