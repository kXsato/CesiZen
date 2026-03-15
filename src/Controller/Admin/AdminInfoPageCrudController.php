<?php

namespace App\Controller\Admin;

use App\Entity\InfoPage;
use App\Enum\InfoPageCategory;
use EmilePerron\TinymceBundle\Form\Type\TinymceType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class AdminInfoPageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return InfoPage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            SlugField::new('slug', 'Slug')->setTargetFieldName('title')->onlyOnDetail(),
            ChoiceField::new('category', 'Catégorie')
                ->setChoices(array_combine(
                    array_map(fn($c) => $c->label(), InfoPageCategory::cases()),
                    InfoPageCategory::cases()
                )),
            BooleanField::new('isPublished', 'Publié'),
            TextareaField::new('content', 'Contenu')
                ->setFormType(TinymceType::class)
                ->onlyOnForms()
                ->setNumOfRows(20),
            TextareaField::new('content', 'Contenu')->onlyOnIndex()->renderAsHtml(),
            DateTimeField::new('createdAt', 'Créé le')->setDisabled(true)->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Modifié le')->setDisabled(true)->onlyOnDetail(),
        ];
    }
}
