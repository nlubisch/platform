<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1536234587UserAccessKey extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536234587;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `user_access_key` (
              `id` binary(16) NOT NULL,
              `user_id` binary(16) NOT NULL,
              `write_access` tinyint(1) NOT NULL,
              `access_key` varchar(255) NOT NULL,
              `secret_access_key` varchar(255) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `last_usage_at` datetime(3) NULL,
              PRIMARY KEY (`id`),
              INDEX `idx.user_id_user_tenant_id` (`user_id`),
              INDEX `idx.access_key` (`access_key`),
              CONSTRAINT `fk.user_access_key.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
