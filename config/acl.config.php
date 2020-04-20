<?php
/**
 * @author Kevin <khoa@husol.org>
 *
 */

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
    'CLIENT' => [
      'Application\Controller\IndexController' => [
        'allow' => ['index'],
        'deny' => null
      ],
    ],

    //For Admin
    'ADMIN' => null
  ],

  'roles' => [
    'CLIENT' => null,
    'STAFF' => ['CLIENT'],
    'SUPER_STAFF' => ['STAFF'],
    'ADMIN' => null,
  ],
];
