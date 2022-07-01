<?php

/**
 * File that define the Index class.
 *
 * @author    Damien DE SOUSA <email@email.com>
 * @copyright 2020 Damien DE SOUSA
 */

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Controller\Admin;

use App\Controller\TestBlockCRUDController;
use Dades\CmsBundle\Entity\Block;
use Dades\CmsBundle\Entity\Page;
use Dades\CmsBundle\Entity\Site;
use Dades\CmsBundle\Service\Site\SiteReaderInterface;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\RichTextBlockCRUDController;
use Dades\EasyAdminExtensionBundle\Controller\Admin\Block\SEOBlockCRUDController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

/**
 * This class display the index admin page.
 */
class Index extends AbstractDashboardController
{
    public const INDEX_ROUTE = 'admin_index';

    public const ADMIN_HOME_PAGE_URI = '/admin/';

    public function __construct(
        private TranslatorInterface $translator,
        private SiteReaderInterface $siteReaderService,
        private string $iconDirectory,
    ) {
    }

    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        $site = $this->siteReaderService->read();
        $dashboard = Dashboard::new()
            ->setTitle($this->translator->trans('cms.name'))
            ->disableUrlSignatures();
        if ($site) {
            $dashboard->setFaviconPath(sprintf('/%s%s', $this->iconDirectory, $site->getIcon()));
        }

        return $dashboard;
    }

    /**
     * @inheritdoc
     */
    public function configureMenuItems(): iterable
    {
        $menuItems = [
            MenuItem::linkToDashboard('admin.sections.dashboard', 'fa fa-home'),

            MenuItem::section('admin.sections.general_parameter'),
            MenuItem::linkToCrud('admin.sections.site', 'fa fa-gear', Site::class),
        ];

        $menuItems[] = MenuItem::section('admin.sections.cms');
        $menuItems[] = MenuItem::subMenu('admin.sections.block', 'fa fa-square')->setSubItems([
            MenuItem::linkToCrud('admin.sections.seo-block', 'fa fa-line-chart', Block::class)
                ->setController(SEOBlockCRUDController::class),
            MenuItem::linkToCrud('admin.sections.rich-text-block', 'fa fa-line-chart', Block::class)
                ->setController(RichTextBlockCRUDController::class),
        ]);
        $menuItems[] = MenuItem::linkToCrud('admin.sections.page', 'fa fa-columns', Page::class);


        return $menuItems;
    }
}
