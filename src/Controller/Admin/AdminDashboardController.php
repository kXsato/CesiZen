<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setDashboard(self::class)
            ->setController(AdminUserCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(AdminUserCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::linkTo(AdminInfoPageCrudController::class, 'Pages d\'information', 'fa fa-file-text');
        yield MenuItem::linkTo(AdminOwnProfilCrudController::class, 'Mon profil', 'fa fa-user');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}
