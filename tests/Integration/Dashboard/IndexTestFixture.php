<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Integration\Dashboard;

use Dades\CmsBundle\Entity\Site;
use Dades\TestUtils\Provider\Data\SiteProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IndexTestFixture extends Fixture
{
    use SiteProvider;

    public function load(ObjectManager $manager)
    {
        $site = $this->provideSite();
        $site->setIcon('icon.png');
        $manager->persist($site);

        $manager->flush();

        $this->referenceRepository->addReference('site', $site);
    }
}