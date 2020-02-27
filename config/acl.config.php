<?php
/**
 * @author Kevin <khoa@husol.org>
 *
 */

return [
  'resources' => [
    'Application' => [
      'Application\Controller\HomeController',
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
