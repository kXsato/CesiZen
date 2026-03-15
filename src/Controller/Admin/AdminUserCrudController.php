<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class AdminUserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email', 'Adresse e-mail'),
            TextField::new('userName', 'Nom d\'utilisateur'),
            DateField::new('birthDate', 'Date de naissance'),
            ChoiceField::new('roles', 'Rôle(s)')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            BooleanField::new('isAccountActivated', 'Compte actif'),
            DateTimeField::new('registrationDate', 'Inscription')->setDisabled(true)->onlyOnDetail(),
            DateTimeField::new('lastLogin', 'Dernière connexion')->setDisabled(true)->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        // Définit un mot de passe verrouillé — l'utilisateur devra le créer via l'email envoyé ci-dessous
        $entityInstance->setPassword($this->hasher->hashPassword($entityInstance, bin2hex(random_bytes(32))));

        parent::persistEntity($entityManager, $entityInstance);

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($entityInstance);

            $email = (new TemplatedEmail())
                ->from(new Address('cesizen@noreply.com', 'CesiZen'))
                ->to((string) $entityInstance->getEmail())
                ->subject('Définissez votre mot de passe CesiZen')
                ->htmlTemplate('reset_password/email.html.twig')
                ->context(['resetToken' => $resetToken]);

            $this->mailer->send($email);

            $this->addFlash('success', sprintf(
                'Compte créé pour %s. Un email de définition de mot de passe lui a été envoyé.',
                $entityInstance->getEmail()
            ));
        } catch (ResetPasswordExceptionInterface) {
            $this->addFlash('warning', sprintf(
                'Compte créé pour %s, mais l\'email de définition de mot de passe n\'a pas pu être envoyé.',
                $entityInstance->getEmail()
            ));
        }
    }
}
