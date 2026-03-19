<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class AccountReactivationController extends AbstractController
{
    #[Route('/reactivation', name: 'app_account_reactivation')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer,
    ): Response {
        $sent = false;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email', '');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user && !$user->isAccountActivated() && !$user->isReactivationRequested()) {
                $user->setReactivationRequested(true);
                $em->flush();

                $admins = $userRepository->findByRole('ROLE_ADMIN');
                foreach ($admins as $admin) {
                    $notification = (new TemplatedEmail())
                        ->from(new Address('cesizen@noreply.com', 'CesiZen'))
                        ->to((string) $admin->getEmail())
                        ->subject('Demande de réactivation de compte')
                        ->htmlTemplate('account_reactivation/email_admin.html.twig')
                        ->context(['user' => $user]);

                    $mailer->send($notification);
                }
            }

            // Toujours afficher le message de confirmation (ne pas révéler si le compte existe)
            $sent = true;
        }

        return $this->render('account_reactivation/request.html.twig', ['sent' => $sent]);
    }
}
