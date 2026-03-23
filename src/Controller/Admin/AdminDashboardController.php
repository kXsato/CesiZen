<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private UserRepository $userRepository,
    ) {}

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
        $pendingReactivations = $this->userRepository->countReactivationRequested();

        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
        yield MenuItem::linkTo(AdminUserCrudController::class, 'Utilisateurs', 'fa fa-users')
            ->setBadge($pendingReactivations > 0 ? $pendingReactivations : null, 'danger');
        yield MenuItem::linkTo(AdminInfoPageCrudController::class, 'Pages', 'fa fa-file-text');
        yield MenuItem::linkTo(AdminMenuItemCrudController::class, 'Menu', 'fa fa-bars');
        yield MenuItem::linkTo(AdminOwnProfilCrudController::class, 'Mon profil', 'fa fa-user');
    
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}
