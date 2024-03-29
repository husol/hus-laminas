<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Laminas\Cache',
    'Laminas\Log',
    'Laminas\Filter',
    'Laminas\Paginator',
    'Laminas\Mail',
    'Laminas\I18n',
    'Laminas\Db',
    'Laminas\Session',
    'Laminas\Router',
    'Laminas\Validator',
    'Laminas\Cache\Storage\Adapter\Redis',
    'Core',
    'Admin',
    'Application',
];
