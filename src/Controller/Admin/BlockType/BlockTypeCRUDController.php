<?php

/**
 * Defines the BlockTypeCRUDController class.
 *
 * @author Damien DE SOUSA
 */

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Controller\Admin\BlockType;

use Dades\CmsBundle\Entity\BlockType;
use Dades\CmsBundle\Exception\Entity\DeleteEntityException;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Finder\Finder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Defines the CRUD actions for a block type.
 */
class BlockTypeCRUDController extends AbstractCrudController
{
    public const FORM_TYPE_BLOCK_RELATIVE_PATH = 'cms-bundle/src/Form/Type/Block';

    private Finder $finder;

    #[Pure]
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private TranslatorInterface $translator,
    ) {
        $this->finder = new Finder();
    }

    public static function getEntityFqcn(): string
    {
        return BlockType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->renderContentMaximized();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::INDEX, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_SUPER_ADMIN');
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('type', 'block-type.grid.type')
                ->setMaxLength(100),
            TextField::new('layout', 'block-type.grid.layout')
                ->setMaxLength(255),
            ChoiceField::new('formType', 'block-type.grid.form-type')
                ->setChoices($this->getAvailableFormTypes()),
        ];
    }

    private function getAvailableFormTypes(): array
    {
//        dd(__DIR__ . '/../../../../../' . self::FORM_TYPE_BLOCK_RELATIVE_PATH);
        $files = $this->finder->files()->in(__DIR__ . '/../../../../../' . self::FORM_TYPE_BLOCK_RELATIVE_PATH);
        $choices = [];
        foreach ($files as $file) {
            $choices[$file->getRelativePathname()] = $file->getRelativePathname();
        }

        return $choices;
    }

    public function delete(AdminContext $context): RedirectResponse
    {
        try {
            return parent::delete($context);
        } catch (DeleteEntityException $deleteEntityException) {
            $this->addFlash(
                'danger',
                $this->translator->trans(
                    $deleteEntityException->getTransMessage(),
                    $deleteEntityException->getTransMessageParams()
                )
            );
            $url = $this->adminUrlGenerator->setAction('index')->generateUrl();

            return $this->redirect($url);
        }
    }
}
