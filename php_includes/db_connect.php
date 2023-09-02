<?php
$host="localhost";
$user="root";
$password="";
$dbname="beerometer_db";

$db = new mysqli($host, $user, $password);

if (! empty (mysqli_fetch_array(mysqli_query($db,"SHOW DATABASES LIKE '$dbname'"))))
{
    mysqli_close($db);
    $db = mysqli_connect($host, $user, $password, $dbname);
}
?>