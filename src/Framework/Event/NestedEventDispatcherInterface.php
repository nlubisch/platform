<?php

namespace Shopware\Framework\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface NestedEventDispatcherInterface extends EventDispatcherInterface
{
    public function dispatch($eventName, Event $event = null): Event;
}