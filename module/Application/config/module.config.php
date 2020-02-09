<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Segment;

return [
  'router' => [
    'routes' => [
      'home' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'index',
          ],
        ],
      ],
      'admin' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/admin[/:action]',
          'defaults' => [
            'controller' => Controller\HomeController::class,
            'action' => 'index',
          ],
        ],
      ],
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
      Controller\HomeController::class => Controller\Factory\HomeControllerFactory::class,
    ],
  ],
  'view_manager' => [
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_map' => [
      'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
      'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
      'error/404' => __DIR__ . '/../view/error/404.phtml',
      'error/index' => __DIR__ . '/../view/error/index.phtml',
    ],
    'template_path_stack' => [
      __DIR__ . '/../view',
    ],
  ],
  'session_containers' => [
    'HusSessionContainer'
  ],
  'view_helpers' => [
    'factories' => [
      View\HelperView::class => View\Factory\HelperViewFactory::class,
    ],
    'aliases' => [
      'config' => View\HelperView::class
    ]
  ],
];
