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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

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

        $plainPassword = bin2hex(random_bytes(8));

        $entityInstance->setPassword($this->hasher->hashPassword($entityInstance, $plainPassword));

        $this->addFlash('success', sprintf(
            'Compte créé pour %s. Mot de passe temporaire : %s',
            $entityInstance->getEmail(),
            $plainPassword
        ));

        parent::persistEntity($entityManager, $entityInstance);
    }
}
