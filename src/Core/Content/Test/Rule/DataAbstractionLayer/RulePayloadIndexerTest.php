<?php declare(strict_types=1);

namespace Shopware\Core\Content\Test\Rule\DataAbstractionLayer;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Rule\DataAbstractionLayer\Indexing\RulePayloadIndexer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Read\ReadCriteria;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\Rule\Container\AndRule;
use Shopware\Core\Framework\Rule\Container\OrRule;
use Shopware\Core\Framework\Rule\CurrencyRule;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\SalesChannelRule;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class RulePayloadIndexerTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var RulePayloadIndexer
     */
    private $indexer;

    /**
     * @var Connection
     */
    private $connection;

    public function setUp()
    {
        $this->repository = $this->getContainer()->get('rule.repository');
        $this->indexer = $this->getContainer()->get(RulePayloadIndexer::class);
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->context = Context::createDefaultContext();
    }

    public function testIndex()
    {
        $id = Uuid::uuid4()->getHex();
        $andId = Uuid::uuid4()->getHex();
        $orId = Uuid::uuid4()->getHex();

        $data = [
            'id' => $id,
            'name' => 'test rule',
            'priority' => 1,
            'conditions' => [
                [
                    'id' => $andId,
                    'type' => AndRule::class,
                    'children' => [
                        [
                            'id' => $orId,
                            'parentId' => $andId,
                            'type' => OrRule::class,
                            'children' => [
                                [
                                    'id' => Uuid::uuid4()->getHex(),
                                    'parentId' => $orId,
                                    'type' => CurrencyRule::class,
                                    'value' => [
                                        'currencyIds' => [
                                            'SWAG-CURRENCY-ID-1',
                                            'SWAG-CURRENCY-ID-2',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create([$data], $this->context);

        $this->connection->update('rule', ['payload' => null], ['1' => '1']);
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNull($rule->get('payload'));
        $this->indexer->index(new \DateTime());
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([new OrRule([(new CurrencyRule())->assign(['currencyIds' => ['SWAG-CURRENCY-ID-1', 'SWAG-CURRENCY-ID-2']])])]),
            $rule->getPayload()
        );
    }

    public function testRefresh()
    {
        $id = Uuid::uuid4()->getHex();
        $andId = Uuid::uuid4()->getHex();
        $orId = Uuid::uuid4()->getHex();

        $data = [
            'id' => $id,
            'name' => 'test rule',
            'priority' => 1,
            'conditions' => [
                [
                    'id' => $andId,
                    'type' => AndRule::class,
                    'children' => [
                        [
                            'id' => $orId,
                            'parentId' => $andId,
                            'type' => OrRule::class,
                            'children' => [
                                [
                                    'id' => Uuid::uuid4()->getHex(),
                                    'parentId' => $orId,
                                    'type' => CurrencyRule::class,
                                    'value' => [
                                        'currencyIds' => [
                                            'SWAG-CURRENCY-ID-1',
                                            'SWAG-CURRENCY-ID-2',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create([$data], $this->context);

        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([new OrRule([(new CurrencyRule())->assign(['currencyIds' => ['SWAG-CURRENCY-ID-1', 'SWAG-CURRENCY-ID-2']])])]),
            $rule->getPayload()
        );
    }

    public function testRefreshWithMultipleRules()
    {
        $id = Uuid::uuid4()->getHex();
        $rule2Id = Uuid::uuid4()->getHex();
        $andId = Uuid::uuid4()->getHex();
        $orId = Uuid::uuid4()->getHex();
        $rule2AndId = Uuid::uuid4()->getHex();

        $data = [
            [
                'id' => $id,
                'name' => 'test rule',
                'priority' => 1,
                'conditions' => [
                    [
                        'id' => $andId,
                        'type' => AndRule::class,
                        'children' => [
                            [
                                'id' => $orId,
                                'parentId' => $andId,
                                'type' => OrRule::class,
                                'children' => [
                                    [
                                        'id' => Uuid::uuid4()->getHex(),
                                        'parentId' => $orId,
                                        'type' => CurrencyRule::class,
                                        'value' => [
                                            'currencyIds' => [
                                                'SWAG-CURRENCY-ID-1',
                                                'SWAG-CURRENCY-ID-2',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => $rule2Id,
                'name' => 'second rule',
                'priority' => 42,
                'conditions' => [
                    [
                        'id' => $rule2AndId,
                        'type' => AndRule::class,
                        'children' => [
                            [
                                'parentId' => $rule2AndId,
                                'type' => SalesChannelRule::class,
                                'value' => [
                                    'salesChannelIds' => [
                                        'SWAG-SALES-CHANNEL-ID-1',
                                        'SWAG-SALES-CHANNEL-ID-2',
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create($data, $this->context);

        $this->connection->update('rule', ['payload' => null], ['1' => '1']);
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNull($rule->get('payload'));
        $this->indexer->index(new \DateTime());
        $rules = $this->repository->read(new ReadCriteria([$id, $rule2Id]), $this->context);
        $rule = $rules->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([new OrRule([(new CurrencyRule())->assign(['currencyIds' => ['SWAG-CURRENCY-ID-1', 'SWAG-CURRENCY-ID-2']])])]),
            $rule->getPayload()
        );
        $rule = $rules->get($rule2Id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([(new SalesChannelRule())->assign(['salesChannelIds' => ['SWAG-SALES-CHANNEL-ID-1', 'SWAG-SALES-CHANNEL-ID-2']])]),
            $rule->getPayload()
        );
    }

    public function testIndexWithMultipleRules()
    {
        $id = Uuid::uuid4()->getHex();
        $rule2Id = Uuid::uuid4()->getHex();
        $andId = Uuid::uuid4()->getHex();
        $orId = Uuid::uuid4()->getHex();
        $rule2AndId = Uuid::uuid4()->getHex();

        $data = [
            [
                'id' => $id,
                'name' => 'test rule',
                'priority' => 1,
                'conditions' => [
                    [
                        'id' => $andId,
                        'type' => AndRule::class,
                        'children' => [
                            [
                                'id' => $orId,
                                'parentId' => $andId,
                                'type' => OrRule::class,
                                'children' => [
                                    [
                                        'id' => Uuid::uuid4()->getHex(),
                                        'parentId' => $orId,
                                        'type' => CurrencyRule::class,
                                        'value' => [
                                            'currencyIds' => [
                                                'SWAG-CURRENCY-ID-1',
                                                'SWAG-CURRENCY-ID-2',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => $rule2Id,
                'name' => 'second rule',
                'priority' => 42,
                'conditions' => [
                    [
                        'id' => $rule2AndId,
                        'type' => AndRule::class,
                        'children' => [
                            [
                                'parentId' => $rule2AndId,
                                'type' => SalesChannelRule::class,
                                'value' => [
                                    'salesChannelIds' => [
                                        'SWAG-SALES-CHANNEL-ID-1',
                                        'SWAG-SALES-CHANNEL-ID-2',
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create($data, $this->context);

        $rules = $this->repository->read(new ReadCriteria([$id, $rule2Id]), $this->context);
        $rule = $rules->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([new OrRule([(new CurrencyRule())->assign(['currencyIds' => ['SWAG-CURRENCY-ID-1', 'SWAG-CURRENCY-ID-2']])])]),
            $rule->getPayload()
        );
        $rule = $rules->get($rule2Id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([(new SalesChannelRule())->assign(['salesChannelIds' => ['SWAG-SALES-CHANNEL-ID-1', 'SWAG-SALES-CHANNEL-ID-2']])]),
            $rule->getPayload()
        );
    }

    public function testIndexWithMultipleRootConditions()
    {
        $id = Uuid::uuid4()->getHex();
        $andId = Uuid::uuid4()->getHex();
        $orId = Uuid::uuid4()->getHex();

        $data = [
            'id' => $id,
            'name' => 'test rule',
            'priority' => 1,
            'conditions' => [
                [
                    'id' => $orId,
                    'type' => OrRule::class,
                    'children' => [
                        [
                            'id' => $andId,
                            'parentId' => $orId,
                            'type' => AndRule::class,
                            'children' => [
                                [
                                    'id' => Uuid::uuid4()->getHex(),
                                    'parentId' => $andId,
                                    'type' => CurrencyRule::class,
                                    'value' => [
                                        'currencyIds' => [
                                            'SWAG-CURRENCY-ID-1',
                                            'SWAG-CURRENCY-ID-2',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => Uuid::uuid4()->getHex(),
                    'type' => OrRule::class,
                ]
            ],
        ];

        $this->repository->create([$data], $this->context);

        $this->connection->update('rule', ['payload' => null], ['1' => '1']);
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNull($rule->get('payload'));
        $this->indexer->index(new \DateTime());
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(AndRule::class, $rule->getPayload());

        static::assertCount(2, $rule->getPayload()->getRules());
        static::assertContainsOnlyInstancesOf(OrRule::class, $rule->getPayload()->getRules());
    }

    public function testIndexWithRootRuleNotAndRule()
    {
        $id = Uuid::uuid4()->getHex();

        $data = [
            'id' => $id,
            'name' => 'test rule',
            'priority' => 1,
            'conditions' => [
                [
                    'type' => CurrencyRule::class,
                    'value' => [
                        'currencyIds' => [
                            'SWAG-CURRENCY-ID-1',
                            'SWAG-CURRENCY-ID-2',
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create([$data], $this->context);

        $this->connection->update('rule', ['payload' => null], ['1' => '1']);
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNull($rule->get('payload'));
        $this->indexer->index(new \DateTime());
        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([(new CurrencyRule())->assign(['currencyIds' => ['SWAG-CURRENCY-ID-1', 'SWAG-CURRENCY-ID-2']])]),
            $rule->getPayload()
        );
    }

    public function testRefreshWithRootRuleNotAndRule()
    {
        $id = Uuid::uuid4()->getHex();

        $data = [
            'id' => $id,
            'name' => 'test rule',
            'priority' => 1,
            'conditions' => [
                [
                    'type' => CurrencyRule::class,
                    'value' => [
                        'currencyIds' => [
                            'SWAG-CURRENCY-ID-1',
                            'SWAG-CURRENCY-ID-2',
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create([$data], $this->context);

        $rule = $this->repository->read(new ReadCriteria([$id]), $this->context)->get($id);
        static::assertNotNull($rule->getPayload());
        static::assertInstanceOf(Rule::class, $rule->getPayload());
        static::assertEquals(
            new AndRule([(new CurrencyRule())->assign(['currencyIds' => ['SWAG-CURRENCY-ID-1', 'SWAG-CURRENCY-ID-2']])]),
            $rule->getPayload()
        );
    }
}