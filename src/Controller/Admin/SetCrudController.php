<?php

namespace App\Controller\Admin;

use App\Entity\Set;
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

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex() // This will display the ID only on the index/list view
                ->hideOnForm() // This will hide the ID on the form view
            ,
            NumberField::new('number')
                ->setLabel('Nummer'),
            TextField::new('name')
                ->setLabel('Name'),
        ];
    }
}
