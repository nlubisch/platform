<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\DataAbstractionLayer\Write;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturerTranslation\ProductManufacturerTranslationDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\Currency\Aggregate\CurrencyTranslation\CurrencyTranslationDefinition;
use Shopware\Core\System\Currency\CurrencyDefinition;
use Shopware\Core\System\Exception\LanguageIdentifierNotFoundException;
use Shopware\Core\System\Language\LanguageDefinition;

class TranslationTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var RepositoryInterface
     */
    private $productRepository;

    /**
     * @var RepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var RepositoryInterface
     */
    private $languageRepository;

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var Context
     */
    private $context;

    protected function setUp()
    {
        $this->productRepository = $this->getContainer()->get('product.repository');
        $this->currencyRepository = $this->getContainer()->get('currency.repository');
        $this->languageRepository = $this->getContainer()->get('language.repository');
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->context = Context::createDefaultContext();
    }

    public function testCurrencyWithTranslationViaLocale(): void
    {
        $name = 'US Dollar';
        $shortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_GB' => [
                    'name' => 'US Dollar',
                    'shortName' => 'USD',
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $name], $payload);
        static::assertArraySubset(['shortName' => $shortName], $payload);
    }

    public function testCurrencyWithTranslationViaLanguageIdSimpleNotation(): void
    {
        $name = 'US Dollar';
        $shortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                [
                    'languageId' => Defaults::LANGUAGE_EN,
                    'name' => 'US Dollar',
                    'shortName' => 'USD',
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $name], $payload);
        static::assertArraySubset(['shortName' => $shortName], $payload);
    }

    public function testCurrencyWithTranslationMergeViaLocaleAndLanguageId(): void
    {
        $name = 'US Dollar';
        $shortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_GB' => [
                    'name' => $name,
                ],

                Defaults::LANGUAGE_EN => [
                    'shortName' => $shortName,
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $name], $payload);
        static::assertArraySubset(['shortName' => $shortName], $payload);
    }

    public function testCurrencyWithTranslationMergeOverwriteViaLocaleAndLanguageId(): void
    {
        $name = 'US Dollar';
        $shortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_GB' => [
                    'name' => $name,
                    'shortName' => 'should be overwritten',
                ],

                Defaults::LANGUAGE_EN => [
                    'shortName' => $shortName,
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $name], $payload);
        static::assertArraySubset(['shortName' => $shortName], $payload);
    }

    public function testCurrencyWithTranslationViaLocaleAndLanguageId(): void
    {
        $germanLanguageId = Uuid::uuid4()->getHex();
        $germanName = 'Amerikanischer Dollar';
        $germanShortName = 'US Dollar Deutsch';
        $englishName = 'US Dollar';
        $englishShortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_GB' => [
                    'name' => $englishName,
                    'shortName' => $englishShortName,
                ],

                $germanLanguageId => [
                    'name' => $germanName,
                    'shortName' => $germanShortName,
                    'language' => [
                        'id' => $germanLanguageId,
                        'name' => 'de_DE',
                        'locale' => [
                            'id' => Uuid::uuid4()->getHex(),
                            'code' => 'x-tst_DE2',
                        ],
                        'translationCode' => [
                            'id' => Uuid::uuid4()->getHex(),
                            'code' => 'x-tst_DE3',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(2, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains($germanLanguageId, $languageIds);
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload1 = $translations->getPayload()[0];
        $payload2 = $translations->getPayload()[1];

        static::assertArraySubset(
            [
                'shortName' => $germanShortName,
                'name' => $germanName,
            ],
            $payload1
        );

        static::assertArraySubset(
            [
                'shortName' => $englishShortName,
                'name' => $englishName,
            ],
            $payload2
        );
    }

    public function testCurrencyTranslationCaching(): void
    {
        $englishName = 'US Dollar';
        $englishShortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_GB' => [
                    'name' => $englishName,
                    'shortName' => $englishShortName,
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $englishName], $payload);
        static::assertArraySubset(['shortName' => $englishShortName], $payload);

        $germanLanguageId = Uuid::uuid4()->getHex();
        $data = [
            'id' => $germanLanguageId,
            'name' => 'de_DE2',
            'locale' => [
                'code' => 'x-de_DE2',
            ],
            'translationCode' => [
                'code' => 'x-de_DE3',
            ],
        ];

        $this->languageRepository->create([$data], $this->context);

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'de_DE2' => [
                    'name' => 'Amerikanische Dollar',
                    'shortName' => 'US Dollar German',
                ],
            ],
        ];

        $this->expectException(LanguageIdentifierNotFoundException::class);
        $this->currencyRepository->create([$data], $this->context);
    }

    public function testCurrencyTranslationWithCachingAndInvalidation(): void
    {
        static::markTestSkipped();
        $englishName = 'US Dollar';
        $englishShortName = 'USD';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_GB' => [
                    'name' => $englishName,
                    'shortName' => $englishShortName,
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $englishName], $payload);
        static::assertArraySubset(['shortName' => $englishShortName], $payload);

        $germanLanguageId = Uuid::uuid4()->getHex();
        $data = [
            'id' => $germanLanguageId,
            'locale' => [
                'name' => 'Niederländisch',
                'code' => 'x-nl_NL',
                'territory' => 'Niederlande',
            ],
            'name' => 'nl_NL',
        ];

        $this->languageRepository->create([$data], $this->context);

        // TODO: invalidate cache

        $germanName = 'Amerikaans Dollar';
        $germanShortName = 'US Dollar German';

        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'x-nl_NL' => [
                    'name' => $germanName,
                    'shortName' => $germanShortName,
                ],
            ],
        ];

        $result = $this->currencyRepository->create([$data], $this->context);

        $currencies = $result->getEventByDefinition(CurrencyDefinition::class);
        static::assertCount(1, $currencies->getIds());

        $translations = $result->getEventByDefinition(CurrencyTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains($germanLanguageId, $languageIds);

        $payload = $translations->getPayload()[0];
        static::assertArraySubset(['name' => $germanName], $payload);
        static::assertArraySubset(['shortName' => $germanShortName], $payload);
    }

    public function testCurrencyTranslationWithInvalidLocaleCode(): void
    {
        $data = [
            'factor' => 1,
            'symbol' => '$',
            'translations' => [
                'en_UK' => [
                    'name' => 'US Dollar',
                    'shortName' => 'USD',
                ],
            ],
        ];

        $this->expectException(LanguageIdentifierNotFoundException::class);
        $this->currencyRepository->create([$data], $this->context);
    }

    public function testProductWithDifferentTranslations(): void
    {
        $germanLanguageId = Uuid::uuid4()->getHex();

        $data = [
            [
                'id' => '79dc5e0b5bd1404a9dec7841f6254c7e',
                'manufacturer' => [
                    'id' => 'e4e8988334a34bb48d397b41a611084f',
                    'name' => 'Das blaue Haus',
                    'link' => 'http://www.blaueshaus-shop.de',
                ],
                'tax' => [
                    'id' => 'fe4eb0fd92a7417ebf8720a5148aae64',
                    'taxRate' => 19,
                    'name' => '19%',
                ],
                'price' => [
                    'gross' => 7.9899999999999824,
                    'net' => 6.7142857142857,
                ],
                'translations' => [
                    $germanLanguageId => [
                        'id' => '4f1bcf3bc0fb4e62989e88b3bd37d1a2',
                        'productId' => '79dc5e0b5bd1404a9dec7841f6254c7e',
                        'name' => 'Backform gelb',
                        'description' => 'inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his misericordite.',
                        'language' => [
                            'id' => $germanLanguageId,
                            'name' => 'de_DE',
                            'locale' => [
                                'id' => Uuid::uuid4()->getHex(),
                                'code' => 'x-de_DE',
                            ],
                            'translationCode' => [
                                'id' => Uuid::uuid4()->getHex(),
                                'code' => 'x-de_DE2',
                            ],
                        ],
                    ],
                    Defaults::LANGUAGE_EN => [
                        'name' => 'Test En',
                    ],
                ],
                'cover' => [
                    'id' => 'd610dccf27754a7faa5c22d7368e6d8f',
                    'productId' => '79dc5e0b5bd1404a9dec7841f6254c7e',
                    'position' => 1,
                    'media' => [
                        'id' => '4b2252d11baa49f3a62e292888f5e439',
                        'title' => 'Backform-gelb',
                    ],
                ],
                'active' => true,
                'isCloseout' => false,
                'pseudoSales' => 0,
                'markAsTopseller' => false,
                'allowNotification' => false,
                'sales' => 0,
                'stock' => 45,
                'minStock' => 0,
                'position' => 0,
                'weight' => 0,
                'minPurchase' => 1,
                'shippingFree' => false,
                'purchasePrice' => 0,
            ],
        ];

        $result = $this->productRepository->create($data, $this->context);

        $products = $result->getEventByDefinition(ProductDefinition::class);
        static::assertCount(1, $products->getIds());

        $languages = $result->getEventByDefinition(LanguageDefinition::class);
        static::assertCount(1, array_unique($languages->getIds()));
        static::assertContains($germanLanguageId, $languages->getIds());

        $translations = $result->getEventByDefinition(ProductManufacturerTranslationDefinition::class);
        static::assertCount(1, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains($germanLanguageId, $languageIds);

        $translations = $result->getEventByDefinition(ProductTranslationDefinition::class);
        static::assertCount(2, $translations->getIds());
        $languageIds = array_column($translations->getPayload(), 'languageId');
        static::assertContains(Defaults::LANGUAGE_EN, $languageIds);
        static::assertContains($germanLanguageId, $languageIds);
    }
}
