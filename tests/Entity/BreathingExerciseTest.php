<?php

namespace App\Tests\Entity;

use App\Entity\BreathingExercise;
use PHPUnit\Framework\TestCase;

class BreathingExerciseTest extends TestCase
{
    private BreathingExercise $exercise;

    protected function setUp(): void
    {
        $this->exercise = new BreathingExercise();
    }

    public function testGetTotalDurationWithDefaultValues(): void
    {
        $this->assertSame(0, $this->exercise->getTotalDuration());
    }

    public function testGetTotalDuration(): void
    {
        $this->exercise
            ->setInspirationDuration(4)
            ->setApneaDuration(2)
            ->setExpirationDuration(6);

        $this->assertSame(12, $this->exercise->getTotalDuration());
    }

    public function testGetTotalDurationWithoutApnea(): void
    {
        $this->exercise
            ->setInspirationDuration(5)
            ->setApneaDuration(0)
            ->setExpirationDuration(5);

        $this->assertSame(10, $this->exercise->getTotalDuration());
    }

    public function testIsActiveDefaultsToTrue(): void
    {
        $this->assertTrue($this->exercise->isActive());
    }

    public function testSetIsActive(): void
    {
        $this->exercise->setIsActive(false);
        $this->assertFalse($this->exercise->isActive());
    }

    public function testToStringReturnsName(): void
    {
        $this->exercise->setName('Cohérence cardiaque');
        $this->assertSame('Cohérence cardiaque', (string) $this->exercise);
    }

    public function testToStringWithNoNameReturnsEmptyString(): void
    {
        $this->assertSame('', (string) $this->exercise);
    }

    public function testSettersReturnStatic(): void
    {
        $result = $this->exercise->setInspirationDuration(4);
        $this->assertSame($this->exercise, $result);

        $result = $this->exercise->setApneaDuration(2);
        $this->assertSame($this->exercise, $result);

        $result = $this->exercise->setExpirationDuration(6);
        $this->assertSame($this->exercise, $result);
    }
}
