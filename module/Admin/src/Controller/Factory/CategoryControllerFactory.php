<?php

namespace Admin\Controller\Factory;

/**
 * This is the factory for HusController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class CategoryControllerFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
  public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
  {
    return new \Admin\Controller\CategoryController($container);
  }
}