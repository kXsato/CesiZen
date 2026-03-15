<?php

namespace App\DataFixtures;

use App\Entity\InfoPage;
use App\Entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile(__DIR__ . '/../../config/fixtures/menu.yaml');

        foreach ($data['menu'] as $itemData) {
            $item = $this->buildItem($itemData);
            $manager->persist($item);

            if (isset($itemData['children'])) {
                foreach ($itemData['children'] as $childData) {
                    $child = $this->buildItem($childData);
                    $item->addChild($child);
                    $manager->persist($child);
                }
            }
        }

        $manager->flush();
    }

    private function buildItem(array $data): MenuItem
    {
        $item = (new MenuItem())
            ->setLabel($data['label'])
            ->setPosition($data['position'])
            ->setIsActive($data['isActive']);

        if (!empty($data['infoPage'])) {
            /** @var InfoPage $page */
            $page = $this->getReference('page_' . $data['infoPage'], InfoPage::class);
            $item->setInfoPage($page);
        }

        if (!empty($data['url'])) {
            $item->setUrl($data['url']);
        }

        if (!empty($data['systemRoute'])) {
            $item->setSystemRoute($data['systemRoute']);
        }

        return $item;
    }

    public function getDependencies(): array
    {
        return [InfoPageFixtures::class];
    }
}
