<?php declare(strict_types=1);

namespace Shopware\Storefront\Page\Checkout;

use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\HttpFoundation\Request;

class PaymentMethodLoader
{
    /**
     * @var RepositoryInterface
     */
    private $paymentMethodRepository;

    public function __construct(RepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function load(Request $request, Context $context): PaymentMethodCollection
    {
        // todo@dr remove request, provide storefront context, provide calculated cart, use context rule system to validate
        $criteria = $this->createCriteria($request);
        $paymentMethods = $this->paymentMethodRepository->search($criteria, $context);

        return new PaymentMethodCollection($paymentMethods->getElements());
    }

    private function createCriteria(Request $request): Criteria
    {
        $limit = $request->query->getInt('limit', 20);
        $page = $request->query->getInt('page', 1);

        $criteria = new Criteria();
        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setLimit($limit);
        $criteria->addFilter(new EqualsFilter('payment_method.active', 1));
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        return $criteria;
    }
}
