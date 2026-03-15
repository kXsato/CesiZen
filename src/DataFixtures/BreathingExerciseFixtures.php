<?php

namespace App\DataFixtures;

use App\Entity\BreathingExercise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class BreathingExerciseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile(__DIR__ . '/../../config/fixtures/breathing_exercises.yaml');

        foreach ($data['breathing_exercises'] as $item) {
            $exercise = (new BreathingExercise())
                ->setName($item['name'])
                ->setInspirationDuration($item['inspirationDuration'])
                ->setApneaDuration($item['apneaDuration'])
                ->setExpirationDuration($item['expirationDuration'])
                ->setIsActive($item['isActive'])
                ->setDescription($item['description'] ?? null);

            $manager->persist($exercise);
        }

        $manager->flush();
    }
}
