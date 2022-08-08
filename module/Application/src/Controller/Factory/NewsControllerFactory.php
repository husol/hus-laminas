<?php

namespace Application\Controller\Factory;


/**
 * This is the factory for *Controller. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class NewsControllerFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
  public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
  {
    return new \Application\Controller\NewsController($container);
  }
}