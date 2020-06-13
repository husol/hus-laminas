<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\HttpUserAgent;
use Laminas\Session\Validator\RemoteAddr;

return [
  // Session configuration.
  'session_config' => [
    // Session cookie will expire in 8 hours.
    'cookie_lifetime' => 3600*8,
    // Session data will be stored on server maximum for 2 days.
    'gc_maxlifetime' => 3600*24*2,
  ],
  // Session manager configuration.
  'session_manager' => [
    // Session validators (used for security).
    'validators' => [
      //RemoteAddr::class,
      HttpUserAgent::class,
    ]
  ],
  // Session storage configuration.
  'session_storage' => [
    'type' => SessionArrayStorage::class
  ],
  'service_manager' => [
    'abstract_factories' => [
      Laminas\Db\Adapter\AdapterAbstractServiceFactory::class,
    ],
    'factories' => [
      Laminas\Db\Adapter\Adapter::class => Laminas\Db\Adapter\AdapterServiceFactory::class,
    ],
  ],
  'view_manager' => [
    'display_exceptions' => false,
  ],
];
