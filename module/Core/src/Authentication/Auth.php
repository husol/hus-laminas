<?php

namespace Core\Authentication;

use Laminas\Filter\StaticFilter;
use Laminas\Filter\Word\DashToCamelCase;

class Auth
{
    /**
     * @var $instance the unique instance of cache storage
     */
    private static $instance = null;

    /**
     * Constructs the service.
     */
    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            $thisClass = __CLASS__;
            self::$instance = new $thisClass();
        }

        return self::$instance;
    }

    /**
     * @param array $roles
     * @param string $resource
     * @param string $action
     * @return boolean
     */
    public function isAllowed($roles, $resource, $action)
    {
        if (!class_exists($resource)) {
            $resource = str_replace(
                '##',
                StaticFilter::execute($resource, DashToCamelCase::class),
                '##\\Controller\\##Controller'
            );
        }

        return \Core\Permission\Acl\Acl::getInstance()->isAllowedMultiRoles(
            $roles,
            $resource,
            $action
        );
    }
}
