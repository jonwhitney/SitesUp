<?php
/* DSN & PDO */
//$dsn = "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=siteler";
//$dsn = "mysql:host=localhost;port=8888;dbname=siteler";
$dsn = 'mysql:host=sql5c40a.carrierzone.com;dbname=siteler_jonwhitney859512';
$username="jonwhitney859512";
$password="D0gbutt8!";

// admin / P1Gfarm3r

$db = new PDO($dsn, $username, $password);
//try {
//    $db = new PDO($dsn, $username, $password);
//} catch (PDOException $e) {
//    $error_message = $e->getMessage();
//    include('database_error.php');
//    exit();
//}
?>
