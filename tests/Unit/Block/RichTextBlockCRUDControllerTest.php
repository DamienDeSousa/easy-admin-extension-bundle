<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Unit\Block;

use Dades\CmsBundle\DadesCmsBundle;
use Dades\CmsBundle\Validator\Files\TwigTemplateExists;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\RichTextBlockCRUDController;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Index;
use Dades\EasyAdminExtensionBundle\DadesEasyAdminExtensionBundle;
use Dades\EasyAdminExtensionBundle\Tests\AssertTrait;
use Dades\EasyAdminExtensionBundle\Tests\TraversableContainsAtLeast;
use Dades\EasyAdminExtensionBundle\Tests\Unit\UnitKernelTrait;
use Dades\TestUtils\Loader\LoadResourceTrait;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
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
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class RichTextBlockCRUDControllerTest extends TestCase
{
    use UnitKernelTrait;

    use AssertTrait;

    private static Kernel $kernel;

    public function testConfigureAction()
    {
        /** @var Index $dashboardController */
        $dashboardController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Index');
        /** @var RichTextBlockCRUDController $richTextBlockCRUDController */
        $richTextBlockCRUDController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Block\RichTextBlockCRUDController');
        $richTextBlockActions = $richTextBlockCRUDController
            ->configureActions($dashboardController->configureActions())
            ->getAsDto(null);

        foreach ($richTextBlockActions->getActionPermissions() as $actionPermission) {
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
    public function testConfigureFields($page)
    {
        /** @var RichTextBlockCRUDController $richTextBlockCRUDController */
        $richTextBlockCRUDController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Block\RichTextBlockCRUDController');
        $fields = $richTextBlockCRUDController->configureFields($page);
        $keys = [];
        /** @var Field $field */
        foreach ($fields as $field) {
            $keys[] = $field->getAsDto()->getLabel();
        }
        $fields = array_combine($keys, (array)$fields);

        $this->assertTrue($fields['block.name']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals('name', $fields['block.name']->getAsDto()->getProperty());
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['block.name']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertTrue($fields['block.template']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals('template', $fields['block.template']->getAsDto()->getProperty());
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['block.template']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            Choice::class,
            $fields['block.template']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            TwigTemplateExists::class,
            $fields['block.template']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertTrue($fields['rich-text-block.rich-text']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals('richText', $fields['rich-text-block.rich-text']->getAsDto()->getProperty());
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['rich-text-block.rich-text']->getAsDto()->getFormTypeOption('constraints')
        );
    }

    public function testEntityTypeCreation()
    {
        /** @var RichTextBlockCRUDController $richTextBlockCRUDController */
        $richTextBlockCRUDController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Block\RichTextBlockCRUDController');
        $richTextBlock = $richTextBlockCRUDController->createEntity($richTextBlockCRUDController::getEntityFqcn());

        $this->assertEquals($richTextBlockCRUDController::BLOCK_TYPE, $richTextBlock->getType());
    }

    public static function setUpBeforeClass(): void
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();
    }
}
