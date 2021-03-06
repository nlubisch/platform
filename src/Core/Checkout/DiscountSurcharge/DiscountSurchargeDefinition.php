<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\DiscountSurcharge;

use Shopware\Core\Checkout\DiscountSurcharge\Aggregate\DiscountSurchargeTranslation\DiscountSurchargeTranslationDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ObjectField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\Required;

class DiscountSurchargeDefinition extends EntityDefinition
{
    public static function getEntityName(): string
    {
        return 'discount_surcharge';
    }

    public static function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            new TranslatedField('name'),
            (new FkField('rule_id', 'ruleId', RuleDefinition::class))->setFlags(new Required()),
            (new ObjectField('filter_rule', 'filterRule'))->setFlags(new Required()),
            (new StringField('type', 'type'))->setFlags(new Required()),
            (new FloatField('amount', 'amount'))->setFlags(new Required()),
            new CreatedAtField(),
            new UpdatedAtField(),
            (new TranslationsAssociationField(DiscountSurchargeTranslationDefinition::class))->setFlags(new Required(), new CascadeDelete()),
            new ManyToOneAssociationField('rule', 'rule_id', RuleDefinition::class, true),
        ]);
    }

    public static function getCollectionClass(): string
    {
        return DiscountSurchargeCollection::class;
    }

    public static function getStructClass(): string
    {
        return DiscountSurchargeStruct::class;
    }
}
