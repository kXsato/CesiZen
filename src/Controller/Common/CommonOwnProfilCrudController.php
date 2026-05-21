<?php

namespace App\Controller\Common;

use App\Controller\AbstractUserCrudController;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class CommonOwnProfilCrudController extends AbstractUserCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mon profil')
            ->setEntityLabelInPlural('Mon profil');
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportData = Action::new('exportData', 'Télécharger mes données', 'fa fa-download')
            ->linkToRoute('account_export')
            ->setCssClass('btn btn-sm btn-outline');

        $deleteAccount = Action::new('deleteAccount', 'Supprimer mon compte', 'fa fa-trash')
            ->linkToRoute('account_delete')
            ->setCssClass('btn btn-sm btn-outline btn-error');

        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_EDIT, $exportData)
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

    protected function filterByCurrentUser(QueryBuilder $qb): void
    {
        $qb->andWhere('entity.id = :currentUser')
            ->setParameter('currentUser', $this->getUser()->getId());
    }

    public function configureFields(string $pageName): iterable
    {
        return $this->userBaseFields();
    }
}
