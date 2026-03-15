<?php

namespace App\Repository;

use App\Entity\BreathingExercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BreathingExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BreathingExercise::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = true')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
