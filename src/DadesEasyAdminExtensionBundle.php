<?php

namespace Dades\EasyAdminExtensionBundle;

use Dades\EasyAdminExtensionBundle\DependencyInjection\DadesEasyAdminExtensionExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

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
