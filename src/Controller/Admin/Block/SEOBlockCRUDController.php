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
class SEOBlockCRUDController extends AbstractBlockCRUDController
{
    public const BLOCK_TYPE = 'seo_block';

    #[Pure]
    public function __construct(FileLocator $fileLocator)
    {
        parent::__construct($fileLocator);
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        $parentFields = parent::configureFields($pageName);
        $metaRobotsValues = $this->getMetaRobotsValues();

        return array_merge(
            $parentFields,
            [
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
            ]
        );
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
}
