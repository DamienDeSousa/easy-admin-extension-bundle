<?php

namespace Dades\EasyAdminExtensionBundle\Tests\Integration\Page;

use Dades\CmsBundle\Entity\Block;
use Dades\CmsBundle\Entity\Page;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PageCRUDControllerTestFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $seoBlock = new Block();
        $seoBlock->setTemplate('path/to/seo/tpl.html.twig');
        $seoBlock->setName('seo');
        $seoBlock->setType(SEOBlockCRUDController::BLOCK_TYPE);
        $page = new Page();
        $page->setRouteName('route_name');
        $page->setUrl('/url');
        $page->setSeoBlock($seoBlock);
        $page->setTemplate('path/to/tmpl.html.twig');

        $manager->persist($page);
        $manager->flush();

        $this->addReference('page', $page);
    }
}