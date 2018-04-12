<?php

namespace Shopware\Api\Application\Repository;

use Shopware\Api\Entity\Search\AggregatorResult;
use Shopware\Api\Entity\Search\IdSearchResult;
use Shopware\Api\Entity\Search\Criteria;
use Shopware\Api\Entity\Search\EntityAggregatorInterface;
use Shopware\Api\Entity\Search\EntitySearcherInterface;
use Shopware\Api\Entity\Read\EntityReaderInterface;
use Shopware\Api\Entity\RepositoryInterface;
use Shopware\Api\Entity\Write\WriteContext;
use Shopware\Api\Entity\Write\GenericWrittenEvent;
use Shopware\Context\Struct\ApplicationContext;
use Shopware\Context\Struct\ShopContext;
use Shopware\Api\Application\Event\Application\ApplicationSearchResultLoadedEvent;
use Shopware\Api\Application\Event\Application\ApplicationBasicLoadedEvent;
use Shopware\Api\Application\Event\Application\ApplicationAggregationResultLoadedEvent;
use Shopware\Api\Application\Event\Application\ApplicationIdSearchResultLoadedEvent;
use Shopware\Api\Application\Struct\ApplicationSearchResult;
use Shopware\Api\Application\Definition\ApplicationDefinition;
use Shopware\Api\Application\Collection\ApplicationBasicCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Version\VersionManager;
use Shopware\Api\Application\Collection\ApplicationDetailCollection;
use Shopware\Api\Application\Event\Application\ApplicationDetailLoadedEvent;

class ApplicationRepository implements RepositoryInterface
{
   /**
    * @var EntityReaderInterface
    */
   private $reader;

   /**
    * @var EntitySearcherInterface
    */
   private $searcher;

   /**
    * @var EntityAggregatorInterface
    */
   private $aggregator;

   /**
    * @var EventDispatcherInterface
    */
   private $eventDispatcher;

   /**
    * @var VersionManager
    */
   private $versionManager;

   public function __construct(
       EntityReaderInterface $reader,
       VersionManager $versionManager,
       EntitySearcherInterface $searcher,
       EntityAggregatorInterface $aggregator,
       EventDispatcherInterface $eventDispatcher
   ) {
       $this->reader = $reader;
       $this->searcher = $searcher;
       $this->aggregator = $aggregator;
       $this->eventDispatcher = $eventDispatcher;
       $this->versionManager = $versionManager;
   }

    public function search(Criteria $criteria, ApplicationContext $context): ApplicationSearchResult
    {
        $ids = $this->searchIds($criteria, $context);

        $entities = $this->readBasic($ids->getIds(), $context);

        $aggregations = null;
        if ($criteria->getAggregations()) {
            $aggregations = $this->aggregate($criteria, $context);
        }

        $result = ApplicationSearchResult::createFromResults($ids, $entities, $aggregations);

        $event = new ApplicationSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function aggregate(Criteria $criteria, ApplicationContext $context): AggregatorResult
    {
        $result = $this->aggregator->aggregate(ApplicationDefinition::class, $criteria, $context);

        $event = new ApplicationAggregationResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function searchIds(Criteria $criteria, ApplicationContext $context): IdSearchResult
    {
        $result = $this->searcher->search(ApplicationDefinition::class, $criteria, $context);

        $event = new ApplicationIdSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function readBasic(array $ids, ApplicationContext $context): ApplicationBasicCollection
    {
        /** @var ApplicationBasicCollection $entities */
        $entities = $this->reader->readBasic(ApplicationDefinition::class, $ids, $context);

        $event = new ApplicationBasicLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function readDetail(array $ids, ApplicationContext $context): ApplicationDetailCollection
    {

        /** @var ApplicationDetailCollection $entities */
        $entities = $this->reader->readDetail(ApplicationDefinition::class, $ids, $context);

        $event = new ApplicationDetailLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;                
                
    }


    public function update(array $data, ApplicationContext $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->update(ApplicationDefinition::class, $data, WriteContext::createFromApplicationContext($context));
        $event = GenericWrittenEvent::createWithWrittenEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function upsert(array $data, ApplicationContext $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->upsert(ApplicationDefinition::class, $data, WriteContext::createFromApplicationContext($context));
        $event = GenericWrittenEvent::createWithWrittenEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function create(array $data, ApplicationContext $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->insert(ApplicationDefinition::class, $data, WriteContext::createFromApplicationContext($context));
        $event = GenericWrittenEvent::createWithWrittenEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function delete(array $ids, ApplicationContext $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->delete(ApplicationDefinition::class, $ids, WriteContext::createFromApplicationContext($context));
        $event = GenericWrittenEvent::createWithDeletedEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function createVersion(string $id, ApplicationContext $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->versionManager->createVersion(ApplicationDefinition::class, $id, WriteContext::createFromApplicationContext($context), $name, $versionId);
    }

    public function merge(string $versionId, ApplicationContext $context): void
    {
        $this->versionManager->merge($versionId, WriteContext::createFromApplicationContext($context));
    }
}