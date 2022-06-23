<?php

namespace Dades\EasyAdminExtensionBundle\Controller\Admin\Block;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Config\FileLocator;

class SEOBlockCRUDController extends AbstractCrudController
{
    private Finder $finder;

    public function __construct(private FileLocator $fileLocator)
    {
        $this->finder = new Finder();
    }
    public static function getEntityFqcn(): string
    {
        return \Dades\CmsBundle\Entity\SEOBlock::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->renderContentMaximized();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::INDEX, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_ADMIN');
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'block.name')
                ->setMaxLength(255),
            TextField::new('title', 'seo-block.title')
                ->setMaxLength(60),
            TextField::new('description', 'seo-block.description')
                ->setMaxLength(141),
            ChoiceField::new('metaRobots', 'seo-block.meta-robots')
                ->allowMultipleChoices()
                ->setChoices($this->getMetaRobotsValues()),
            TextField::new('metaViewport', 'seo-block.meta-viewport')
                ->setMaxLength(255)
                ->hideOnIndex(),
            TextField::new('canonical', 'seo-block.canonical')
                ->hideOnIndex(),
            ChoiceField::new('template', 'seo-block.template')
                ->setChoices($this->getAvailableTemplate()),
        ];
    }

    private function getMetaRobotsValues(): array
    {
        return [
            'index' => 'index',
            'noindex' => 'noindex',
            'none' => 'none',
            'noimageindex' => 'noimageindex',
            'follow' => 'follow',
            'nofollow' => 'nofollow',
            'noarchive/nocache' => 'noarchive/nocache',
            'nosnippet' => 'nosnippet',
            'notranslate' => 'notranslate',
            'unavailable_after' => 'unavailable_after',
        ];
    }
    
    private function getAvailableTemplate(): array
    {
        $seoBlockTemplates = $this->fileLocator->locate('@DadesCmsBundle/Resources/views/block/seoblock/');
        $files = $this->finder->files()->in($seoBlockTemplates);
        $choices = [];
        foreach ($files as $file) {
            $choices[$file->getRelativePathname()] = $file->getRelativePathname();
        }

        return $choices;
    }
}