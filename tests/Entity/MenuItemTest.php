<?php

namespace App\Tests\Entity;

use App\Entity\InfoPage;
use App\Entity\MenuItem;
use PHPUnit\Framework\TestCase;

class MenuItemTest extends TestCase
{
    private MenuItem $item;

    protected function setUp(): void
    {
        $this->item = new MenuItem();
    }

    public function testIsActiveDefaultsToTrue(): void
    {
        $this->assertTrue($this->item->isActive());
    }

    public function testSetIsActive(): void
    {
        $this->item->setIsActive(false);
        $this->assertFalse($this->item->isActive());
    }

    public function testPositionDefaultsToZero(): void
    {
        $this->assertSame(0, $this->item->getPosition());
    }

    public function testGetUrlReturnsUrlWhenNoInfoPage(): void
    {
        $this->item->setUrl('https://example.com');
        $this->assertSame('https://example.com', $this->item->getUrl());
    }

    public function testGetUrlReturnsNullWhenInfoPageIsSet(): void
    {
        $infoPage = new InfoPage();
        $this->item->setUrl('https://example.com');
        $this->item->setInfoPage($infoPage);

        // Quand une infoPage est définie, getUrl() retourne null (géré en Twig)
        $this->assertNull($this->item->getUrl());
    }

    public function testGetRawUrlReturnsStoredUrl(): void
    {
        $this->item->setUrl('https://example.com');
        $infoPage = new InfoPage();
        $this->item->setInfoPage($infoPage);

        // getRawUrl() retourne toujours l'URL brute, même si une infoPage est définie
        $this->assertSame('https://example.com', $this->item->getRawUrl());
    }

    public function testAddChild(): void
    {
        $child = new MenuItem();
        $this->item->addChild($child);

        $this->assertTrue($this->item->getChildren()->contains($child));
        $this->assertSame($this->item, $child->getParent());
    }

    public function testAddChildNotDuplicated(): void
    {
        $child = new MenuItem();
        $this->item->addChild($child);
        $this->item->addChild($child);

        $this->assertCount(1, $this->item->getChildren());
    }

    public function testRemoveChild(): void
    {
        $child = new MenuItem();
        $this->item->addChild($child);
        $this->item->removeChild($child);

        $this->assertFalse($this->item->getChildren()->contains($child));
    }

    public function testChildrenEmptyByDefault(): void
    {
        $this->assertCount(0, $this->item->getChildren());
    }

    public function testParentIsNullByDefault(): void
    {
        $this->assertNull($this->item->getParent());
    }

    public function testToStringReturnsLabel(): void
    {
        $this->item->setLabel('Accueil');
        $this->assertSame('Accueil', (string) $this->item);
    }

    public function testToStringWithNoLabelReturnsEmptyString(): void
    {
        $this->assertSame('', (string) $this->item);
    }
}
