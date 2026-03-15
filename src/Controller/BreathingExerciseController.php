<?php

namespace App\Controller;

use App\Entity\BreathingExercise;
use App\Repository\BreathingExerciseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BreathingExerciseController extends AbstractController
{
    #[Route('/exercices', name: 'breathing_index')]
    public function index(BreathingExerciseRepository $repository): Response
    {
        return $this->render('breathing/index.html.twig', [
            'exercises' => $repository->findActive(),
        ]);
    }

    #[Route('/exercices/{id}', name: 'breathing_exercise', requirements: ['id' => '\d+'])]
    public function exercise(BreathingExercise $exercise): Response
    {
        if (!$exercise->isActive()) {
            throw $this->createNotFoundException();
        }

        return $this->render('breathing/exercise.html.twig', [
            'exercise' => $exercise,
        ]);
    }
}
