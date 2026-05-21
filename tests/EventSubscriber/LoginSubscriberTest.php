<?php

namespace App\Tests\EventSubscriber;

use App\Entity\User;
use App\EventSubscriber\LoginSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = LoginSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(LoginSuccessEvent::class, $events);
        $this->assertSame('onLoginSuccess', $events[LoginSuccessEvent::class]);
    }

    public function testOnLoginSuccessUpdatesLastLogin(): void
    {
        $user = new User();
        $this->assertNull($user->getLastLogin());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $event = $this->createMock(LoginSuccessEvent::class);
        $event->method('getUser')->willReturn($user);

        $subscriber = new LoginSubscriber($em);
        $before = new \DateTime();
        $subscriber->onLoginSuccess($event);
        $after = new \DateTime();

        $lastLogin = $user->getLastLogin();
        $this->assertNotNull($lastLogin);
        $this->assertGreaterThanOrEqual($before, $lastLogin);
        $this->assertLessThanOrEqual($after, $lastLogin);
    }

    public function testOnLoginSuccessIgnoresNonAppUser(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $nonUser = $this->createMock(UserInterface::class);

        $event = $this->createMock(LoginSuccessEvent::class);
        $event->method('getUser')->willReturn($nonUser);

        $subscriber = new LoginSubscriber($em);
        $subscriber->onLoginSuccess($event);
    }
}
