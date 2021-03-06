<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;
use Symfony\Component\EventDispatcher\Event;

abstract class NestedEvent extends Event implements \JsonSerializable
{
    use JsonSerializableTrait;

    abstract public function getName(): string;

    abstract public function getContext(): Context;

    /**
     * @return NestedEventCollection|null
     */
    public function getEvents(): ?NestedEventCollection
    {
        return null;
    }

    public function getFlatEventList(): NestedEventCollection
    {
        $events = [$this];

        if (!$nestedEvents = $this->getEvents()) {
            return new NestedEventCollection($events);
        }

        /** @var NestedEvent $event */
        foreach ($nestedEvents as $event) {
            $events[] = $event;
            foreach ($event->getFlatEventList() as $item) {
                $events[] = $item;
            }
        }

        return new NestedEventCollection($events);
    }
}
