<?php declare(strict_types=1);

namespace Shopware\Api\Customer\Event\Customer;

use Shopware\Api\Application\Event\Application\ApplicationBasicLoadedEvent;
use Shopware\Api\Customer\Collection\CustomerDetailCollection;
use Shopware\Api\Customer\Event\CustomerAddress\CustomerAddressBasicLoadedEvent;
use Shopware\Api\Customer\Event\CustomerGroup\CustomerGroupBasicLoadedEvent;
use Shopware\Api\Order\Event\Order\OrderBasicLoadedEvent;
use Shopware\Api\Payment\Event\PaymentMethod\PaymentMethodBasicLoadedEvent;
use Shopware\Context\Struct\ApplicationContext;
use Shopware\Framework\Event\NestedEvent;
use Shopware\Framework\Event\NestedEventCollection;

class CustomerDetailLoadedEvent extends NestedEvent
{
    public const NAME = 'customer.detail.loaded';

    /**
     * @var ApplicationContext
     */
    protected $context;

    /**
     * @var CustomerDetailCollection
     */
    protected $customers;

    public function __construct(CustomerDetailCollection $customers, ApplicationContext $context)
    {
        $this->context = $context;
        $this->customers = $customers;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): ApplicationContext
    {
        return $this->context;
    }

    public function getCustomers(): CustomerDetailCollection
    {
        return $this->customers;
    }

    public function getEvents(): ?NestedEventCollection
    {
        $events = [];
        if ($this->customers->getGroups()->count() > 0) {
            $events[] = new CustomerGroupBasicLoadedEvent($this->customers->getGroups(), $this->context);
        }
        if ($this->customers->getDefaultPaymentMethods()->count() > 0) {
            $events[] = new PaymentMethodBasicLoadedEvent($this->customers->getDefaultPaymentMethods(), $this->context);
        }
        if ($this->customers->getApplications()->count() > 0) {
            $events[] = new ApplicationBasicLoadedEvent($this->customers->getApplications(), $this->context);
        }
        if ($this->customers->getLastPaymentMethods()->count() > 0) {
            $events[] = new PaymentMethodBasicLoadedEvent($this->customers->getLastPaymentMethods(), $this->context);
        }
        if ($this->customers->getDefaultBillingAddress()->count() > 0) {
            $events[] = new CustomerAddressBasicLoadedEvent($this->customers->getDefaultBillingAddress(), $this->context);
        }
        if ($this->customers->getDefaultShippingAddress()->count() > 0) {
            $events[] = new CustomerAddressBasicLoadedEvent($this->customers->getDefaultShippingAddress(), $this->context);
        }
        if ($this->customers->getAddresses()->count() > 0) {
            $events[] = new CustomerAddressBasicLoadedEvent($this->customers->getAddresses(), $this->context);
        }
        if ($this->customers->getOrders()->count() > 0) {
            $events[] = new OrderBasicLoadedEvent($this->customers->getOrders(), $this->context);
        }

        return new NestedEventCollection($events);
    }
}