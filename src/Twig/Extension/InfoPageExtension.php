<?php

namespace App\Twig\Extension;

use App\Repository\MenuItemRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class InfoPageExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private MenuItemRepository $repository) {}

    public function getGlobals(): array
    {
        return [
            'menu_items' => $this->repository->findRootItems(),
        ];
    }
}
