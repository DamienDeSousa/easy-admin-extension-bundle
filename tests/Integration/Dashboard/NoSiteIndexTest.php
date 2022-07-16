<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Integration\Dashboard;

use Dades\EasyAdminExtensionBundle\Controller\Admin\Index;
use Dades\EasyAdminExtensionBundle\Tests\Integration\IntegrationKernelTrait;
use Dades\TestFixtures\Fixture\FixtureLoaderTrait;
use Dades\TestUtils\Runner\RunCommandTrait;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Kernel;

class NoSiteIndexTest extends TestCase
{
    use FixtureLoaderTrait;

    use RunCommandTrait;

    use IntegrationKernelTrait;

    private Kernel $kernel;

    public function testIndexDashboardWithoutSite()
    {
        /** @var Index $dashboardController */
        $dashboardController = $this->kernel->getContainer()->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Index');
        $iconDirectoryPath = $this->kernel->getContainer()->getParameter('dades_cms.icon_directory');
        $dashboard = $dashboardController->configureDashboard()->getAsDto();

        $this->assertStringStartsNotWith($iconDirectoryPath, $dashboard->getFaviconPath());
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
            new NoSiteIndexTestFixture()
        );
    }
}