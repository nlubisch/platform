<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1543492692CreateMediaFolder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1543492692;
    }

    public function update(Connection $connection): void
    {
        $connection->exec('
            CREATE TABLE `media_folder` (
              `id` BINARY(16) NOT NULL,
              `parent_id` BINARY(16),
              `child_count` int(11) unsigned NOT NULL DEFAULT \'0\',
              `media_folder_configuration_id` BINARY(16),
              `configuration` BINARY(16),
              `use_parent_configuration` TINYINT(1) DEFAULT \'1\',
              `created_at` DATETIME(3),
              `updated_at` DATETIME(3),
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.media_folder.parent_id` FOREIGN KEY (`parent_id`) 
                REFERENCES `media_folder` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // no destructive changes
    }
}
