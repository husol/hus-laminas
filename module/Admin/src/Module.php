<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Application\Listener\LayoutListener;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Resolver\TemplateMapResolver;

class Module
{
  const VERSION = '3.0.3-dev';

  public function getConfig()
  {
    return include __DIR__ . '/../config/module.config.php';
  }

  public function onBootstrap(MvcEvent $event): void
  {
    $application = $event->getApplication();

    /** @var TemplateMapResolver $templateMapResolver */
    $templateMapResolver = $application->getServiceManager()->get(
      'ViewTemplateMapResolver'
    );

    // Create and register layout listener
    $listener = new LayoutListener($templateMapResolver);
    $listener->attach($application->getEventManager());
  }
}
