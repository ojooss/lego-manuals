<?php

namespace App\Controller\Admin;

use App\Entity\Set;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Set::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPaginatorPageSize(100)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex() // This will display the ID only on the index/list view
                ->hideOnForm() // This will hide the ID on the form view
                ->formatValue(function ($value) {
                    return sprintf('%d', $value);
                })
            ,
            NumberField::new('number')
                ->setLabel('Nummer')
                ->formatValue(function ($value) {
                    return sprintf('%d', $value);
                })
            ,
            TextField::new('name')
                ->setLabel('Name')
            ,
        ];
    }


    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('number')
            ->add('name')
            ;
    }
}
