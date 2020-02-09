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
      'Report\Controller\OtherReportController' => [
        'allow' => [],
        'deny' => null
      ],
    ],
*/
    //For Admin
    'ADMIN' => null
  ],

  'roles' => [
/*
    'ITM' => null,
    'OWNER' => ['ITM'],
*/
    'ADMIN' => null,
  ],
];
