<?php
//gzip compress Website
ob_start("ob_gzhandler");

//////////////////////////////////////////////////////////////////// START AJAX POST-METHODES ////////////////////////////////////////////////////////////////////
// If this website recives a POST (Ajax) Request with variable "dbOperation", this PHP script is executed.
if (isset($_POST["dbOperation"])) {
  if ($_POST["dbOperation"] == "VIEW") {
    // ########################### START (VIEW Beers per Team) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    // Get current calculation option for team points
    $calcOption = "null";

    $sqlOpt = "SELECT o_ID, o_value FROM options WHERE o_ID = 1";
    $queryOpt = mysqli_query($db, $sqlOpt);

    while ($rowOpt = mysqli_fetch_array($queryOpt, MYSQLI_ASSOC)) {
      if ($rowOpt['o_value'] == "oneToOne") {
        $calcOption = "oneToOne";
      } elseif ($rowOpt['o_value'] == "oneToTeamSize") {
        $calcOption = "oneToTeamSize";
      }
    }

    $sql = "SELECT team.t_ID, t_name, COUNT(beer.b_ID) as sumbeer FROM team JOIN person ON team.t_ID = person.t_ID JOIN beer ON person.p_ID = beer.p_ID GROUP BY team.t_ID ORDER BY sumbeer";
    $query = mysqli_query($db, $sql);
    while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
      $teamId[] = $row['t_ID'];
      $teamName[] = $row['t_name'];

      // Get number of persons in a team
      $sqlNumPers = "SELECT COUNT(p_ID) as sumpers FROM team JOIN person ON team.t_ID = person.t_ID WHERE team.t_ID =" . $row['t_ID'];
      $queryNumPers = mysqli_query($db, $sqlNumPers);
      $sumpers = 0;
      while ($rowNumPers = mysqli_fetch_array($queryNumPers, MYSQLI_ASSOC)) {
        $sumpers = $rowNumPers['sumpers'];
      }

      if ($calcOption == "oneToOne") {
        $sumbeer[] = $row['sumbeer'] - $sumpers;

      } else if ($calcOption == "oneToTeamSize") {
        $sumbeer[] = round(($row['sumbeer'] - $sumpers) / $sumpers, 2);
      }
    }

    //Sending AJAX Response (Answer)
    echo json_encode($teamId);
    echo ";";
    echo json_encode($teamName);
    echo ";";
    echo json_encode($sumbeer);
    exit();
    // ########################### END (VIEW Beers per Team) ########################### 
  }
  exit();
}
//////////////////////////////////////////////////////////////////// END AJAX POST-METHODES ////////////////////////////////////////////////////////////////////
?>

<?php
// Connect to database
include_once("php_includes/db_connect.php");

// Get number of teams from database.
$sql = "SELECT * FROM team";
$query = mysqli_query($db, $sql);
$teamNumRows = mysqli_num_rows($query);
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
    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      display: flex;
      align-items: flex-end;
      justify-content: left;
      overflow: hidden;
      cursor: pointer;
      height: 100vh;
    }

    .chart {
      display: flex;
      flex-direction: row;
      position: absolute;
      height: 100%;
      align-items: flex-end;
    }

    .bar {
      flex: 1;
      background-color: #ebbb40;
      margin: 0 5px;
      text-align: center;
      color: white;
      max-width: 400px;
      min-height: 85px;
    }

    .bar-text {
      font-family: Arial, Helvetica, sans-serif;
      font-weight: bold;
      word-break: break-all;
      word-wrap: break-word;
      position: relative;
      bottom: 100px;
      margin: 0px;
      z-index: 1000;
      color: white;
      text-shadow: -1px -1px 0 #000000, 1px -1px 0 #000000, -1px 1px 0 #000000,
        1px 1px 0 #000000, -2px 0 0 #000000, 2px 0 0 #000000, 0 2px 0 #000000,
        0 -2px 0 #000000;
    }

    section {
      display: inline-block;
      position: relative;
      overflow: hidden;
      bottom: 100px;
    }

    section .air {
      position: relative;
      bottom: 0;
      width: 400px;
      height: 25px;
      background: url("beer.png");
      background-size: 400px 25px;
      border-radius: 100px 100px 0px 0px;
    }

    section .air.air1 {
      animation: wave 30s linear infinite;
      z-index: 1000;
      opacity: 1;
      animation-delay: 0s;
      bottom: -75px;
    }

    section .air.air2 {
      animation: wave2 15s linear infinite;
      z-index: 999;
      opacity: 0.5;
      animation-delay: -5s;
      bottom: -45px;
    }

    section .air.air3 {
      animation: wave 30s linear infinite;
      z-index: 998;
      opacity: 0.2;
      animation-delay: -2s;
      bottom: -16px;
    }

    section .air.air4 {
      animation: wave2 5s linear infinite;
      z-index: 997;
      opacity: 0.7;
      animation-delay: -5s;
      bottom: 7px;
    }

    @keyframes wave {
      0% {
        background-position-x: 0px;
      }

      100% {
        background-position-x: 400px;
      }
    }

    @keyframes wave2 {
      0% {
        background-position-x: 0px;
      }

      100% {
        background-position-x: -400px;
      }
    }
  </style>
