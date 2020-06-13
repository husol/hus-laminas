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
    ],
    'Application' => [
      'Application\Controller\IndexController',
    ],
  ],

  'rules' => [
    ROLE_CLIENT => [
      'Application\Controller\IndexController' => [
        'allow' => ['index'],
        'deny' => null
      ],
    ],

    //For Admin
    ROLE_ADMIN => null
  ],

  'roles' => [
    ROLE_CLIENT => null,
    ROLE_STAFF => [ROLE_CLIENT],
    ROLE_ADMIN => null,
  ],
];
