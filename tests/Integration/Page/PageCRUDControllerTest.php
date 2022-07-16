<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Integration\Page;

use Dades\CmsBundle\Validator\Block\NotBlockType;
use Dades\CmsBundle\Validator\Files\TwigTemplateExists;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Index;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Page\PageCRUDController;
use Dades\EasyAdminExtensionBundle\Tests\AssertTrait;
use Dades\EasyAdminExtensionBundle\Tests\Integration\IntegrationKernelTrait;
use Dades\TestFixtures\Fixture\FixtureLoaderTrait;
use Dades\TestUtils\Runner\RunCommandTrait;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PageCRUDControllerTest extends TestCase
{
    use FixtureLoaderTrait;

    use RunCommandTrait;

    use IntegrationKernelTrait;

    use AssertTrait;

    private Kernel $kernel;

    public function testConfigureActions(): void
    {
        /** @var Index $dashboardController */
        $dashboardController = $this->kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Index');
        /** @var PageCRUDController $pageCRUDController */
        $pageCRUDController = $this->kernel->getContainer()->get(PageCRUDController::class);
        $actions = $pageCRUDController->configureActions($dashboardController->configureActions())->getAsDto(null);

        foreach ($actions->getActionPermissions() as $actionPermission) {
            $this->assertEquals('ROLE_ADMIN', $actionPermission);
        }
    }

    public function providePagesForActions(): array
    {
        return [
            [Crud::PAGE_DETAIL],
            [Crud::PAGE_EDIT],
            [Crud::PAGE_INDEX],
            [Crud::PAGE_NEW],
        ];
    }

    /**
     * @dataProvider providePagesForActions
     */
    public function testConfigureFields($page): void
    {
        /** @var PageCRUDController $pageCRUDController */
        $pageCRUDController = $this->kernel->getContainer()->get(PageCRUDController::class);
        $fields = $pageCRUDController->configureFields($page);
        $keys = [];
        /** @var Field $field */
        foreach ($fields as $field) {
            $keys[] = $field->getAsDto()->getLabel();
        }
        $fields = array_combine($keys, (array)$fields);

        $this->assertTrue($fields['page.template']->getAsDto()->getFormTypeOption('required'));
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['page.template']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            Choice::class,
            $fields['page.template']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            TwigTemplateExists::class,
            $fields['page.template']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertTrue($fields['page.route-name']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals(100, $fields['page.route-name']->getAsDto()->getCustomOption(TextField::OPTION_MAX_LENGTH));
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['page.route-name']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            Length::class,
            $fields['page.route-name']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertTrue($fields['page.url']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals(150, $fields['page.url']->getAsDto()->getCustomOption(TextField::OPTION_MAX_LENGTH));
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['page.route-name']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            Length::class,
            $fields['page.route-name']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertFalse($fields['page.blocks']->getAsDto()->getFormTypeOption('by_reference'));
        $this->assertContainsAtLeastInstancesOf(
            NotBlockType::class,
            $fields['page.blocks']->getAsDto()->getFormTypeOption('constraints')
        );


        if ($page !== Crud::PAGE_EDIT) {
            $this->assertTrue($fields['page.seo-block']->getAsDto()->getFormTypeOption('required'));
        }

        //TODO:
        //test the others seoBlock association fields
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
            new PageCRUDControllerTestFixture()
        );
    }
}