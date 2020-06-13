<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin;

use Laminas\Router\Http\Segment;

return [
  'router' => [
    'routes' => [
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
      'adminUser' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/admin/users[/:action]',
          'defaults' => [
            'controller' => Controller\UserController::class,
            'action' => 'index',
          ],
        ],
      ],
      'adminCategory' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/admin/categories[/:action]',
          'defaults' => [
            'controller' => Controller\CategoryController::class,
            'action' => 'index',
          ],
        ],
      ],
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\HomeController::class => Controller\Factory\HomeControllerFactory::class,
      Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
      Controller\CategoryController::class => Controller\Factory\CategoryControllerFactory::class,
    ],
  ],
  'view_manager' => [
    'display_not_found_reason' => true,
    'display_exceptions' => true,
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_map' => [
      'layout/admin' => ROOT_DIR . '/module/Application/view/layout/layout_admin.phtml',
      'error/404' => ROOT_DIR . '/module/Application/view/error/404.phtml',
      'error/index' => ROOT_DIR . '/module/Application/view/error/index.phtml',
    ],
    'template_path_stack' => [
      'admin' => __DIR__ . '/../view',
    ],
    'strategies' => [
      'ViewJsonStrategy',
    ],
  ],
  'session_containers' => [
    'HusSessionContainer'
  ],
  'view_helpers' => [
    'factories' => [
      \Application\View\HelperView::class => \Application\View\Factory\HelperViewFactory::class,
    ],
    'aliases' => [
      'config' => \Application\View\HelperView::class
    ]
  ],
];
