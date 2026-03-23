<?php

namespace App\Controller\Common;

use App\Entity\ResetPasswordRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/mon-compte')]
class AccountDeletionController extends AbstractController
{
    #[Route('/supprimer', name: 'account_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        ResetPasswordHelperInterface $resetPasswordHelper,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('account_delete', $request->getPayload()->getString('_token'))) {
                $this->addFlash('error', 'Token invalide.');
                return $this->redirectToRoute('account_delete');
            }

            $user = $this->getUser();

            $resetPasswordHelper->removeResetRequest($request->getSession()->get('ResetPasswordPublicToken', ''));
            foreach ($em->getRepository(ResetPasswordRequest::class)->findBy(['user' => $user]) as $resetRequest) {
                $em->remove($resetRequest);
            }

            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte a été supprimé définitivement.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('account_deletion/confirm.html.twig');
    }
}
