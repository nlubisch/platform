<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Field;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;

class ManyToManyAssociationField extends Field implements AssociationInterface
{
    use AssociationTrait;

    /**
     * @var string
     */
    private $referenceDefinition;

    /**
     * @var string
     */
    private $mappingDefinition;

    /**
     * @var string
     */
    private $mappingLocalColumn;

    /**
     * @var string
     */
    private $mappingReferenceColumn;

    /**
     * @var string
     */
    private $sourceColumn;

    /**
     * @var string
     */
    private $referenceColumn;

    public function __construct(
        string $propertyName,
        string $referenceClass,
        string $mappingDefinition,
        bool $loadInBasic,
        string $mappingLocalColumn,
        string $mappingReferenceColumn,
        string $sourceColumn = 'id',
        string $referenceColumn = 'id'
    ) {
        parent::__construct($propertyName);
        $this->referenceDefinition = $referenceClass;
        $this->referenceClass = $mappingDefinition;
        $this->loadInBasic = $loadInBasic;
        $this->mappingDefinition = $mappingDefinition;
        $this->mappingLocalColumn = $mappingLocalColumn;
        $this->mappingReferenceColumn = $mappingReferenceColumn;
        $this->sourceColumn = $sourceColumn;
        $this->referenceColumn = $referenceColumn;
    }

    /**
     * @return string|EntityDefinition
     */
    public function getReferenceDefinition(): string
    {
        return $this->referenceDefinition;
    }

    /**
     * @return string|EntityDefinition
     */
    public function getMappingDefinition(): string
    {
        return $this->mappingDefinition;
    }

    public function getMappingLocalColumn(): string
    {
        return $this->mappingLocalColumn;
    }

    public function getMappingReferenceColumn(): string
    {
        return $this->mappingReferenceColumn;
    }

    public function getLocalField(): string
    {
        return $this->sourceColumn;
    }

    public function getReferenceField(): string
    {
        return $this->referenceColumn;
    }
}
