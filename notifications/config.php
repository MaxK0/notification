<?php

return [
  'db' => [
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'notification_db',
    'username' => 'root',
    'password' => ''
  ],
  'mail' => [
    'host' => 'smtp.gmail.com',
    'SMTPauth' => true,
    'port' => 465,
    'secure' => 'ssl',
    'username' => 'maxkoryakov1@gmail.com',
    'password' => 'edga quca rmwo ydbb',
    'charset' => 'UTF-8',
    'from_email' => 'maxkoryakov1@gmail.com',
    'from_name' => 'Компания x',
    'is_html' => true
  ],
  'bot' => [
    'token' => '6748061151:AAEeAYx13C62g7kX0C34_2IBxkHsaVLGXJw',
    'url' => 'https://api.telegram.org/bot'
  ],
];