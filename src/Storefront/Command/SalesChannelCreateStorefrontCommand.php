<?php declare(strict_types=1);

namespace Shopware\Storefront\Command;

use Shopware\Core\Defaults;
use Shopware\Core\System\Command\SalesChannelCreateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SalesChannelCreateStorefrontCommand extends SalesChannelCreateCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('sales-channel:create:storefront')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'App URL for storefront')
        ;
    }

    protected function getTypeId(): string
    {
        return Defaults::SALES_CHANNEL_STOREFRONT;
    }

    protected function getSalesChannelConfiguration(InputInterface $input, OutputInterface $output): array
    {
        return [
            'domains' => [
                ['url' => $input->getOption('url'), 'language' => $input->getOption('languageId')],
            ],
        ];
    }
}
