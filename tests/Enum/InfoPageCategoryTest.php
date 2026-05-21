<?php

namespace App\Tests\Enum;

use App\Enum\InfoPageCategory;
use PHPUnit\Framework\TestCase;

class InfoPageCategoryTest extends TestCase
{
    public function testLabelForLegal(): void
    {
        $this->assertSame('Page légale', InfoPageCategory::LEGAL->label());
    }

    public function testLabelForPage(): void
    {
        $this->assertSame("Page d'information", InfoPageCategory::PAGE->label());
    }

    public function testLabelForPageRoot(): void
    {
        $this->assertSame('Page racine (/{slug})', InfoPageCategory::PAGE_ROOT->label());
    }

    public function testValues(): void
    {
        $this->assertSame('legal', InfoPageCategory::LEGAL->value);
        $this->assertSame('page', InfoPageCategory::PAGE->value);
        $this->assertSame('root', InfoPageCategory::PAGE_ROOT->value);
    }

    public function testFromValue(): void
    {
        $this->assertSame(InfoPageCategory::LEGAL, InfoPageCategory::from('legal'));
        $this->assertSame(InfoPageCategory::PAGE, InfoPageCategory::from('page'));
        $this->assertSame(InfoPageCategory::PAGE_ROOT, InfoPageCategory::from('root'));
    }

    public function testTryFromWithInvalidValueReturnsNull(): void
    {
        $this->assertNull(InfoPageCategory::tryFrom('invalid'));
    }
}
