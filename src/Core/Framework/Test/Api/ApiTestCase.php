<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Api;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated use the traits instead
 */
class ApiTestCase extends WebTestCase
{
    /**
     * @var string[]
     */
    protected $apiUsernames = [];

    /**
     * @var Client
     */
    protected $apiClient;

    /**
     * @var Client
     */
    protected $storefrontApiClient;

    /**
     * @var string
     */
    protected $storefrontApiSalesChannelId;

    /**
     * @throws \Shopware\Core\Framework\Exception\InvalidUuidException
     */
    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();

        $apiClient = $this->getClient();
        $apiClient->setServerParameters([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => ['application/vnd.api+json,application/json'],
        ]);
        $this->authorizeClient($apiClient);

        $storefrontApiClient = $this->getClient();
        $storefrontApiClient->setServerParameters([
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'HTTP_Accept' => 'application/json',
            'HTTP_X_SW_CONTEXT_TOKEN' => Uuid::uuid4()->getHex(),
        ]);
        $this->authorizeStorefrontClient($storefrontApiClient);

        $this->apiClient = $apiClient;
        $this->storefrontApiClient = $storefrontApiClient;
    }

    public function tearDown()
    {
        /** @var Connection $connection */
        $connection = self::$container->get(Connection::class);

        try {
            $connection->executeQuery(
                'DELETE FROM user WHERE username IN (:usernames)',
                ['usernames' => $this->apiUsernames],
                ['usernames' => Connection::PARAM_STR_ARRAY]
            );

            $connection->executeQuery(
                'DELETE FROM sales_channel WHERE id = :salesChannelId',
                ['salesChannelId' => $this->storefrontApiSalesChannelId]
            );
        } catch (\Exception $ex) {
        }

        parent::tearDown();
    }

    public function getClient()
    {
        $clientKernel = self::createKernel();
        $clientKernel->boot();

        return $clientKernel->getContainer()->get('test.client');
    }

    public function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public function getStorefrontApiSalesChannelId(): string
    {
        return $this->storefrontApiSalesChannelId;
    }

    public function assertEntityExists(...$params): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/' . implode('/', $params);

        $this->apiClient->request('GET', $url);

        static::assertSame(
            Response::HTTP_OK,
            $this->apiClient->getResponse()->getStatusCode(),
            'Entity does not exists but should do.'
        );
    }

    public function assertEntityNotExists(...$params): void
    {
        $url = '/api/v' . PlatformRequest::API_VERSION . '/' . implode('/', $params);

        $this->apiClient->request('GET', $url);

        static::assertSame(
            Response::HTTP_NOT_FOUND,
            $this->apiClient->getResponse()->getStatusCode(),
            'Entity exists but should not.'
        );
    }

    /**
     * @throws \Shopware\Core\Framework\Exception\InvalidUuidException
     */
    protected function authorizeClient(Client $client): void
    {
        $username = Uuid::uuid4()->getHex();
        $password = Uuid::uuid4()->getHex();

        /** @var Connection $connection */
        $connection = self::$container->get(Connection::class);
        $connection->insert('user', [
            'id' => Uuid::uuid4()->getBytes(),
            'name' => $username,
            'email' => 'admin@example.com',
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'locale_id' => Uuid::fromStringToBytes(Defaults::LOCALE_EN_GB),
            'active' => 1,
            'created_at' => (new \DateTime())->format(Defaults::DATE_FORMAT),
        ]);

        $this->apiUsernames[] = $username;

        $authPayload = [
            'grant_type' => 'password',
            'client_id' => 'administration',
            'username' => $username,
            'password' => $password,
        ];

        $client->request('POST', '/api/oauth/token', $authPayload);

        $data = json_decode($client->getResponse()->getContent(), true);

        static::assertArrayHasKey('access_token', $data, 'No token returned from API: ' . ($data['errors'][0]['detail'] ?? 'unknown error' . print_r($data, true)));
        static::assertArrayHasKey('refresh_token', $data, 'No refresh_token returned from API: ' . ($data['errors'][0]['detail'] ?? 'unknown error'));

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['access_token']));
    }

    protected function authorizeStorefrontClient(Client $storefrontApiClient): void
    {
        $salesChannelId = Uuid::uuid4();
        $accessKey = AccessKeyHelper::generateAccessKey('sales-channel');

        /** @var RepositoryInterface $salesChannelRepository */
        $salesChannelRepository = self::$container->get('sales_channel.repository');
        $salesChannelRepository->upsert([[
            'id' => $salesChannelId->getHex(),
            'typeId' => Defaults::SALES_CHANNEL_STOREFRONT_API,
            'name' => 'API Test case sales channel',
            'accessKey' => $accessKey,
            'languageId' => Defaults::LANGUAGE_EN,
            'currencyId' => Defaults::CURRENCY,
            'paymentMethodId' => Defaults::PAYMENT_METHOD_DEBIT,
            'shippingMethodId' => Defaults::SHIPPING_METHOD,
            'countryId' => Defaults::COUNTRY,
            'catalogs' => [['id' => Defaults::CATALOG]],
            'currencies' => [['id' => Defaults::CURRENCY]],
            'languages' => [['id' => Defaults::LANGUAGE_EN]],
        ]], Context::createDefaultContext());

        $this->storefrontApiSalesChannelId = $salesChannelId->getHex();

        $header = 'HTTP_' . str_replace('-', '_', strtoupper(PlatformRequest::HEADER_ACCESS_KEY));
        $storefrontApiClient->setServerParameter($header, $accessKey);
    }
}
