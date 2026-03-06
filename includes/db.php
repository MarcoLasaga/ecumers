<?php

function get_db_connection(): mysqli
{
    static $conn;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        die('Database connection failed.');
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}