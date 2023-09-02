<?php
// Connect to database
include("php_includes/db_connect.php");

// Create Database
$sql = "CREATE DATABASE beerometer_db";
$query = mysqli_query($db, $sql);
if ($query === TRUE) {
  // Connect to database
  include("php_includes/db_connect.php");
  echo "<p>Database created :)</p>";
} else {
  echo "<p>Database not created :(</p>";
}

// Create Team-Table
if (!mysqli_query(
    $db,
    "CREATE TABLE team ( 
      t_ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      t_name VARCHAR(200) NOT NULL DEFAULT 'Teamname'
  )"
  )) {
    echo ("Error description: " . mysqli_error($db));
  } else {
    echo "<p>Team Table created :)</p>";
  }



// Create Person-Table
if (!mysqli_query(
    $db,
    "CREATE TABLE person ( 
      p_ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      t_ID INT(10) UNSIGNED NOT NULL,
      p_name VARCHAR(100) NOT NULL,
      p_firstname VARCHAR(100) NOT NULL,
      FOREIGN KEY (t_ID) REFERENCES team(t_ID) ON UPDATE CASCADE ON DELETE CASCADE
  )"
  )) {
echo ("Error description: " . mysqli_error($db));
} else {
echo "<p>Person Table created :)</p>";
}




// Create Beer-Table
if (!mysqli_query(
  $db,
    "CREATE TABLE beer ( 
        b_ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        p_ID INT(10) UNSIGNED NOT NULL,
        b_timestamp TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        FOREIGN KEY (p_ID) REFERENCES person(p_ID) ON UPDATE CASCADE ON DELETE CASCADE
    )"
)) {
echo ("Error description: " . mysqli_error($db));
} else {
echo "<p>Beer Table created :)</p>";
}