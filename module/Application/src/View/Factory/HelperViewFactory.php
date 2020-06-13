<?php

namespace Application\View\Factory;

class HelperViewFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        return new \Application\View\HelperView($container);
    }
}