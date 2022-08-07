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
      'forbidden' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/error/403',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action' => 'error403',
          ],
        ],
      ],
      'register' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/register',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action' => 'registerForm',
          ],
        ],
      ],
      'login' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/sign-in',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action' => 'index',
          ],
        ],
      ],
      'logout' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/sign-out',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action' => 'logout',
          ],
        ],
      ],
      'auth' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/auth/:action',
          'defaults' => [
            'controller' => Controller\AuthController::class,
            'action' => 'index',
          ],
        ],
      ],
      'home' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/[/:action]',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'index',
          ],
        ],
      ],
      'products' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/products[/:action]',
          'defaults' => [
            'controller' => Controller\ProductController::class,
            'action' => 'index',
          ],
        ],
      ],
      'product-detail' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/products/:slug',
          'defaults' => [
            'controller' => Controller\ProductController::class,
            'action' => 'detail',
          ],
          'constraints' => [
            'slug' => '\d+_.*',
          ],
        ],
      ],
      'cart' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/cart[/:action]',
          'defaults' => [
            'controller' => Controller\CartController::class,
            'action' => 'index',
          ],
        ],
      ],
      'cart-payment' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/cart/:id/payment',
          'defaults' => [
            'controller' => Controller\CartController::class,
            'action' => 'payment',
          ],
          'constraints' => [
            'id' => '\d+',
          ],
        ],
      ],
      'cart-payment-success' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/cart/payment/success',
          'defaults' => [
            'controller' => Controller\CartController::class,
            'action' => 'success',
          ],
        ],
      ],
      'cart-payment-cancel' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/cart/payment/cancel',
          'defaults' => [
            'controller' => Controller\CartController::class,
            'action' => 'cancel',
          ],
        ],
      ],
      'about' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/about[/:action]',
          'defaults' => [
            'controller' => Controller\AboutController::class,
            'action' => 'index',
          ],
        ],
      ],
      'contact' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/contact[/:action]',
          'defaults' => [
            'controller' => Controller\ContactController::class,
            'action' => 'index',
          ],
        ],
      ],
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
      Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
      Controller\ProductController::class => Controller\Factory\ProductControllerFactory::class,
      Controller\AboutController::class => Controller\Factory\AboutControllerFactory::class,
      Controller\CartController::class => Controller\Factory\CartControllerFactory::class,
      Controller\ContactController::class => Controller\Factory\ContactControllerFactory::class,
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
