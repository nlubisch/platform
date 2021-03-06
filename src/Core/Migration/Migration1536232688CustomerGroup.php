<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1536232688CustomerGroup extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232688;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `customer_group` (
              `id` binary(16) NOT NULL,
              `display_gross` tinyint(1) NOT NULL DEFAULT \'1\',
              `input_gross` tinyint(1) NOT NULL DEFAULT \'1\',
              `has_global_discount` tinyint(1) NOT NULL DEFAULT \'0\',
              `percentage_global_discount` double NULL,
              `minimum_order_amount` double NULL,
              `minimum_order_amount_surcharge` double NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
