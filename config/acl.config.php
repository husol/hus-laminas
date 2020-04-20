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
      //'Application\Controller\IndexController',
    ],
  ],

  'rules' => [
/*
    'SUPER_STAFF' => [
      'Application\Controller\CategoryController' => [
        'allow' => ['index'],
        'deny' => null
      ],
    ],
*/
    //For Admin
    'ADMIN' => null
  ],

  'roles' => [
/*
    'STAFF' => null,
    'SUPER_STAFF' => ['STAFF'],
*/
    'ADMIN' => null,
  ],
];
