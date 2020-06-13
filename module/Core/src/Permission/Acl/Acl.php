<?php

namespace Core\Permission\Acl;

use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\Permissions\Acl\Resource\GenericResource;

class Acl extends \Laminas\Permissions\Acl\Acl
{
    /**
     * @var $instance the unique instance of cache storage
     */
    private static $instance = null;

    /**
     * @var $config internal cache keys
     */
    private static $config = null;

    public function __construct()
    {
        self::$config = \Laminas\Config\Factory::fromFile(APPLICATION_PATH . '/config/acl.config.php');

        $this->__buildResources();
        $this->__buildRoles();
        $this->__buildRules();
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
     * @param string[] $roles
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function isAllowedMultiRoles(array $roles, $resource, $action)
    {
        foreach ($roles as $role) {
            if ($this->isAllowed($role, $resource, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create the resources
     */
    private function __buildResources()
    {
        foreach (self::$config['resources'] as $k => $row) {
            $this->addResource(new GenericResource($k));
            foreach ($row as $r) {
                $this->addResource(new GenericResource($r), $k);
            }
        }
    }

    /**
     * Create roles
     */
    private function __buildRoles()
    {
        foreach (self::$config['roles'] as $r => $rr) {
            if (is_null($rr)) {
                $this->addRole(new GenericRole($r));
            }

            if (is_array($rr)) {
                $parents = [];
                foreach ($rr as $rrr) {
                    $parents[] = $rrr;
                }

                $this->addRole(new GenericRole($r), $parents);
            }
        }
    }

    /**
     * Create rules
     */
    private function __buildRules()
    {
        foreach (self::$config['rules'] as $role => $controller) {
            if (is_null($controller)) {
                $this->allow($role);
            } else {
                foreach ($controller as $controllerName => $permission) {
                    if (!is_null($permission['allow'])) {
                        $this->allow($role, $controllerName, $permission['allow']);
                    }

                    if (!is_null($permission['deny'])) {
                        $this->deny($role, $controllerName, $permission['deny']);
                    }
                }
            }
        }
    }
}
