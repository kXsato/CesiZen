<?php

namespace App\Tests\Command;

use App\Command\PurgeStaleDataCommand;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PurgeStaleDataCommandTest extends TestCase
{
    /**
     * Crée un mock de QueryBuilder Doctrine retournant les résultats donnés.
     */
    private function mockQueryBuilder(array $results): QueryBuilder
    {
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMockForAbstractClass();
        $query->method('getResult')->willReturn($results);

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'setParameter', 'getQuery'])
            ->getMock();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }

    /**
     * Crée un mock du repository ResetPasswordRequest.
     */
    private function mockTokenRepository(array $staleUserRequests = [], array $expiredTokens = []): EntityRepository
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findBy')->willReturn($staleUserRequests);
        $repo->method('createQueryBuilder')->willReturn($this->mockQueryBuilder($expiredTokens));

        return $repo;
    }

    public function testExecuteWithNoStaleUsersAndNoExpiredTokens(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findStaleUsers')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($this->mockTokenRepository());

        $tester = new CommandTester(new PurgeStaleDataCommand($userRepository, $em));
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Aucun compte inactif', $output);
        $this->assertStringContainsString('Aucun token expiré', $output);
    }

    public function testExecuteDeletesStaleUsers(): void
    {
        $user = new User();
        $user->setEmail('ancien@example.com');
        $user->setRegistrationDate(new \DateTime('-4 years'));

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findStaleUsers')->willReturn([$user]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($this->mockTokenRepository([], []));

        $em->expects($this->once())->method('remove')->with($user);
        $em->expects($this->atLeastOnce())->method('flush');

        $tester = new CommandTester(new PurgeStaleDataCommand($userRepository, $em));
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $output = $tester->getDisplay();
        $this->assertStringContainsString('ancien@example.com', $output);
        $this->assertStringContainsString('1 compte(s) supprimé(s)', $output);
    }

    public function testExecuteDeletesResetRequestsOfStaleUser(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRegistrationDate(new \DateTime('-4 years'));

        $resetRequest = $this->createMock(ResetPasswordRequest::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findStaleUsers')->willReturn([$user]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($this->mockTokenRepository([$resetRequest], []));

        // remove() doit être appelé pour le reset request ET pour le user (2 fois)
        $em->expects($this->exactly(2))->method('remove');

        $tester = new CommandTester(new PurgeStaleDataCommand($userRepository, $em));
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExecuteDeletesExpiredTokens(): void
    {
        $expiredToken = $this->createMock(ResetPasswordRequest::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findStaleUsers')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($this->mockTokenRepository([], [$expiredToken]));

        $em->expects($this->once())->method('remove')->with($expiredToken);
        $em->expects($this->once())->method('flush');

        $tester = new CommandTester(new PurgeStaleDataCommand($userRepository, $em));
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $output = $tester->getDisplay();
        $this->assertStringContainsString('1 token(s) expiré(s) supprimé(s)', $output);
    }

    public function testExecuteWithCustomMonthsOption(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findStaleUsers')
            ->with(12)
            ->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($this->mockTokenRepository());

        $tester = new CommandTester(new PurgeStaleDataCommand($userRepository, $em));
        $tester->execute(['--months' => '12']);

        $this->assertSame(0, $tester->getStatusCode());
        $output = $tester->getDisplay();
        $this->assertStringContainsString('12', $output);
    }
}
