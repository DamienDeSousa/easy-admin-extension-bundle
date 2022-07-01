<?php

/**
 * Defines the SEOBlockCRUDController class.
 *
 * @author Damien DE SOUSA
 */

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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Provides CRUD actions for SEO blocks.
 */
class SEOBlockCRUDController extends AbstractCrudController
{
    public const BLOCK_TYPE = 'seo_block';

    private Finder $finder;

    #[Pure]
    public function __construct(private FileLocator $fileLocator)
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

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        $availableTemplates = $this->getAvailableTemplate();
        $metaRobotsValues = $this->getMetaRobotsValues();

        return [
            TextField::new('name', 'block.name')
                ->setRequired(true)
                ->setMaxLength(255)
                ->setFormTypeOption('constraints', [new NotBlank()]),
            TextField::new('title', 'seo-block.title')
                ->setMaxLength(60)
                ->setRequired(true)
                ->setFormTypeOption('constraints', [new NotBlank()]),
            TextField::new('description', 'seo-block.description')
                ->setMaxLength(141)
                ->setRequired(true)
                ->setFormTypeOption('constraints', [new NotBlank()]),
            ChoiceField::new('metaRobots', 'seo-block.meta-robots')
                ->allowMultipleChoices()
                ->setChoices(array_combine($metaRobotsValues, $metaRobotsValues))
                ->setRequired(true)
                ->setFormTypeOption(
                    'constraints',
                    [
                        new NotBlank(),
                        new Choice(['choices' => $metaRobotsValues, 'multiple' => true])
                    ]
                ),
            TextField::new('metaViewport', 'seo-block.meta-viewport')
                ->setMaxLength(255)
                ->hideOnIndex()
                ->setRequired(true)
                ->setFormTypeOption('constraints', [new NotBlank()]),
            TextField::new('canonical', 'seo-block.canonical')
                ->hideOnIndex()
                ->setMaxLength(255)
                ->setFormTypeOption(
                    'constraints',
                    [
                        new Length(['max' => 255])
                    ]
                ),
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

    public function getMetaRobotsValues(): array
    {
        return [
            'index',
            'noindex',
            'none',
            'noimageindex',
            'follow',
            'nofollow',
            'noarchive/nocache',
            'nosnippet',
            'notranslate',
            'unavailable_after',
        ];
    }

    private function getAvailableTemplate(): array
    {
        $seoBlockTemplates = $this->fileLocator->locate(
            '@DadesCmsBundle/Resources/views/block/' . self::BLOCK_TYPE . '/'
        );
        $files = $this->finder->files()->in($seoBlockTemplates);
        $choices = [];
        foreach ($files as $file) {
            $fullFilePath = sprintf('@DadesCms/block/%s/%s', self::BLOCK_TYPE, $file->getRelativePathname());
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

        return $queryBuilder->andWhere('entity.type = :type')->setParameter('type', self::BLOCK_TYPE);
    }

    public function createEntity(string $entityFqcn): Block
    {
        /** @var Block $entity */
        $entity = parent::createEntity($entityFqcn);
        $entity->setType(self::BLOCK_TYPE);

        return $entity;
    }
}