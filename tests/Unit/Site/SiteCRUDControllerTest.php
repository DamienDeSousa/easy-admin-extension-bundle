<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Unit\Site;

use Dades\CmsBundle\DadesCmsBundle;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Index;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Site\SiteCRUDController;
use Dades\EasyAdminExtensionBundle\DadesEasyAdminExtensionBundle;
use Dades\EasyAdminExtensionBundle\Tests\AssertTrait;
use Dades\EasyAdminExtensionBundle\Tests\TraversableContainsAtLeast;
use Dades\EasyAdminExtensionBundle\Tests\Unit\UnitKernelTrait;
use Dades\TestUtils\Loader\LoadResourceTrait;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;

class SiteCRUDControllerTest extends TestCase
{
    use UnitKernelTrait;

    use AssertTrait;

    private static Kernel $kernel;

    public function testConfigureActions()
    {
        /** @var Index $dashboardController */
        $dashboardController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Index');
        /** @var SiteCRUDController $siteCrudController */
        $siteCrudController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Site\SiteCRUDController');
        $siteCrudActionsDto = $siteCrudController
            ->configureActions($dashboardController->configureActions())
            ->getAsDto(null);
        $actionPermissions = $siteCrudActionsDto->getActionPermissions();
        $disabledActions = $siteCrudActionsDto->getDisabledActions();

        $this->assertEquals('ROLE_ADMIN', $actionPermissions[Action::DETAIL]);
        $this->assertEquals('ROLE_ADMIN', $actionPermissions[Action::EDIT]);
        $this->assertEquals('ROLE_ADMIN', $actionPermissions[Action::INDEX]);
        $this->assertEquals('ROLE_ADMIN', $actionPermissions[Action::SAVE_AND_RETURN]);
        $this->assertEquals('ROLE_ADMIN', $actionPermissions[Action::SAVE_AND_CONTINUE]);
        $this->assertContains(Action::NEW, $disabledActions);
        $this->assertContains(Action::DELETE, $disabledActions);
        $this->assertContains(Action::BATCH_DELETE, $disabledActions);
        $this->assertContains(Action::SAVE_AND_ADD_ANOTHER, $disabledActions);
    }

    public function providePagesForActions(): array
    {
        return [
            [Crud::PAGE_DETAIL],
            [Crud::PAGE_EDIT],
            [Crud::PAGE_INDEX],
        ];
    }

    /**
     * @dataProvider providePagesForActions
     */
    public function testConfigureFields($page)
    {
        $iconDirectory = self::$kernel->getContainer()->getParameter('dades_cms.icon_directory');
        /** @var SiteCRUDController $siteCrudController */
        $siteCrudController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Site\SiteCRUDController');
        $fields = $siteCrudController->configureFields($page);
        $keys = [];
        /** @var Field $field */
        foreach ($fields as $field) {
            $keys[] = $field->getAsDto()->getLabel();
        }
        $fields = array_combine($keys, (array)$fields);

        $this->assertTrue($fields['site.show.title']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals('title', $fields['site.show.title']->getAsDto()->getProperty());
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['site.show.title']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertFalse($fields['site.show.icon']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals('icon', $fields['site.show.icon']->getAsDto()->getProperty());
        $this->assertEquals($iconDirectory, $fields['site.show.icon']->getAsDto()->getCustomOption('basePath'));
        $this->assertEquals(
            'public/' . $iconDirectory,
            $fields['site.show.icon']->getAsDto()->getCustomOption('uploadDir')
        );
        $this->assertEquals(
            '[randomhash].[extension]',
            $fields['site.show.icon']->getAsDto()->getCustomOption('uploadedFileNamePattern')
        );
    }

    public static function setUpBeforeClass(): void
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();
    }
}