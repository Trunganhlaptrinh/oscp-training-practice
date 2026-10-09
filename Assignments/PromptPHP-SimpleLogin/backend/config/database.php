<?php

$host = "localhost";
$username = "root";
$password = "root";
$database = "simple_login";

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Không thể kết nối database");
}

$connection->set_charset("utf8mb4");


