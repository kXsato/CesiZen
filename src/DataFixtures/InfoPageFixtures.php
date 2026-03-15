<?php

namespace App\DataFixtures;

use App\Entity\InfoPage;
use App\Enum\InfoPageCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class InfoPageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile(__DIR__ . '/../../config/fixtures/pages.yaml');

        foreach ($data['pages'] as $pageData) {
            $page = (new InfoPage())
                ->setTitle($pageData['title'])
                ->setCategory(InfoPageCategory::from($pageData['category']))
                ->setContent($pageData['content'])
                ->setIsPublished($pageData['isPublished']);

            $manager->persist($page);
            $this->addReference('page_' . $pageData['title'], $page);
        }

        $manager->flush();
    }
}
