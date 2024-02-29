<?php

namespace App\Controller\Admin;

use App\Entity\Manual;
use App\Entity\Set;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{

    public function __construct(
        protected readonly Packages $package
    ) {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());
        return $this->redirect($adminUrlGenerator->setController(SetCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('admin/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setLocales(['de'])
            ->setTitle(
                '<img id="logo" src="' .
                $this->package->getUrl('build/img/logo.jpeg') .
                '"  alt="Logo"/> Administration'
            )
            ->setFaviconPath(
                $this->package->getUrl('build/appicons/favicon-32x32.png')
            )
            ->disableDarkMode()
            ;
    }

    public function configureMenuItems(): iterable
    {
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard');
        yield MenuItem::linkToCrud('Sets', 'fa fa-book', Set::class);
        yield MenuItem::linkToCrud('Anleitungen', 'fa fa-file-pdf', Manual::class);
    }

    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets();
        $assets->addCssFile(
            $this->package->getUrl('build/admin.css')
        );
        return $assets;
    }

}
