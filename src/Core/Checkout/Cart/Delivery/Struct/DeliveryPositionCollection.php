<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Delivery\Struct;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Framework\Struct\Collection;

class DeliveryPositionCollection extends Collection
{
    /**
     * @var DeliveryPosition[]
     */
    protected $elements = [];

    public function add(DeliveryPosition $deliveryPosition): void
    {
        $key = $this->getKey($deliveryPosition);
        $this->elements[$key] = $deliveryPosition;
    }

    public function remove(string $identifier): void
    {
        parent::doRemoveByKey($identifier);
    }

    public function removeElement(DeliveryPosition $deliveryPosition): void
    {
        parent::doRemoveByKey($this->getKey($deliveryPosition));
    }

    public function exists(DeliveryPosition $deliveryPosition): bool
    {
        return parent::has($this->getKey($deliveryPosition));
    }

    public function get(string $identifier): ? DeliveryPosition
    {
        if ($this->has($identifier)) {
            return $this->elements[$identifier];
        }

        return null;
    }

    public function getPrices(): PriceCollection
    {
        return new PriceCollection(
            array_map(
                function (DeliveryPosition $position) {
                    return $position->getPrice();
                },
                $this->elements
            )
        );
    }

    public function getLineItems(): LineItemCollection
    {
        return new LineItemCollection(
            array_map(
                function (DeliveryPosition $position) {
                    return $position->getLineItem();
                },
                $this->elements
            )
        );
    }

    public function getWeight(): float
    {
        $weights = $this->getLineItems()->map(function (LineItem $deliverable) {
            return $deliverable->getDeliveryInformation()->getWeight();
        });

        return array_sum($weights);
    }

    public function getQuantity(): float
    {
        $quantities = $this->map(function (DeliveryPosition $position) {
            return $position->getQuantity();
        });

        return array_sum($quantities);
    }

    /**
     * @param DeliveryPosition $element
     *
     * @return string
     */
    protected function getKey(DeliveryPosition $element): string
    {
        return $element->getIdentifier();
    }
}
