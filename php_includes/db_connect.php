<?php
// ## Testing ##
$db = mysqli_connect("localhost", "root", "", "beerometer_db");
//$db = mysqli_connect("mariadb", "root", "dbp@ss0rd1234", "beerometer_db");
// Überprüfung ob die Verbindung auch aufgebaut wurde
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
?>