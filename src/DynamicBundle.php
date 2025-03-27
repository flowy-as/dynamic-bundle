<?php

namespace Flowy\DynamicBundle;

use Flowy\DynamicBundle\DependencyInjection\DynamicExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DynamicBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new DynamicExtension();
        }
        return $this->extension;
    }
} 