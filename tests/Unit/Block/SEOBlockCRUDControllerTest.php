<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests\Unit\Block;

use Dades\CmsBundle\Validator\Files\TwigTemplateExists;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Index;
use Dades\EasyAdminExtensionBundle\Tests\AssertTrait;
use Dades\EasyAdminExtensionBundle\Tests\Unit\UnitKernelTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SEOBlockCRUDControllerTest extends TestCase
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
        /** @var SEOBlockCRUDController $richTextBlockCRUDController */
        $richTextBlockCRUDController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController');
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
            [Crud::PAGE_NEW]
        ];
    }

    /**
     * @dataProvider providePagesForActions
     */
    public function testConfigureFields($page)
    {
        /** @var SEOBlockCRUDController $seoBlockCrudController */
        $seoBlockCrudController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController');
        $fields = $seoBlockCrudController->configureFields($page);
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
        $this->assertEquals('title', $fields['seo-block.title']->getAsDto()->getProperty());
        $this->assertTrue($fields['seo-block.title']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals(
            60,
            $fields['seo-block.title']->getAsDto()->getCustomOption(TextField::OPTION_MAX_LENGTH)
        );
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['seo-block.title']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertEquals('description', $fields['seo-block.description']->getAsDto()->getProperty());
        $this->assertTrue($fields['seo-block.description']->getAsDto()->getFormTypeOption('required'));
        $this->assertEquals(
            141,
            $fields['seo-block.description']->getAsDto()->getCustomOption(TextField::OPTION_MAX_LENGTH)
        );
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['seo-block.description']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertEquals('metaRobots', $fields['seo-block.meta-robots']->getAsDto()->getProperty());
        $this->assertTrue($fields['seo-block.meta-robots']->getAsDto()->getFormTypeOption('required'));
        $this->assertTrue(
            $fields['seo-block.meta-robots']->getAsDto()->getCustomOption(ChoiceField::OPTION_ALLOW_MULTIPLE_CHOICES)
        );
        $this->assertContainsAtLeastInstancesOf(
            NotBlank::class,
            $fields['seo-block.meta-robots']->getAsDto()->getFormTypeOption('constraints')
        );
        $this->assertContainsAtLeastInstancesOf(
            Choice::class,
            $fields['seo-block.meta-robots']->getAsDto()->getFormTypeOption('constraints')
        );
        if ($page === Crud::PAGE_INDEX) {
            $this->assertNotContains('seo-block.meta-viewport', $fields);
            $this->assertNotContains('seo-block.canonical', $fields);
        } else {
            $this->assertEquals('metaViewport', $fields['seo-block.meta-viewport']->getAsDto()->getProperty());
            $this->assertTrue($fields['seo-block.meta-viewport']->getAsDto()->getFormTypeOption('required'));
            $this->assertContainsAtLeastInstancesOf(
                NotBlank::class,
                $fields['seo-block.meta-viewport']->getAsDto()->getFormTypeOption('constraints')
            );
            $this->assertContainsAtLeastInstancesOf(
                Length::class,
                $fields['seo-block.meta-viewport']->getAsDto()->getFormTypeOption('constraints')
            );
            $this->assertEquals('canonical', $fields['seo-block.canonical']->getAsDto()->getProperty());
            $this->assertContainsAtLeastInstancesOf(
                Length::class,
                $fields['seo-block.canonical']->getAsDto()->getFormTypeOption('constraints')
            );
        }
    }

    public function testEntityTypeCreation()
    {
        /** @var SEOBlockCRUDController $seoBlockCrudController */
        $seoBlockCrudController = self::$kernel
            ->getContainer()
            ->get('Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController');
        $seoBlock = $seoBlockCrudController->createEntity($seoBlockCrudController::getEntityFqcn());

        $this->assertEquals($seoBlockCrudController::BLOCK_TYPE, $seoBlock->getType());
    }

    public static function setUpBeforeClass(): void
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();
    }
}