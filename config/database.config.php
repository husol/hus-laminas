<?php
return [
  'main' => [
    'driver'  => 'Pdo_Mysql',
    'hostname'=> 'localhost',
    'port'    => '3306',
    'database'=> 'story',
    'charset'=> 'utf8',
    'username' => 'root',
    'password' => 'husol123ok'
  ],
  'slave' => [
    'driver'  => 'Pdo_Mysql',
    'hostname'=> 'localhost',
    'port'    => '3306',
    'database'=> 'story',
    'charset'=> 'utf8',
    'username' => 'root',
    'password' => 'husol123ok'
  ]
];