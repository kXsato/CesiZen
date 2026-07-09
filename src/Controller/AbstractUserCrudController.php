<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

abstract class AbstractUserCrudController extends AbstractCrudController
{
    use GetsCurrentUserTrait;

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /** @return list<mixed> */
    protected function userBaseFields(): array
    {
        return [
            EmailField::new('email', 'Adresse e-mail'),
            TextField::new('userName', 'Nom d\'utilisateur'),
            DateField::new('birthDate', 'Date de naissance'),
        ];
    }

    abstract protected function filterByCurrentUser(QueryBuilder $qb): void;

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $this->filterByCurrentUser($qb);

        return $qb;
    }
}
