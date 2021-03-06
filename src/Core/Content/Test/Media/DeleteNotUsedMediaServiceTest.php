<?php declare(strict_types=1);

namespace Shopware\Core\Content\Test\Media;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\DataAbstractionLayer\MediaRepository;
use Shopware\Core\Content\Media\DeleteNotUsedMediaService;
use Shopware\Core\Content\Media\MediaProtectionFlags;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Read\ReadCriteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class DeleteNotUsedMediaServiceTest extends TestCase
{
    use IntegrationTestBehaviour, MediaFixtures;

    private const FIXTURE_FILE = __DIR__ . '/fixtures/shopware-logo.png';

    /** @var DeleteNotUsedMediaService */
    private $deleteMediaService;

    /** @var MediaRepository */
    private $mediaRepo;

    /** @var Context */
    private $context;

    public function setUp()
    {
        $this->mediaRepo = $this->getContainer()->get('media.repository');

        $this->context = Context::createDefaultContext();
        $this->context->getWriteProtection()->allow(MediaProtectionFlags::WRITE_META_INFO);

        $this->deleteMediaService = new DeleteNotUsedMediaService($this->mediaRepo);
    }

    public function testCountNotUsedMedia()
    {
        $this->setFixtureContext($this->context);

        $this->getTxt();
        $this->getPngWithoutExtension();
        $this->getMediaWithProduct();
        $this->getMediaWithManufacturer();

        static::assertEquals(2, $this->deleteMediaService->countNotUsedMedia($this->context));
    }

    public function testDeleteNotUsedMedia()
    {
        $this->setFixtureContext($this->context);

        $txt = $this->getTxt();
        $png = $this->getPng();
        $withProduct = $this->getMediaWithProduct();
        $withManufacturer = $this->getMediaWithManufacturer();

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);
        $firstPath = $urlGenerator->getRelativeMediaUrl($txt);
        $secondPath = $urlGenerator->getRelativeMediaUrl($png);
        $thirdPath = $urlGenerator->getRelativeMediaUrl($withProduct);
        $fourthPath = $urlGenerator->getRelativeMediaUrl($withManufacturer);

        $this->getPublicFilesystem()->putStream($firstPath, fopen(self::FIXTURE_FILE, 'r'));
        $this->getPublicFilesystem()->putStream($secondPath, fopen(self::FIXTURE_FILE, 'r'));
        $this->getPublicFilesystem()->putStream($thirdPath, fopen(self::FIXTURE_FILE, 'r'));
        $this->getPublicFilesystem()->putStream($fourthPath, fopen(self::FIXTURE_FILE, 'r'));

        $this->deleteMediaService->deleteNotUsedMedia($this->context);

        $result = $this->mediaRepo->read(
            new ReadCriteria([
                $txt->getId(),
                $png->getId(),
                $withProduct->getId(),
                $withManufacturer->getId(),
            ]), $this->context
        );

        static::assertNull($result->get($txt->getId()));
        static::assertNull($result->get($png->getId()));
        static::assertNotNull($result->get($withProduct->getId()));
        static::assertNotNull($result->get($withManufacturer->getId()));

        static::assertFalse($this->getPublicFilesystem()->has($firstPath));
        static::assertFalse($this->getPublicFilesystem()->has($secondPath));
        static::assertTrue($this->getPublicFilesystem()->has($thirdPath));
        static::assertTrue($this->getPublicFilesystem()->has($fourthPath));
    }
}
