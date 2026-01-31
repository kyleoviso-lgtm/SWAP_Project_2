<?php

// use this in other files to connect to db 
$connection = new mysqli('localhost', 'root', '', 'mydb');
if ($connection->connect_error) {
    die('Database connection failed: ' . $connection->connect_error);
}

// change this depending on the remote vs local db
define('LOCAL_MODE', true); 

?>


