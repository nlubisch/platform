<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1536234583DiscountSurcharge extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536234583;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `discount_surcharge` (
              `id` BINARY(16) NOT NULL,
              `rule_id` BINARY(16) NOT NULL,
              `filter_rule` JSON NOT NULL,
              `type` VARCHAR(255),
              `amount` FLOAT,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) NULL,
               PRIMARY KEY (`id`),
               CONSTRAINT `json.filter_rule` CHECK (JSON_VALID(`filter_rule`)),
               CONSTRAINT `fk.discount_surcharge.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
