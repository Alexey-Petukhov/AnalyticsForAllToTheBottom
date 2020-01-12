<?php

require_once 'rb.php';

$db =[
    'dsn' => 'mysql:host=localhost;dbname = bottom; charset=utf8',
    'user' => 'root',
    'pass' => 'root',
];

R::setup($db['dsn'],$db['user'], $db['pass']);
R::freeze(true);