<?php

namespace App\Controller\Common;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CommonOwnProfilCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mon profil')
            ->setEntityLabelInPlural('Mon profil');
    }

    public function configureActions(Actions $actions): Actions
    {
        $deleteAccount = Action::new('deleteAccount', 'Supprimer mon compte', 'fa fa-trash')
            ->linkToRoute('account_delete')
            ->setCssClass('btn btn-sm btn-outline btn-error')
            ->displayIf(fn() => true);

        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_EDIT, $deleteAccount);
    }

    public function index(AdminContext $context): KeyValueStore|Response
    {
        $url = $this->adminUrlGenerator
            ->setController(static::class)
            ->setAction(Action::EDIT)
            ->setEntityId($this->getUser()->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $qb->andWhere('entity.id = :currentUser')
            ->setParameter('currentUser', $this->getUser()->getId());

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email', 'Adresse e-mail'),
            TextField::new('userName', 'Nom d\'utilisateur'),
            DateField::new('birthDate', 'Date de naissance'),
        ];
    }


}
