<?php
namespace Application\View;

use Laminas\Json\Json;
use Laminas\View\Helper\AbstractHelper;

// This view helper class get config from global configuration
class HelperView extends AbstractHelper
{
    protected $container = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    // Gets config by name
    public function get($name)
    {
        $config = $this->container->get('config');
        $result = $config[$name];

        if (is_array($result)) {
            $result = Json::encode($result);
        }

        return $result;
    }
}