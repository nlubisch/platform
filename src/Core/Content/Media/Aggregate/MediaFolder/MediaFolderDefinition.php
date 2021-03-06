<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\Aggregate\MediaFolder;

use Shopware\Core\Content\Media\Aggregate\MediaFolderConfiguration\MediaFolderConfigurationDefinition;
use Shopware\Core\Content\Media\Aggregate\MediaFolderTranslation\MediaFolderTranslationDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\SearchRanking;

class MediaFolderDefinition extends EntityDefinition
{
    public static function getEntityName(): string
    {
        return 'media_folder';
    }

    public static function isInheritanceAware(): bool
    {
        return true;
    }

    public static function getCollectionClass(): string
    {
        return MediaFolderCollection::class;
    }

    public static function getStructClass(): string
    {
        return MediaFolderStruct::class;
    }

    public static function getDefaults(EntityExistence $existence): array
    {
        if ($existence->exists()) {
            return [];
        }
        if ($existence->isChild()) {
            return [];
        }

        return [
            'useParentConfiguration' => true,
        ];
    }

    protected static function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),

            new BoolField('use_parent_configuration', 'useParentConfiguration'),

            (new FkField('media_folder_configuration_id', 'configurationId', MediaFolderConfigurationDefinition::class))->setFlags(new Inherited()),

            new ParentFkField(self::class),
            new ParentAssociationField(self::class, false),

            new ChildrenAssociationField(self::class),
            new ChildCountField(),

            new OneToManyAssociationField('media', MediaDefinition::class, 'media_folder_id', false),
            (new ManyToOneAssociationField('configuration', 'media_folder_configuration_id', MediaFolderConfigurationDefinition::class, true))->setFlags(new Inherited()),

            (new TranslatedField('name'))->addFlags(new SearchRanking(self::HIGH_SEARCH_RANKING), new Required()),
            new TranslationsAssociationField(MediaFolderTranslationDefinition::class),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
