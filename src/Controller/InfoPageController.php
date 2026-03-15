<?php

namespace App\Controller;

use App\Enum\InfoPageCategory;
use App\Repository\InfoPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class InfoPageController extends AbstractController
{
    #[Route('/page/{slug}', name: 'info_page')]
    public function page(string $slug, InfoPageRepository $repository): Response
    {
        return $this->renderPage($slug, InfoPageCategory::PAGE, $repository);
    }

    #[Route('/legal/{slug}', name: 'info_page_legal')]
    public function legal(string $slug, InfoPageRepository $repository): Response
    {
        return $this->renderPage($slug, InfoPageCategory::LEGAL, $repository);
    }

    #[Route('/{slug}', name: 'info_page_root', priority: -10)]
    public function root(string $slug, InfoPageRepository $repository): Response
    {
        return $this->renderPage($slug, InfoPageCategory::PAGE_ROOT, $repository);
    }

    private function renderPage(string $slug, InfoPageCategory $category, InfoPageRepository $repository): Response
    {
        $page = $repository->findOneBy([
            'slug' => $slug,
            'category' => $category,
            'isPublished' => true,
        ]);

        if (!$page) {
            throw new NotFoundHttpException('Page introuvable.');
        }

        return $this->render('info_page/show.html.twig', ['page' => $page]);
    }
}
