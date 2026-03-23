<?php

namespace App\Controller\Common;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mon-compte')]
class AccountExportController extends AbstractController
{
    #[Route('/export', name: 'account_export', methods: ['GET'])]
    public function export(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $data = [
            'exported_at' => (new \DateTime())->format(\DateTime::ATOM),
            'données_personnelles' => [
                'email'              => $user->getEmail(),
                'nom_utilisateur'    => $user->getUserName(),
                'date_de_naissance'  => $user->getBirthDate()?->format('Y-m-d'),
                'date_inscription'   => $user->getRegistrationDate()?->format(\DateTime::ATOM),
                'dernière_connexion' => $user->getLastLogin()?->format(\DateTime::ATOM),
                'rôles'              => $user->getRoles(),
            ],
        ];

        $response = new JsonResponse($data, Response::HTTP_OK, [], false);
        $response->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $response->headers->set('Content-Disposition', 'attachment; filename="mes-donnees-cesiezen.json"');

        return $response;
    }
}
