<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\Struct\Collection;

class EntityCollection extends Collection
{
    public function add(Entity $entity): void
    {
        $class = $this->getExpectedClass();
        if (!$entity instanceof $class) {
            throw new \InvalidArgumentException(
                sprintf('Expected collection element of type %s got %s', $class, \get_class($entity))
            );
        }

        $this->elements[$entity->getUniqueIdentifier()] = $entity;
    }

    /**
     * @return mixed|null
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->elements[$id];
        }

        return null;
    }

    public function getIds(): array
    {
        return $this->fmap(function (Entity $entity) {
            return $entity->getUniqueIdentifier();
        });
    }

    public function filterByProperty(string $property, $value)
    {
        return $this->filter(
            function (Entity $struct) use ($property, $value) {
                return $struct->get($property) === $value;
            }
        );
    }

    public function merge(self $collection): void
    {
        /** @var Entity $entity */
        foreach ($collection as $entity) {
            if ($this->has($entity->getUniqueIdentifier())) {
                continue;
            }
            $this->add($entity);
        }
    }

    public function remove(string $id): void
    {
        parent::doRemoveByKey($id);
    }

    public function getList(array $ids)
    {
        return new static(array_intersect_key($this->elements, array_flip($ids)));
    }

    public function sortByIdArray(array $ids): void
    {
        $sorted = [];
        foreach ($ids as $id) {
            if (array_key_exists($id, $this->elements)) {
                $sorted[$id] = $this->elements[$id];
            }
        }
        $this->elements = $sorted;
    }

    protected function getExpectedClass(): string
    {
        return Entity::class;
    }
}
