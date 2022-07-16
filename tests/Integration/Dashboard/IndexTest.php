<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Integration\Dashboard;

use Dades\CmsBundle\Entity\Block;
use Dades\CmsBundle\Entity\Page;
use Dades\CmsBundle\Entity\Site;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\RichTextBlockCRUDController;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Index;
use Dades\EasyAdminExtensionBundle\Tests\Integration\IntegrationKernelTrait;
use Dades\TestFixtures\Fixture\FixtureLoaderTrait;
use Dades\TestUtils\Runner\RunCommandTrait;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Dto\MenuItemDto;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexTest extends TestCase
{
    use FixtureLoaderTrait;

    use RunCommandTrait;

    use IntegrationKernelTrait;

    private Kernel $kernel;

    public function testIndexDashboardWithSite()
    {
        /** @var Site $site */
        $site = $this->fixtureRepository->getReference('site');
        /** @var Index $dashboardController */
        $dashboardController = $this->kernel->getContainer()->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Index');
        /** @var TranslatorInterface $translator */
        $translator = $this->kernel->getContainer()->get('translator');
        $iconDirectoryPath = $this->kernel->getContainer()->getParameter('dades_cms.icon_directory');
        $dashboard = $dashboardController->configureDashboard()->getAsDto();
        $menuItems = $dashboardController->configureMenuItems();
        $formatMenuItem = function ($item) use (&$formatMenuItem) {
            /** @var MenuItemDto $dtoItem */
            $dtoItem = in_array('getAsDto', get_class_methods($item)) ? $item->getAsDto() : $item;
            $subItems = [];
            foreach ($dtoItem->getSubItems() as $subItem) {
                $subItems = array_merge($subItems, $formatMenuItem($subItem));
            }
            return [
                $dtoItem->getLabel() => [
                    'type' => $dtoItem->getType(),
                    'routeName' => $dtoItem->getRouteName(),
                    'routeParameters' => $dtoItem->getRouteParameters(),
                    'subItems' => $subItems,
                ],
            ];
        };
        $formattedMenuItems = [];
        foreach ($menuItems as $menuItem) {
            $formattedMenuItems = array_merge($formattedMenuItems, $formatMenuItem($menuItem));
        }

        $this->assertEquals(
            sprintf(
                '/%s%s',
                $iconDirectoryPath,
                $site->getIcon()
            ),
            $dashboard->getFaviconPath()
        );
        $this->assertEquals($translator->trans(id: 'cms.name', domain: 'messages'), $dashboard->getTitle());
        $this->assertArrayHasKey('admin.sections.dashboard', $formattedMenuItems);
        $this->assertEquals('dashboard', $formattedMenuItems['admin.sections.dashboard']['type']);
        $this->assertArrayHasKey('admin.sections.site', $formattedMenuItems);
        $this->assertEquals('crud', $formattedMenuItems['admin.sections.site']['type']);
        $this->assertEquals(
            Site::class,
            $formattedMenuItems['admin.sections.site']['routeParameters']['entityFqcn']
        );
        $this->assertArrayHasKey('admin.sections.block', $formattedMenuItems);
        $this->assertEquals('submenu', $formattedMenuItems['admin.sections.block']['type']);
        $this->assertArrayHasKey('admin.sections.seo-block', $formattedMenuItems['admin.sections.block']['subItems']);
        $this->assertEquals(
            'crud',
            $formattedMenuItems['admin.sections.block']['subItems']['admin.sections.seo-block']['type']
        );
        $this->assertEquals(
            SEOBlockCRUDController::class,
            $formattedMenuItems['admin.sections.block']['subItems']['admin.sections.seo-block']['routeParameters']['crudControllerFqcn']
        );
        $this->assertEquals(
            Block::class,
            $formattedMenuItems['admin.sections.block']['subItems']['admin.sections.seo-block']['routeParameters']['entityFqcn']
        );
        $this->assertArrayHasKey('admin.sections.rich-text-block', $formattedMenuItems['admin.sections.block']['subItems']);
        $this->assertEquals(
            'crud',
            $formattedMenuItems['admin.sections.block']['subItems']['admin.sections.rich-text-block']['type']
        );
        $this->assertEquals(
            RichTextBlockCRUDController::class,
            $formattedMenuItems['admin.sections.block']['subItems']['admin.sections.rich-text-block']['routeParameters']['crudControllerFqcn']
        );
        $this->assertEquals(
            Block::class,
            $formattedMenuItems['admin.sections.block']['subItems']['admin.sections.rich-text-block']['routeParameters']['entityFqcn']
        );
        $this->assertArrayHasKey('admin.sections.page', $formattedMenuItems);
        $this->assertEquals('crud', $formattedMenuItems['admin.sections.page']['type']);
        $this->assertEquals(Page::class, $formattedMenuItems['admin.sections.page']['routeParameters']['entityFqcn']);
    }

    protected function setUp(): void
    {
        $this->kernel = $this->createKernel();

        $this->kernel->boot();
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $this->runCommand(
            $application,
            [
                'command' => 'doctrine:schema:update',
                '--quiet' => true,
                '--force' => true,
            ]
        );
        /** @var ManagerRegistry $registry */
        $registry = $this->kernel->getContainer()->get('doctrine');
        $this->loadFixture(
            $registry->getManager(),
            new IndexTestFixture()
        );
    }
}