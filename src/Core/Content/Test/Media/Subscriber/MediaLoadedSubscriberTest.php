<?php declare(strict_types=1);

namespace Shopware\Core\Content\Test\Media\Subscriber;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailStruct;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Media\MediaStruct;
use Shopware\Core\Content\Media\Subscriber\MediaLoadedSubscriber;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class MediaLoadedSubscriberTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testExtensionSubscribesToMediaLoaded(): void
    {
        static::assertCount(2, MediaLoadedSubscriber::getSubscribedEvents()['media.loaded']);
    }

    public function testItAddsUrl(): void
    {
        $subscriber = $this->getContainer()->get(MediaLoadedSubscriber::class);
        $context = Context::createDefaultContext();

        $mediaId = '34556f108ab14969a0dcf9d9522fd7df';
        $mimeType = 'image/png';

        $mediaStruct = new MediaStruct();
        $mediaStruct->setId($mediaId);
        $mediaStruct->setMimeType($mimeType);
        $mediaStruct->setFileExtension('png');
        $mediaStruct->setFileName($mediaId . '-134578345');
        $mediaStruct->setThumbnails(new MediaThumbnailCollection());

        $mediaLoadedEvent = new EntityLoadedEvent(MediaDefinition::class, new EntityCollection([$mediaStruct]), $context);
        $subscriber->addUrls($mediaLoadedEvent);

        static::assertStringEndsWith(
            $mediaStruct->getFileName() . '.' . $mediaStruct->getFileExtension(),
            $mediaStruct->getUrl());
        static::assertEquals([], $mediaStruct->getThumbnails()->getElements());
    }

    public function testItAddsThumbnailUrl(): void
    {
        $subscriber = $this->getContainer()->get(MediaLoadedSubscriber::class);
        $context = Context::createDefaultContext();

        $mediaId = '34556f108ab14969a0dcf9d9522fd7df';
        $mimeType = 'image/png';

        $thumbnailStruct = new MediaThumbnailStruct();
        $thumbnailStruct->setId($mediaId);
        $thumbnailStruct->setHeight(100);
        $thumbnailStruct->setWidth(100);
        $thumbnailStruct->setHighDpi(false);
        $mediaStruct = new MediaStruct();
        $mediaStruct->setId($mediaId);
        $mediaStruct->setMimeType($mimeType);
        $mediaStruct->setFileExtension('png');
        $mediaStruct->setFileName($mediaId . '-134578345');
        $mediaStruct->setThumbnails(new MediaThumbnailCollection([$thumbnailStruct]));

        $mediaLoadedEvent = new EntityLoadedEvent(MediaDefinition::class, new EntityCollection([$mediaStruct]), $context);
        $subscriber->addUrls($mediaLoadedEvent);

        static::assertStringEndsWith(
            $mediaStruct->getFileName() . '_100x100.' . $mediaStruct->getFileExtension(),
            $mediaStruct->getThumbnails()->get($thumbnailStruct->getId())->getUrl());
    }
}
