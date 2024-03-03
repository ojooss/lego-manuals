<?php

namespace App\Controller\Admin;

use App\Entity\Manual;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ManualCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Manual::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->onlyOnIndex() // This will display the ID only on the index/list view
                ->hideOnForm() // This will hide the ID on the form view
            ,
           TextField::new('url')
                ->setLabel('URL'),
            //TextEditorField::new('description'),
            AssociationField::new('set')
                ->setLabel('Set'),
        ];
    }
}
