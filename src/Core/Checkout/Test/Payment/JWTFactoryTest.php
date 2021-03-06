<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Payment;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\Token\JWTFactory;
use Shopware\Core\Checkout\Payment\Exception\InvalidTokenException;
use Shopware\Core\Checkout\Payment\PaymentService;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class JWTFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    /**
     * @var JWTFactory
     */
    protected $tokenFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PaymentService
     */
    private $paymentService;

    public function setUp()
    {
        $this->tokenFactory = $this->getContainer()->get(JWTFactory::class);
        $this->paymentService = $this->getContainer()->get(PaymentService::class);
        $this->context = Context::createDefaultContext();
    }

    /**
     * @throws InvalidTokenException
     */
    public function testGenerateAndGetToken(): void
    {
        $transaction = self::createTransaction();
        $token = $this->tokenFactory->generateToken($transaction, $this->context);
        $tokenStruct = $this->tokenFactory->parseToken($token, $this->context);

        self::assertEquals($transaction->getId(), $tokenStruct->getTransactionId());
        self::assertEquals($transaction->getPaymentMethodId(), $tokenStruct->getPaymentMethodId());
        self::assertEquals($token, $tokenStruct->getToken());
        self::assertGreaterThan(time(), $tokenStruct->getExpires());
    }

    /**
     * @throws InvalidTokenException
     */
    public function testGetInvalidFormattedToken(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->tokenFactory->parseToken(Uuid::uuid4()->getHex(), $this->context);
    }

    /**
     * @throws InvalidTokenException
     */
    public function testGetTokenWithInvalidSignature(): void
    {
        $transaction = self::createTransaction();
        $token = $this->tokenFactory->generateToken($transaction, $this->context);
        $invalidToken = substr($token, 0, -3);

        $this->expectException(InvalidTokenException::class);
        $this->tokenFactory->parseToken($invalidToken, $this->context);
    }

    public function testInvalidateToken(): void
    {
        $success = $this->tokenFactory->invalidateToken(Uuid::uuid4()->getHex(), $this->context);
        self::assertFalse($success);
    }

    public static function createTransaction(): OrderTransactionStruct
    {
        $transactionStruct = new OrderTransactionStruct();
        $transactionStruct->setId(Uuid::uuid4()->getHex());
        $transactionStruct->setOrderId(Uuid::uuid4()->getHex());
        $transactionStruct->setPaymentMethodId(Uuid::uuid4()->getHex());
        $transactionStruct->setOrderTransactionStateId(Defaults::ORDER_TRANSACTION_OPEN);

        return $transactionStruct;
    }
}
