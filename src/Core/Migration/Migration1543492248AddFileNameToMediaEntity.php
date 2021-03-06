<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1543492248AddFileNameToMediaEntity extends MigrationStep
{
    public const FORWARD_TRIGGER_NAME = 'trigger_1539609364_add_filename_to_media';

    public function getCreationTimestamp(): int
    {
        return 1543492248;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            ALTER TABLE `media`
            ADD COLUMN `file_name` LONGTEXT COLLATE utf8mb4_unicode_ci NULL AFTER `meta_data`;
        ');

        $connection->executeQuery('
            UPDATE `media`
            SET `file_name` = HEX(`id`);
        ');

        $this->addForwardTrigger(
            $connection,
            self::FORWARD_TRIGGER_NAME,
            'media',
            'BEFORE',
            'INSERT',
            'SET NEW.`file_name` = HEX(NEW.`id`)'
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        $this->removeTrigger($connection, self::FORWARD_TRIGGER_NAME);
    }
}
