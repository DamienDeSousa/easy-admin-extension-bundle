<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Controller\Admin\Block;

use Dades\CmsBundle\Entity\Block;
use Dades\CmsBundle\Validator\Files\TwigTemplateExists;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class AbstractBlockCRUDController extends AbstractCrudController
{
    public const BLOCK_TYPE = '';

    protected Finder $finder;

    #[Pure]
    public function __construct(protected FileLocator $fileLocator)
    {
        $this->finder = new Finder();
    }

    public static function getEntityFqcn(): string
    {
        return Block::class;
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

    public function configureFields(string $pageName): iterable
    {
        $availableTemplates = $this->getAvailableTemplate();

        return [
            TextField::new('name', 'block.name')
                ->setRequired(true)
                ->setMaxLength(255)
                ->setFormTypeOption('constraints', [new NotBlank()]),
            ChoiceField::new('template', 'seo-block.template')
                ->setChoices(array_combine($availableTemplates, $availableTemplates))
                ->setRequired(true)
                ->setFormTypeOption(
                    'constraints',
                    [
                        new NotBlank(),
                        new Choice(['choices' => $availableTemplates]),
                        new TwigTemplateExists(),
                    ]
                ),
        ];
    }

    protected function getFileSystemTemplatePath(): string
    {
        return '@DadesCmsBundle/Resources/views/block/%s/';
    }

    protected function getTwigTemplatePath(): string
    {
        return '@DadesCms/block/%s/';
    }

    protected function getAvailableTemplate(): array
    {
        $seoBlockTemplates = $this->fileLocator->locate(
            sprintf($this->getFileSystemTemplatePath(), static::BLOCK_TYPE)
        );
        $files = $this->finder->files()->in($seoBlockTemplates);
        $choices = [];
        foreach ($files as $file) {
            $fullFilePath = sprintf($this->getTwigTemplatePath(), static::BLOCK_TYPE) . $file->getRelativePathname();
            $choices[] = $fullFilePath;
        }

        return $choices;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $queryBuilder->andWhere('entity.type = :type')->setParameter('type', static::BLOCK_TYPE);
    }

    public function createEntity(string $entityFqcn): Block
    {
        /** @var Block $entity */
        $entity = parent::createEntity($entityFqcn);
        $entity->setType(static::BLOCK_TYPE);

        return $entity;
    }
}
