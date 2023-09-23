<?php
//gzip compress Website
ob_start("ob_gzhandler");

//////////////////////////////////////////////////////////////////// START AJAX POST-METHODES ////////////////////////////////////////////////////////////////////
// If this website recives a POST (Ajax) Request with variable "dbOperation", this PHP script is executed.
if (isset($_POST["dbOperation"])) {
  if ($_POST["dbOperation"] == "VIEW") {
    // ########################### START (VIEW Persons) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    // Get persons of team
    $sql = "SELECT p_ID, p_name, p_firstname FROM person";
    $query = mysqli_query($db, $sql);



    while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {

      $idArray[] = $row['p_ID'];
      $nameArray[] = $row['p_name'];
      $firstnameArray[] = $row['p_firstname'];
    }

    //Sending AJAX Response (Answer)
    echo json_encode($idArray);
    echo ";";
    echo json_encode($nameArray);
    echo ";";
    echo json_encode($firstnameArray);
    exit();
    // ########################### END (VIEW Persons) ########################### 
  }
  exit();
}
//////////////////////////////////////////////////////////////////// END AJAX POST-METHODES ////////////////////////////////////////////////////////////////////
?>






<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍺</text></svg>">
  <title>Beer-o-Meter</title>
  <script>
    // Redirect from http:// to https://
    var loc = window.location.href + '';
    if (loc.indexOf('http://') == 0) {
      window.location.href = loc.replace('http://', 'https://');
    }
  </script>
  <script src="js/ajax.js"></script>
  <script src="js/qrious.min.js"></script>

  <style>
    div {
      display: inline-block;
      margin: 0px;
      padding: 10px;
      border: 2px solid black;
    }

    p {
      margin: 0px;
      font-size: 10pt;
    }

    nav {
      margin-bottom: 10px;
    }

    @page {
      margin: 0;
    }

    @media print {
      * {
        font-family: "times new roman", times, serif;
        text-align: justify;
        font-size: 10pt;
        padding: 0px;
        margin: 0px;
      }

      nav {
        display: none;
      }

      body {
        margin: 4mm;
      }
    }
  </style>
</head>

<body>
  <nav>
    <a href="/">To Frontend Page</a>
    |
    <a href="backend.php">To Backend Page</a>
    |
    <a href="print.php">To Print QR-Codes Page</a>
    |
    <a href="beer.php">To Add Beer Page</a>
    <br>
    <h1>Press  Strg+P / Ctrl+P</h1>
  </nav>

  <span id="personlist">
  </span>





  <!--AJAX Script bolck-->
  <script>
    // SHA-265 function
    async function hash(string) {
      const utf8 = new TextEncoder().encode(string);
      const hashBuffer = await crypto.subtle.digest('SHA-256', utf8);
      const hashArray = Array.from(new Uint8Array(hashBuffer));
      const hashHex = hashArray
        .map((bytes) => bytes.toString(16).padStart(2, '0'))
        .join('');
      return hashHex;
    }


    // AJAX function that gets infos about persons
    function getPersonInfos() {
      var dbOperation = "VIEW";

      var ajax = ajaxObj("POST", "print.php");
      ajax.onreadystatechange = function() {
        if (ajaxReturn(ajax) == true) {
          var result = ajax.responseText.trim();
          var subresults = result.split(';');


          var IdArray = JSON.parse(subresults[0]);
          var nameArray = JSON.parse(subresults[1]);
          var firstnameArray = JSON.parse(subresults[2]);

          // Generate QR-Code for each person
          var persontable = document.getElementById("personlist");

          for (let i = 0; i < IdArray.length; i++) {
            personString = IdArray[i] + nameArray[i] + firstnameArray[i];

            //Add QR and name to <div>-Element
            persontable.innerHTML += '<div><canvas id="qrcode' + i + '" style="margin: 2mm;"></canvas><p>' + firstnameArray[i] + "<br>" + nameArray[i] + '</p></div>';
            // Create a new QR code instance
            hash(personString).then((hex) => {
              new QRious({
                element: document.getElementById("qrcode" + i),
                size: 100,
                value: hex
              });
            });
          }

        }
      }
      ajax.send("dbOperation=" + dbOperation);
    }


    // Get infos abot the teams on page load
    window.onload = function() {
      getPersonInfos();
    };
  </script>
</body>

</html>