<?php

namespace Dades\EasyAdminExtensionBundle\Tests\Unit;

use Dades\CmsBundle\DadesCmsBundle;
use Dades\EasyAdminExtensionBundle\DadesEasyAdminExtensionBundle;
use Dades\TestUtils\Loader\LoadResourceTrait;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

trait UnitKernelTrait
{
    public static function createKernel(): Kernel
    {
        return new class('test', false) extends Kernel
        {
            use MicroKernelTrait;

            use LoadResourceTrait;

            public function __construct(string $environment, bool $debug)
            {
                parent::__construct($environment, $debug);
            }

            #[Pure]
            public function registerBundles(): iterable
            {
                return [
                    new DadesCmsBundle(),
                    new DadesEasyAdminExtensionBundle(),
                    new DoctrineBundle(),
                    new FrameworkBundle(),
                    new TwigBundle(),
                    new EasyAdminBundle(),
                    new SecurityBundle(),
                ];
            }

            protected function configureRoutes(RouteCollectionBuilder $routes)
            {
                $confDir = $this->getProjectDir().'/src/Resources/config';
                $routes->import($confDir . '/routes.xml', '/', 'xml');
            }

            protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
            {
                $this->loadDoctrineResource($this->getProjectDir(), $loader);
                $this->loadFrameworkResource($this->getProjectDir(), $loader);
                $this->loadTwigResource($this->getProjectDir(), $loader);
                $this->loadSecurityResource($this->getProjectDir(), $loader);
            }

            public function getCacheDir(): string
            {
                return __DIR__ . '/../../../cache/' . spl_object_hash($this);
            }
        };
    }
}