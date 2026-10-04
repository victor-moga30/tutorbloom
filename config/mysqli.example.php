<?php

$MYSQL_HOST = '127.0.0.1';
$MYSQL_USER = 'username_here';
$MYSQL_PASS = 'password_here';
$MYSQL_DB   = 'database_name_here';

$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);

if ($mysqli->connect_error) {
    die('MySQLi connection failed.');
}

$mysqli->set_charset('utf8mb4');