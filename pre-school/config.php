<?php
// Database connection configuration using pg_connect
$host = "host=localhost";
$dbname = "dbname=pre_school";
$user = "user=postgres";
$password = "password=root";

$conn_string = "$host $dbname $user $password";

$dbconn = pg_connect($conn_string);

if (!$dbconn) {
    die("Error in connection: " . pg_last_error());
}
?>
