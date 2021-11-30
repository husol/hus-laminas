<?php
/**
 * @author Kevin <khoa@husol.org>
 *
 */

const ROLE_CLIENT = 0;
const ROLE_STAFF = 1;
const ROLE_ADMIN = 2;

return [
  'resources' => [
    'Admin' => [
      'Admin\Controller\HomeController',
      'Admin\Controller\UserController',
      'Admin\Controller\CategoryController',
      'Admin\Controller\ProductController',
    ],
    'Application' => [
      'Application\Controller\IndexController',
      'Application\Controller\ProductController',
      'Application\Controller\CartController',
      'Application\Controller\AboutController',
      'Application\Controller\ContactController',
    ],
  ],

  'rules' => [
    ROLE_CLIENT => [
      'Application\Controller\IndexController' => [
        'allow' => ['index'],
        'deny' => null
      ],
      'Application\Controller\ProductController' => [
        'allow' => ['index', 'detail'],
        'deny' => null
      ],
      'Application\Controller\CartController' => [
        'allow' => ['index', 'getListCart', 'confirm', 'save'],
        'deny' => null
      ],
      'Application\Controller\AboutController' => [
        'allow' => ['index'],
        'deny' => null
      ],
      'Application\Controller\ContactController' => [
        'allow' => ['index'],
        'deny' => null
      ],
    ],

    //For Admin
    ROLE_STAFF => null,
    ROLE_ADMIN => null
  ],

  'roles' => [
    ROLE_CLIENT => null,
    ROLE_STAFF => [ROLE_CLIENT],
    ROLE_ADMIN => null,
  ],
];
