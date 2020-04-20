<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Laminas\Router\Http\Segment;

return [
  'router' => [
    'routes' => [
      'login' => [
        'type'    => Segment::class,
        'options' => [
          'route'    => '/sign-in',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action'     => 'index',
          ],
        ],
      ],
      'forbidden' => [
        'type'    => Segment::class,
        'options' => [
          'route'    => '/error/403',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action'     => 'error403',
          ],
        ],
      ],
      'auth' => [
        'type'    => Segment::class,
        'options' => [
          'route'    => '/auth[/:action]',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action'     => 'index',
          ],
        ],
      ],
      'home' => [
        'type' => Segment::class,
        'options' => [
          'route'    => '/[/:action]',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action'     => 'index',
          ],
        ],
      ],
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
      Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
    ],
  ],
  'view_manager' => [
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_map' => [
      'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
      'index/index' => __DIR__ . '/../view/index/index.phtml',
      'error/404' => __DIR__ . '/../view/error/404.phtml',
      'error/index' => __DIR__ . '/../view/error/index.phtml',
    ],
    'template_path_stack' => [
      'application' => __DIR__ . '/../view',
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