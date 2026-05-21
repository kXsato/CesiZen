<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    private UserChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new UserChecker();
    }

    public function testCheckPreAuthPassesForActivatedAccount(): void
    {
        $user = new User();
        $user->setIsAccountActivated(true);

        // Ne doit pas lever d'exception
        $this->checker->checkPreAuth($user);
        $this->assertTrue(true);
    }

    public function testCheckPreAuthThrowsForDeactivatedAccount(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre compte a été désactivé. Vous pouvez soumettre une demande de réactivation.');

        $user = new User();
        $user->setIsAccountActivated(false);

        $this->checker->checkPreAuth($user);
    }

    public function testCheckPreAuthIgnoresNonUserInstance(): void
    {
        $otherUser = $this->createMock(UserInterface::class);

        // Ne doit pas lever d'exception pour un utilisateur non-App\Entity\User
        $this->checker->checkPreAuth($otherUser);
        $this->assertTrue(true);
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $user = new User();

        // Ne doit pas lever d'exception
        $this->checker->checkPostAuth($user);
        $this->assertTrue(true);
    }
}
