<?php

/**
 * Defines the DadesEasyAdminExtensionBundle class.
 *
 * @author Damien DE SOUSA
 */

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle;

use Dades\EasyAdminExtensionBundle\DependencyInjection\DadesEasyAdminExtensionExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Defines the bundle.
 */
class DadesEasyAdminExtensionBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface|null
    {
        if (null === $this->extension) {
            $this->extension = new DadesEasyAdminExtensionExtension();
        }

        return $this->extension;
    }
}
