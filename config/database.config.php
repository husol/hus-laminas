<?php
return [
  'main' => [
    'driver'    => 'Pdo_Mysql',
    'hostname'  => 'localhost',
    'port'      => '3306',
    'database'  => 'store',
    'charset'   => 'utf8',
    'username'  => 'root',
    'password'  => 'husol123ok'
  ],
  'slave' => [
    'driver'    => 'Pdo_Mysql',
    'hostname'  => '{DATABASE_HOST}',
    'port'      => '3306',
    'database'  => '{DATABASE_NAME}',
    'charset'   => 'utf8',
    'username'  => '{DB_USERNAME}',
    'password'  => '{DB_PASSWORD}'
  ]
];
