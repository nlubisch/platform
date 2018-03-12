<?php declare(strict_types=1);

namespace Shopware\Api\Language\Definition;

use Shopware\Api\Category\Definition\CategoryTranslationDefinition;
use Shopware\Api\Country\Definition\CountryAreaTranslationDefinition;
use Shopware\Api\Country\Definition\CountryStateTranslationDefinition;
use Shopware\Api\Country\Definition\CountryTranslationDefinition;
use Shopware\Api\Currency\Definition\CurrencyTranslationDefinition;
use Shopware\Api\Customer\Definition\CustomerGroupTranslationDefinition;
use Shopware\Api\Entity\EntityDefinition;
use Shopware\Api\Entity\EntityExtensionInterface;
use Shopware\Api\Entity\Field\DateField;
use Shopware\Api\Entity\Field\FkField;
use Shopware\Api\Entity\Field\IdField;
use Shopware\Api\Entity\Field\ManyToOneAssociationField;
use Shopware\Api\Entity\Field\OneToManyAssociationField;
use Shopware\Api\Entity\Field\StringField;
use Shopware\Api\Entity\Field\TranslationsAssociationField;
use Shopware\Api\Entity\FieldCollection;
use Shopware\Api\Entity\Write\Flag\CascadeDelete;
use Shopware\Api\Entity\Write\Flag\PrimaryKey;
use Shopware\Api\Entity\Write\Flag\Required;
use Shopware\Api\Entity\Write\Flag\WriteOnly;
use Shopware\Api\Language\Collection\LanguageBasicCollection;
use Shopware\Api\Language\Collection\LanguageDetailCollection;
use Shopware\Api\Language\Event\Language\LanguageDeletedEvent;
use Shopware\Api\Language\Event\Language\LanguageWrittenEvent;
use Shopware\Api\Language\Repository\LanguageRepository;
use Shopware\Api\Language\Struct\LanguageBasicStruct;
use Shopware\Api\Language\Struct\LanguageDetailStruct;
use Shopware\Api\Listing\Definition\ListingFacetTranslationDefinition;
use Shopware\Api\Listing\Definition\ListingSortingTranslationDefinition;
use Shopware\Api\Locale\Definition\LocaleDefinition;
use Shopware\Api\Locale\Definition\LocaleTranslationDefinition;
use Shopware\Api\Mail\Definition\MailTranslationDefinition;
use Shopware\Api\Media\Definition\MediaAlbumTranslationDefinition;
use Shopware\Api\Media\Definition\MediaTranslationDefinition;
use Shopware\Api\Order\Definition\OrderStateTranslationDefinition;
use Shopware\Api\Payment\Definition\PaymentMethodTranslationDefinition;
use Shopware\Api\Product\Definition\ProductManufacturerTranslationDefinition;
use Shopware\Api\Product\Definition\ProductTranslationDefinition;
use Shopware\Api\Shipping\Definition\ShippingMethodTranslationDefinition;
use Shopware\Api\Tax\Definition\TaxAreaRuleTranslationDefinition;
use Shopware\Api\Unit\Definition\UnitTranslationDefinition;

class LanguageDefinition extends EntityDefinition
{
    /**
     * @var FieldCollection
     */
    protected static $primaryKeys;

    /**
     * @var FieldCollection
     */
    protected static $fields;

    /**
     * @var EntityExtensionInterface[]
     */
    protected static $extensions = [];

    public static function getEntityName(): string
    {
        return 'language';
    }

    public static function getFields(): FieldCollection
    {
        if (self::$fields) {
            return self::$fields;
        }

        self::$fields = new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            new FkField('parent_id', 'parentId', LanguageDefinition::class),
            (new FkField('locale_id', 'localeId', LocaleDefinition::class))->setFlags(new Required()),
            (new StringField('name', 'name'))->setFlags(new Required()),
            (new IdField('locale_version_id', 'localeVersionId'))->setFlags(new Required()),
            new DateField('created_at', 'createdAt'),
            new DateField('updated_at', 'updatedAt'),
            new ManyToOneAssociationField('parent', 'parent_id', LanguageDefinition::class, false),
            new ManyToOneAssociationField('locale', 'locale_id', LocaleDefinition::class, false),
            (new OneToManyAssociationField('children', LanguageDefinition::class, 'parent_id', false, 'id'))->setFlags(new CascadeDelete()),
            (new TranslationsAssociationField('categoryTranslations', CategoryTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('countryAreaTranslations', CountryAreaTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('countryStateTranslations', CountryStateTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('countryTranslations', CountryTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('currencyTranslations', CurrencyTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('customerGroupTranslations', CustomerGroupTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('listingFacetTranslations', ListingFacetTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('listingSortingTranslations', ListingSortingTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('localeTranslations', LocaleTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('mailTranslations', MailTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('mediaAlbumTranslations', MediaAlbumTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('mediaTranslations', MediaTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('orderStateTranslations', OrderStateTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('paymentMethodTranslations', PaymentMethodTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('productManufacturerTranslations', ProductManufacturerTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('productTranslations', ProductTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('shippingMethodTranslations', ShippingMethodTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('taxAreaRuleTranslations', TaxAreaRuleTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
            (new TranslationsAssociationField('unitTranslations', UnitTranslationDefinition::class, 'language_id', false, 'id'))->setFlags(new WriteOnly(), new CascadeDelete()),
        ]);

        foreach (self::$extensions as $extension) {
            $extension->extendFields(self::$fields);
        }

        return self::$fields;
    }

    public static function getRepositoryClass(): string
    {
        return LanguageRepository::class;
    }

    public static function getBasicCollectionClass(): string
    {
        return LanguageBasicCollection::class;
    }

    public static function getDeletedEventClass(): string
    {
        return LanguageDeletedEvent::class;
    }

    public static function getWrittenEventClass(): string
    {
        return LanguageWrittenEvent::class;
    }

    public static function getBasicStructClass(): string
    {
        return LanguageBasicStruct::class;
    }

    public static function getTranslationDefinitionClass(): ?string
    {
        return null;
    }

    public static function getDetailStructClass(): string
    {
        return LanguageDetailStruct::class;
    }

    public static function getDetailCollectionClass(): string
    {
        return LanguageDetailCollection::class;
    }
}