</head>

<body onclick="redirectToBackend()">
  <div class="chart" id="chart">
    <?php
    for ($i = 0; $i < $teamNumRows; $i++) {
      echo '
      <div class="bar" id="barheight' . $i . '">
        <section>
          <div class="air air1"></div>
          <div class="air air2"></div>
          <div class="air air3"></div>
          <div class="air air4"></div>
        </section>

        <p class="bar-text" id="beernumbers' . $i . '" style="font-size: 40px;"></p>
        <div id="container' . $i . '" style="width: 400px; height: 40px;">
          <p id="teamname' . $i . '" class="bar-text"></p>
        </div>
      </div>
      ';
    }
    ?>
  </div>



  <!--AJAX Script bolck-->
  <script>
    // If clicked on any part of screen redirect to backend.php
    function redirectToBackend() {
      window.location = "backend.php";
    }

    // Set default font size
    var output = document.getElementsByClassName('output');
    for (var i = 0; i < output.length; ++i) {
      var item = output[i];
      item.style.fontSize = "40px";
    }


    // AJAX function that gets infos about the number of beer for each team
    function getTeamBeersInfos() {
      var dbOperation = "VIEW";

      var ajax = ajaxObj("POST", "index.php");
      ajax.onreadystatechange = function() {
        if (ajaxReturn(ajax) == true) {
          var result = ajax.responseText.trim();
          var subresults = result.split(';');

          var teamIdArray = JSON.parse(subresults[0]);
          var teamNameArray = JSON.parse(subresults[1]);
          var sumbeerArray = JSON.parse(subresults[2]);

          // Get the highest number of beers of the best team. 
          sumbeerArray.sort(function(a, b){return a-b});
          var beerhighscore = sumbeerArray[teamIdArray.length - 1];

          // Update view Modal Content
          for (let i = 0; i < teamIdArray.length; i++) {

            //Calculate relative height of each bar in the chart
            var heightofbar = (100 / beerhighscore) * sumbeerArray[i];

            document.getElementById('barheight' + i).style.height = "calc(" + heightofbar + "% - 35px)";
            document.getElementById('teamname' + i).innerHTML = teamNameArray[i];
            document.getElementById('beernumbers' + i).innerHTML = "Biere: " + sumbeerArray[i];

            document.getElementById('teamname' + i).style.fontSize = "40px";
            // Resize Team name to fit in a bar.
            resize_to_fit(i);
            // Call the scrollHorizontally function to scroll to the right of the screen if there are too many bars to dislay at once. 
            setTimeout(scrollHorizontally, 7000);
          }
        }
      }
      ajax.send("dbOperation=" + dbOperation);
    }


    function resize_to_fit(i) {
      var teamname = document.getElementById('teamname' + i);
      var container = document.getElementById('container' + i);
      let fontSize = window.getComputedStyle(teamname).fontSize;
      teamname.style.fontSize = parseFloat(fontSize) - 1 + "px";

      if (teamname.clientHeight >= container.clientHeight) {
        resize_to_fit(i);
      }
    }

    // Function to scroll the body horizontally
    function scrollHorizontally() {
      let scrollInterval = 100; // Adjust the scroll interval (in milliseconds) as needed
      let endDelay = 7000; // 5 seconds end delay

      let scrollcontainer = document.documentElement;
      scrollcontainer.scrollTo(scrollcontainer.scrollLeft + 10, 0);

      // Check if scrolling has reached the end
      if (scrollcontainer.scrollLeft >= scrollcontainer.scrollWidth - window.innerWidth) {
        setTimeout(function() {
          location.reload(true);
          window.location.href = window.location.href;
          window.scrollTo(0, 0);
        }, endDelay);
        return; // Stop scrolling
      }
      setTimeout(scrollHorizontally, scrollInterval);
    }

    // Start scrolling automatically with a delay when the page loads
    window.onload = function() {
      getTeamBeersInfos();
    };
  </script>
</body>

</html>