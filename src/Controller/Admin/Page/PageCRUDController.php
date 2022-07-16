<?php

/**
 * Defines the PageCRUDController class.
 *
 * @author Damien DE SOUSA
 */

namespace Dades\EasyAdminExtensionBundle\Controller\Admin\Page;

use Dades\CmsBundle\Entity\Page;
use Dades\CmsBundle\Validator\Block\AvailableBlock;
use Dades\CmsBundle\Validator\Block\BlockType;
use Dades\CmsBundle\Validator\Block\NotBlockType;
use Dades\CmsBundle\Validator\Files\TwigTemplateExists;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Provides CRUD actions for Page.
 */
class PageCRUDController extends AbstractCrudController
{
    private Finder $finder;

    #[Pure]
    public function __construct(private FileLocator $fileLocator)
    {
        $this->finder = new Finder();
    }

    public static function getEntityFqcn(): string
    {
        return Page::class;
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
        $availableTemplates = $this->getAvailableTemplates();
        return [
            ChoiceField::new('template', 'page.template')
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
            TextField::new('routeName', 'page.route-name')
                ->setMaxLength(100)
                ->setRequired(true)
                ->setFormTypeOption(
                    'constraints',
                    [
                        new NotBlank(),
                        new Length(['max' => 100]),
                    ]
                ),
            TextField::new('url', 'page.url')
                ->setMaxLength(150)
                ->setRequired(true)
                ->setFormTypeOption(
                    'constraints',
                    [
                        new NotBlank(),
                        new Length(['max' => 150])
                    ]
                ),
            AssociationField::new('blocks', 'page.blocks')
                /**
                 * set this option to enable the possibility to
                 * update the MANY side of the ONE-TO-MANY relation from the ONE side
                 */
                ->setFormTypeOptions(['by_reference' => false])
                ->setQueryBuilder(function ($queryBuilder) {
                    /** @var QueryBuilder $queryBuilder */
                    return $queryBuilder->where('entity.type <> :type')
                        ->setParameter('type', SEOBlockCRUDController::BLOCK_TYPE);
                })->setFormTypeOption(
                    'constraints',
                    [
                        new NotBlockType(['type' => SEOBlockCRUDController::BLOCK_TYPE]),
                    ]
                ),
            AssociationField::new('seoBlock', 'page.seo-block')
                ->onlyWhenCreating()
                ->setRequired(true)
                ->setQueryBuilder(function ($queryBuilder) {
                    /** @var QueryBuilder $queryBuilder */
                    return $queryBuilder
                        ->leftJoin('entity.pageForSeo', 'p')
                        ->where('entity.type = :type')
                        ->andWhere('p IS NULL')
                        ->setParameter('type', SEOBlockCRUDController::BLOCK_TYPE);
                })->setFormTypeOption(
                    'constraints',
                    [
                        new BlockType(['type' => SEOBlockCRUDController::BLOCK_TYPE]),
                        new AvailableBlock(),
                        new NotNull(),
                    ]
                ),
            AssociationField::new('seoBlock', 'page.seo-block')
                ->hideOnForm()
                ->setRequired(true),
        ];
    }

    private function getAvailableTemplates(): array
    {
        $blankPageFolder = $this->fileLocator->locate('@DadesCmsBundle/Resources/views/page/blankpage/');
        $files = $this->finder->files()->in($blankPageFolder);
        $choices = [];
        foreach ($files as $file) {
            $choices[] = '@DadesCms/page/blankpage/' . $file->getRelativePathname();
        }

        return $choices;
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (
            !$this->isGranted(
                Permission::EA_EXECUTE_ACTION,
                ['action' => Action::EDIT, 'entity' => $context->getEntity()]
            )
        ) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        // START rewrite
        $fields = $this->configureFields(Crud::PAGE_EDIT);
        /** @var Page $page */
        $page = $context->getEntity()->getInstance();
        $fields[] = AssociationField::new('seoBlock', 'page.seo-block')
            ->setRequired(true)
            ->setQueryBuilder(function ($queryBuilder) use ($page) {
                /** @var QueryBuilder $queryBuilder */
                return $queryBuilder
                    ->leftJoin('entity.pageForSeo', 'p')
                    ->where('entity.type = :type')
                    ->andWhere('p IS NULL')
                    ->orWhere('p.id = :page_id')
                    ->setParameter('type', SEOBlockCRUDController::BLOCK_TYPE)
                    ->setParameter('page_id', $page->getId());
            })->setFormTypeOption(
                'constraints',
                [
                    new BlockType(['type' => SEOBlockCRUDController::BLOCK_TYPE]),
                    new AvailableBlock(['page' => $page]),
                    new NotNull(),
                ]
            );
        // END rewrite
        $this->container
            ->get(EntityFactory::class)
            ->processFields($context->getEntity(), FieldCollection::new($fields));
        $this->container
            ->get(EntityFactory::class)
            ->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
        $entityInstance = $context->getEntity()->getInstance();

        if ($context->getRequest()->isXmlHttpRequest()) {
            $fieldName = $context->getRequest()->query->get('fieldName');
            $newValue = 'true' === mb_strtolower($context->getRequest()->query->get('newValue'));

            $event = $this->ajaxEdit($context->getEntity(), $fieldName, $newValue);
            if ($event->isPropagationStopped()) {
                return $event->getResponse();
            }

            // cast to integer instead of string to avoid sending empty responses for 'false'
            return new Response((int) $newValue);
        }

        $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->processUploadedFiles($editForm);

            $event = new BeforeEntityUpdatedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            $this->updateEntity(
                $this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()),
                $entityInstance
            );

            $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

            return $this->getRedirectResponseAfterSave($context, Action::EDIT);
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => 'crud/edit',
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }
}
