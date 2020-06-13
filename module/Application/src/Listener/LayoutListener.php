<?php

namespace Application\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\StringToLower;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Resolver\TemplateMapResolver;

class LayoutListener extends AbstractListenerAggregate
{
/** @var TemplateMapResolver */
private $templateMapResolver;

/** @var FilterInterface */
private $filter;

public function __construct(TemplateMapResolver $templateMapResolver)
{
$this->templateMapResolver = $templateMapResolver;
$this->filter              = (new FilterChain())
->attach(new CamelCaseToDash())
->attach(new StringToLower());
}

public function attach(EventManagerInterface $events, $priority = 1)
{
$this->listeners[] = $events->attach(
MvcEvent::EVENT_RENDER,
[$this, 'setLayout']
);
}

public function setLayout(MvcEvent $event) : void
{
// Get and check the route match object
$routeMatch = $event->getRouteMatch();
if (! $routeMatch) {
return;
}

// Get and check the parameter for current controller
$controller = $routeMatch->getParam('controller');
if (! $controller) {
return;
}

// Extract module name
$module = substr($controller, 0, strpos($controller, '\\'));

// Convert the module name from camel case to a lower string with dashes
$name = 'layout/' . $this->filter->filter($module);

// Has the resolver an entry / layout with the given name?
if (! $this->templateMapResolver->has($name)) {
return;
}

// Get root view model
$layoutViewModel = $event->getViewModel();

// Change template
$layoutViewModel->setTemplate($name);
}
}
