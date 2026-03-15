<?php

namespace App\Twig\Extension;

use App\Repository\InfoPageRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class InfoPageExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private InfoPageRepository $repository) {}

    public function getGlobals(): array
    {
        return [
            'published_pages' => $this->repository->findBy(
                ['isPublished' => true],
                ['title' => 'ASC']
            ),
        ];
    }
}
