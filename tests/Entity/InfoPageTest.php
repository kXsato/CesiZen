<?php

namespace App\Tests\Entity;

use App\Entity\InfoPage;
use App\Enum\InfoPageCategory;
use PHPUnit\Framework\TestCase;

class InfoPageTest extends TestCase
{
    private InfoPage $page;

    protected function setUp(): void
    {
        $this->page = new InfoPage();
    }

    public function testDefaultCategoryIsPage(): void
    {
        $this->assertSame(InfoPageCategory::PAGE, $this->page->getCategory());
    }

    public function testIsPublishedDefaultsToFalse(): void
    {
        $this->assertFalse($this->page->isPublished());
    }

    public function testInitOnCreateSetsCreatedAt(): void
    {
        $before = new \DateTime();
        $this->page->setTitle('Test');
        $this->page->initOnCreate();
        $after = new \DateTime();

        $createdAt = $this->page->getCreatedAt();
        $this->assertNotNull($createdAt);
        $this->assertGreaterThanOrEqual($before, $createdAt);
        $this->assertLessThanOrEqual($after, $createdAt);
    }

    public function testInitOnCreateGeneratesSlugFromTitle(): void
    {
        $this->page->setTitle('Ma Page Test');
        $this->page->initOnCreate();

        $this->assertSame('ma-page-test', $this->page->getSlug());
    }

    public function testInitOnUpdateSetsUpdatedAt(): void
    {
        $this->page->setTitle('Titre');
        $before = new \DateTime();
        $this->page->initOnUpdate();
        $after = new \DateTime();

        $updatedAt = $this->page->getUpdatedAt();
        $this->assertNotNull($updatedAt);
        $this->assertGreaterThanOrEqual($before, $updatedAt);
        $this->assertLessThanOrEqual($after, $updatedAt);
    }

    public function testInitOnUpdateRegeneratesSlug(): void
    {
        $this->page->setTitle('Ancien Titre');
        $this->page->initOnCreate();

        $this->page->setTitle('Nouveau Titre');
        $this->page->initOnUpdate();

        $this->assertSame('nouveau-titre', $this->page->getSlug());
    }

    public function testSlugNormalizesAccents(): void
    {
        $this->page->setTitle('Politique de confidentialité');
        $this->page->initOnCreate();

        $this->assertSame('politique-de-confidentialite', $this->page->getSlug());
    }

    public function testSlugNormalizesUppercase(): void
    {
        $this->page->setTitle('MENTIONS LÉGALES');
        $this->page->initOnCreate();

        $this->assertSame('mentions-legales', $this->page->getSlug());
    }

    public function testToStringReturnsTitle(): void
    {
        $this->page->setTitle('Mon Titre');
        $this->assertSame('Mon Titre', (string) $this->page);
    }

    public function testToStringWithNoTitleReturnsEmptyString(): void
    {
        $this->assertSame('', (string) $this->page);
    }

    public function testSetCategory(): void
    {
        $this->page->setCategory(InfoPageCategory::LEGAL);
        $this->assertSame(InfoPageCategory::LEGAL, $this->page->getCategory());
    }

    public function testCreatedAtIsNullBeforeInit(): void
    {
        $this->assertNull($this->page->getCreatedAt());
    }

    public function testUpdatedAtIsNullByDefault(): void
    {
        $this->assertNull($this->page->getUpdatedAt());
    }
}
