<?php

namespace App\Controller\Admin;

use App\Entity\InfoPage;
use App\Enum\InfoPageCategory;
use EmilePerron\TinymceBundle\Form\Type\TinymceType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AdminInfoPageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return InfoPage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages')
            ->addFormTheme('@Tinymce/form/tinymce_type.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Titre');
        yield SlugField::new('slug', 'Slug')->setTargetFieldName('title')->onlyOnDetail();
        yield ChoiceField::new('category', 'Catégorie')
            ->setChoices(array_combine(
                array_map(fn($c) => $c->label(), InfoPageCategory::cases()),
                InfoPageCategory::cases()
            ));
        yield BooleanField::new('isPublished', 'Publié');
        yield Field::new('content', 'Contenu')
            ->onlyOnForms()
            ->setFormType(TinymceType::class);
        yield DateTimeField::new('createdAt', 'Créé le')->setDisabled(true)->onlyOnDetail();
        yield DateTimeField::new('updatedAt', 'Modifié le')->setDisabled(true)->onlyOnDetail();
    }
}
