<?php

namespace App\Controller\Admin;

use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AdminMenuItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MenuItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Élément de menu')
            ->setEntityLabelInPlural('Menu')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('label', 'Libellé');
        yield ChoiceField::new('systemRoute', 'Lien système')
            ->setChoices([
                'Connexion'    => 'app_login',
                'Inscription'  => 'app_register',
                'Mon compte'   => 'user_dashboard',
                'Administration' => 'admin',
            ])
            ->setRequired(false)
            ->setHelp('Prioritaire sur la page et l\'URL personnalisée')
            ->onlyOnForms();
        yield AssociationField::new('infoPage', 'Page du site')
            ->setHelp('Laissez vide pour utiliser une URL personnalisée')
            ->onlyOnForms();
        yield TextField::new('rawUrl', 'URL personnalisée')
            ->setHelp('Utilisé uniquement si aucune page ni lien système n\'est sélectionné')
            ->onlyOnForms();
        yield IntegerField::new('position', 'Position');
        yield BooleanField::new('isActive', 'Actif');

        if (in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW])) {
            yield CollectionField::new('children', 'Sous-éléments')
                ->setEntryType(\App\Form\MenuItemChildType::class)
                ->allowAdd()
                ->allowDelete()
                ->onlyOnForms();
        }
    }

    public function persistEntity(EntityManagerInterface $em, mixed $entityInstance): void
    {
        if ($entityInstance instanceof MenuItem) {
            foreach ($entityInstance->getChildren() as $child) {
                $child->setParent($entityInstance);
            }
        }

        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, mixed $entityInstance): void
    {
        if ($entityInstance instanceof MenuItem) {
            foreach ($entityInstance->getChildren() as $child) {
                $child->setParent($entityInstance);
            }
        }

        parent::updateEntity($em, $entityInstance);
    }
}
