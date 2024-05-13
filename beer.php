<?php
//gzip compress Website
ob_start("ob_gzhandler");

//////////////////////////////////////////////////////////////////// START AJAX POST-METHODES ////////////////////////////////////////////////////////////////////
// If this website recives a POST (Ajax) Request with variable "dbOperation", this PHP script is executed.
if (isset($_POST["dbOperation"])) {
  if ($_POST["dbOperation"] == "ADD-BEER") {
    // ########################### START (ADD Beer) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $qrcode = trim($_POST["qrcode"]);

    $personId = "null";
    $name = "null";
    $firstname = "null";

    // Get all persons and compare qrcode value with person values
    $sql = "SELECT p_ID, p_name, p_firstname FROM person";
    $query = mysqli_query($db, $sql);
    while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {

      $personhash = hash('sha256', $row['p_ID'] . $row['p_name'] . $row['p_firstname']);
      if ($qrcode == $personhash) {
        $personId = $row['p_ID'];
        $name = $row['p_name'];
        $firstname = $row['p_firstname'];
        // Insert new Beer into database
        $sqlBeer = "INSERT INTO beer (p_ID)       
        VALUES('$personId')";
        $queryBeer = mysqli_query($db, $sqlBeer);
        break;
      }
    }
    //Sending AJAX Response (Answer)
    echo $personId;
    echo ";";
    echo $name;
    echo ";";
    echo $firstname;
    exit();
    // ########################### END (ADD Beer) ########################### 
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
  <style>
    #fadeoutdiv {
      animation: fadeout 4s linear 1 forwards;
    }

    @keyframes fadeout {
      0% {
        opacity: 1;
      }

      100% {
        opacity: 0;
      }
    }
  </style>
</head>

<body>
  <p>
    <a href="/">Frontend page</a>
    |
    <a href="backend.php">Backend page</a>
    |
    <a href="print.php">Print QR-Codes page</a>
    |
    <b>Add Beer page</b>
  </p>


  <form onSubmit="return false;">
    <h1>Add Beer</h1>
    <label for="qrcodeInput" name="qrcodeInputLabel">Content of qrcode:</label>
    <input type="text" id="qrcodeInput" size="64" placeholder="72ec5cf3cdddf30360707f01192575215c6255e13f6bb236cb997b4e6b44ba2d" name="qrcodeInput" onblur="this.focus();" autofocus required>
    <br>
    <button type="button" id="submitbtnAdd" onclick="addBeer()" aria-label="Submit">Submit</button>
    <br>
    <span id="beerAddStatus"></span>
  </form>






  <!--AJAX Script bolck-->
  <script>
    // Submit form with ENTER
    document.onkeydown = function() {
      if (window.event.keyCode == '13') {
        addBeer();
      }
    }

    // Sleep function
    function sleep(milliseconds) {
      const date = Date.now();
      let currentDate = null;
      do {
        currentDate = Date.now();
      } while (currentDate - date < milliseconds);
    }


    // AJAX function that adds a beer and assign it to a person
    function addBeer() {
      var qrcode = document.getElementById("qrcodeInput").value;

      document.getElementById("qrcodeInput").setAttribute("disabled","");

      var dbOperation = "ADD-BEER";

      if (qrcode == "") {
        document.getElementById("beerAddStatus").innerHTML = `<div id="fadeoutdiv" style="height: 300px; width: 100%; background-color: red; display: flex; justify-content: center; align-items: center; font-size: 7vw;">QRcode Input field is empty</div>`;
        document.getElementById("qrcodeInput").removeAttribute("disabled","");

      } else {
        document.getElementById("beerAddStatus").innerHTML = ``;

        var ajax = ajaxObj("POST", "beer.php");
        ajax.onreadystatechange = function() {
          if (ajaxReturn(ajax) == true) {
            var result = ajax.responseText.trim();
            var subresults = result.split(';');

            // Clear qrcode Input field
            document.getElementById("qrcodeInput").value = "";

            var personId = subresults[0];
            var name = subresults[1];
            var firstname = subresults[2];

            if (personId == "null" || name == "null" || firstname == "null") {
              document.getElementById("beerAddStatus").innerHTML = `<div id="fadeoutdiv" style="height: 300px; width: 100%; background-color: red; display: flex; justify-content: center; align-items: center; font-size: 7vw;">QR-Code error</div>`;
              setTimeout(enableInputField, 2000);

            } else {
              document.getElementById("beerAddStatus").innerHTML = `<div id="fadeoutdiv" style="height: 300px; width: 100%; background-color: green; display: flex; justify-content: center; align-items: center; font-size: 4vw;">Added beer to person (` + personId + ` ` + name + ` ` + firstname + `)</div>`;
              setTimeout(enableInputField, 2000);
            }
          }
        }
        ajax.send("dbOperation=" + dbOperation + "&qrcode=" + qrcode);
      }
    }

    function enableInputField(){
      document.getElementById("qrcodeInput").removeAttribute("disabled","");
      document.getElementById("qrcodeInput").focus();
    }

    // Get infos abot the teams on page load
    window.onload = function() {
      document.getElementById("qrcodeInput").focus();
    };
  </script>
</body>

</html>