<?php

namespace App\Command;

use App\Entity\ResetPasswordRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:purge-stale-data',
    description: 'Supprime les comptes inactifs et les tokens expirés (RGPD)',
)]
class PurgeStaleDataCommand extends Command
{
    private const DEFAULT_INACTIVITY_MONTHS = 24;

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'months',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Durée d\'inactivité en mois avant suppression',
            self::DEFAULT_INACTIVITY_MONTHS,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $months = (int) $input->getOption('months');

        $io->title('Purge des données obsolètes (RGPD)');

        // 1. Suppression des comptes inactifs
        $staleUsers = $this->userRepository->findStaleUsers($months);

        if (empty($staleUsers)) {
            $io->info("Aucun compte inactif depuis plus de {$months} mois.");
        } else {
            $io->section("Comptes inactifs depuis plus de {$months} mois : " . count($staleUsers));

            foreach ($staleUsers as $user) {
                $resetRequests = $this->em->getRepository(ResetPasswordRequest::class)
                    ->findBy(['user' => $user]);

                foreach ($resetRequests as $request) {
                    $this->em->remove($request);
                }

                $io->text("  Suppression : {$user->getEmail()} (dernière activité : " .
                    ($user->getLastLogin()?->format('Y-m-d') ?? $user->getRegistrationDate()?->format('Y-m-d')) . ')');

                $this->em->remove($user);
            }

            $this->em->flush();
            $io->success(count($staleUsers) . ' compte(s) supprimé(s).');
        }

        // 2. Suppression des tokens de réinitialisation expirés
        $expiredTokens = $this->em->getRepository(ResetPasswordRequest::class)
            ->createQueryBuilder('r')
            ->where('r.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        if (!empty($expiredTokens)) {
            foreach ($expiredTokens as $token) {
                $this->em->remove($token);
            }
            $this->em->flush();
            $io->success(count($expiredTokens) . ' token(s) expiré(s) supprimé(s).');
        } else {
            $io->info('Aucun token expiré à supprimer.');
        }

        return Command::SUCCESS;
    }
}
