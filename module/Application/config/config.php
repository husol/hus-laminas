<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
  //Log Level in ERROR, DEBUG, INFO
  'LOG_LEVEL' => 'DEBUG',
  'IS_LIVE' => false,
  'HARD_DELETED_TABLES' => ['comments', 'orders'],
  //Google Captcha Setting
  'CAPTCHA'=> [
    'siteKey' => '{Google Captcha Site Key}',
    'secretKey' => '{Google Captcha Secret Key}'
  ],
  //SMTP Setting for sending email
  'SMTP_OPTIONS' => [
    'name' => '{System Name}',//Husol
    'host' =>'{SMTP_HOST}',//smtp.gmail.com
    'port' => 587,//587
    'connection_class'  => 'plain',
    'connection_config' => [
      'username' => '{SMTP_USERNAME}',//Gmail account
      'password' => '{SMTP_PASSWORD}',//Gmail password
      'ssl'      => 'tls',
    ]
  ],

  'SERVICES' => [
    'HUS_HOST' => 'https://services.husol.xyz/api'
  ],
];
