<?php

/**
 * Defines the SiteCRUDController class.
 *
 * @author Damien DE SOUSA
 */

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Controller\Admin\Site;

use Dades\CmsBundle\Entity\Site;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Defines the CRUD actions for a Site entity.
 */
class SiteCRUDController extends AbstractCrudController
{
    public function __construct(private string $iconDirectory)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Site::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->renderContentMaximized();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER)
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
        //todo: add this constraint when constraint on ImageField will be available
        // $constraintOptions = [
        //     new FileUpload([
        //         'maxSize' => '1024k',
        //         'mimeTypes' => [
        //             'image/jpeg',
        //             'image/png',
        //             'image/x-icon'
        //         ],
        //         'mimeTypesMessage' => 'site.update.icon.error.mimetypes',
        //         'basePath' => $this->iconDirectory,
        //     ])
        // ];

        return [
            TextField::new('title', 'site.show.title')
                ->setRequired(true)
                ->setFormTypeOption('constraints', [new NotBlank()]),
            ImageField::new('icon', 'site.show.icon')
                ->setBasePath($this->iconDirectory)
                ->setUploadDir(sprintf('public/%s', $this->iconDirectory))
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
                // ->setFormTypeOption('help', 'site.update.icon_help'),
        ];
    }
}
