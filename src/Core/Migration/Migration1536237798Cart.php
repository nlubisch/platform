<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1536237798Cart extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536237798;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `cart` (
              `token` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
              `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
              `cart` JSON NOT NULL,
              `price` float NOT NULL,
              `line_item_count` varchar(42) COLLATE utf8mb4_unicode_ci NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `shipping_method_id` binary(16) NOT NULL,
              `payment_method_id` binary(16) NOT NULL,
              `country_id` binary(16) NOT NULL,
              `customer_id` binary(16) NULL,
              `sales_channel_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              PRIMARY KEY (`token`),
              CONSTRAINT `json.cart` CHECK (JSON_VALID(`cart`)),
              CONSTRAINT `fk.cart.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cart.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cart.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cart.payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cart.shipping_method_id` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cart.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
