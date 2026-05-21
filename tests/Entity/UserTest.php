<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetUserIdentifier(): void
    {
        $this->user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $this->user->getUserIdentifier());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testGetRolesWithAdminRole(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testSetRolesFiltersOutRoleUserFromStorage(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $roles = $this->user->getRoles();

        // ROLE_USER ne doit pas être dupliqué
        $this->assertCount(2, array_unique($roles));
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRoleDefaultsToRoleUser(): void
    {
        $this->assertSame('ROLE_USER', $this->user->getRole());
    }

    public function testSetRoleAdmin(): void
    {
        $this->user->setRole('ROLE_ADMIN');
        $this->assertSame('ROLE_ADMIN', $this->user->getRole());
    }

    public function testSetRoleUserClearsSpecialRoles(): void
    {
        $this->user->setRole('ROLE_ADMIN');
        $this->user->setRole('ROLE_USER');
        $this->assertSame('ROLE_USER', $this->user->getRole());
    }

    public function testIsAccountActivatedDefaultsToTrue(): void
    {
        $this->assertTrue($this->user->isAccountActivated());
    }

    public function testSetIsAccountActivated(): void
    {
        $this->user->setIsAccountActivated(false);
        $this->assertFalse($this->user->isAccountActivated());
    }

    public function testIsReactivationRequestedDefaultsToFalse(): void
    {
        $this->assertFalse($this->user->isReactivationRequested());
    }

    public function testSetReactivationRequested(): void
    {
        $this->user->setReactivationRequested(true);
        $this->assertTrue($this->user->isReactivationRequested());
    }

    public function testToStringReturnsUserName(): void
    {
        $this->user->setUserName('JohnDoe');
        $this->user->setEmail('john@example.com');
        $this->assertSame('JohnDoe', (string) $this->user);
    }

    public function testToStringFallsBackToEmail(): void
    {
        $this->user->setEmail('john@example.com');
        $this->assertSame('john@example.com', (string) $this->user);
    }

    public function testInitDatesOnCreateSetsRegistrationDate(): void
    {
        $before = new \DateTime();
        $this->user->initDatesOnCreate();
        $after = new \DateTime();

        $registrationDate = $this->user->getRegistrationDate();
        $this->assertNotNull($registrationDate);
        $this->assertGreaterThanOrEqual($before, $registrationDate);
        $this->assertLessThanOrEqual($after, $registrationDate);
    }

    public function testPlainPasswordIsNullByDefault(): void
    {
        $this->assertNull($this->user->getPlainPassword());
    }

    public function testSetPlainPassword(): void
    {
        $this->user->setPlainPassword('secret123');
        $this->assertSame('secret123', $this->user->getPlainPassword());
    }

    public function testSetPlainPasswordToNull(): void
    {
        $this->user->setPlainPassword('secret123');
        $this->user->setPlainPassword(null);
        $this->assertNull($this->user->getPlainPassword());
    }
}
