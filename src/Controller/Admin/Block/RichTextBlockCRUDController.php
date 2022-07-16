<?php

/**
 * Defines the RichTextBlockCRUDController class.
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Provides CRUD actions for Rich text block.
 */
class RichTextBlockCRUDController extends AbstractBlockCRUDController
{
    public const BLOCK_TYPE = 'rich_text_block';

    #[Pure]
    public function __construct(FileLocator $fileLocator)
    {
        parent::__construct($fileLocator);
    }

    public function configureFields(string $pageName): iterable
    {
        $parentFields = parent::configureFields($pageName);
        return array_merge(
            $parentFields,
            [
                TextEditorField::new('richText', 'rich-text-block.rich-text')
                    ->setRequired(true)
                    ->setFormTypeOption('constraints', [new NotBlank()]),
            ]
        );
    }
}
