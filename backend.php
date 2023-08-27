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

    $sql = "SELECT t_ID, t_name FROM team";
    $query = mysqli_query($db, $sql);

    $sumbeers = array();
    while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
      $teamId[] = $row['t_ID'];
      $teamName[] = $row['t_name'];
      // Get number of beers per team
      $sqlBeer = "SELECT COUNT(beer.b_ID) as sumbeer FROM team JOIN person ON team.t_ID = person.t_ID JOIN beer ON person.p_ID = beer.p_ID WHERE team.t_ID =" . $row['t_ID'] . " GROUP BY team.t_ID";
      $queryBeer = mysqli_query($db, $sqlBeer);
      while ($rowBeer = mysqli_fetch_array($queryBeer, MYSQLI_ASSOC)) {
        $sumbeers[] = $rowBeer['sumbeer'];
      }


      // Get persons of team
      $sqlPers = "SELECT p_ID, p_name, p_firstname FROM person WHERE t_ID=" . $row['t_ID'];
      $queryPers = mysqli_query($db, $sqlPers);
      $personInfo = "";

      while ($rowPers = mysqli_fetch_array($queryPers, MYSQLI_ASSOC)) {
        // Get number of beers per person
        $sqlBeerPers = "SELECT COUNT(beer.b_ID) as sumbeer FROM person JOIN beer ON person.p_ID = beer.p_ID WHERE person.p_ID =" . $rowPers['p_ID'];
        $queryBeerPers = mysqli_query($db, $sqlBeerPers);
        $beersPers = 0;
        while ($rowBeerPers = mysqli_fetch_array($queryBeerPers, MYSQLI_ASSOC)) {
          $beersPers = $rowBeerPers['sumbeer'];
        }

        $personInfo = $personInfo . '<tr><td>' . $rowPers['p_ID'] . '</td><td>' . $rowPers['p_name'] . '</td><td>' . $rowPers['p_firstname'] . '</td><td>' . $beersPers . '</td><td><a onclick="deletePerson(' . $rowPers['p_ID'] . ')" aria-label="Delete person">🗑️</a></td></tr>';
      }
      $personInfos[] = $personInfo;

      $delTeam[] = '<a onclick="deleteTeam(' . $row['t_ID'] . ')" aria-label="Delete team">🗑️</a>';
    }

    //Sending AJAX Response (Answer)
    echo json_encode($teamId);
    echo ";";
    echo json_encode($teamName);
    echo ";";
    echo json_encode($sumbeers);
    echo ";";
    echo json_encode($personInfos);
    echo ";";
    echo json_encode($delTeam);
    exit();
    // ########################### END (VIEW Beers per Team) ########################### 
  } else if ($_POST["dbOperation"] == "ADD-TEAM") {
    // ########################### START (ADD Team) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $teamname = $_POST["teamname"];

    // Insert new Team into database
    $sql = "INSERT INTO team (t_name)       
        VALUES('$teamname')";
    $query = mysqli_query($db, $sql);

    //Sending AJAX Response (Answer)
    echo "ok";
    exit();
    // ########################### END (ADD Team) ########################### 
  } else if ($_POST["dbOperation"] == "DELETE-TEAM") {
    // ########################### START (DELETE Team) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $teamId = $_POST["teamId"];

    $sql = "DELETE FROM team 
    WHERE t_id = $teamId";
    $query = mysqli_query($db, $sql);

    //Sending AJAX Response (Answer)
    echo "ok";
    exit();
    // ########################### END (DELETE Team) ########################### 
  } else if ($_POST["dbOperation"] == "ADD-PERSON") {
    // ########################### START (ADD Person) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $name = $_POST["name"];
    $firstname = $_POST["firstname"];
    $teamId = $_POST["teamId"];

    // Insert new Team into database
    $sql = "INSERT INTO person (p_name, p_firstname, t_id)       
        VALUES('$name', '$firstname', '$teamId')";
    $query = mysqli_query($db, $sql);

    //Sending AJAX Response (Answer)
    echo "ok";
    exit();
    // ########################### END (ADD Person) ########################### 
  } else if ($_POST["dbOperation"] == "DELETE-PERSON") {
    // ########################### START (DELETE Person) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $personId = $_POST["personId"];

    $sql = "DELETE FROM person 
    WHERE p_id = $personId";
    $query = mysqli_query($db, $sql);

    //Sending AJAX Response (Answer)
    echo "ok";
    exit();
    // ########################### END (DELETE Person) ########################### 
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
  <script src="js/qrcode.min.js"></script>
  <style>
    table,
    th,
    td {
      border: 1px solid black;
      border-collapse: collapse;
      text-align: center;
    }

    .personTableTd {
      float: right;
    }

    a {
      cursor: pointer;
    }
  </style>
</head>

<body>
  <p>
    <a href="/">To Frontend Page</a>
    |
    <a href="backend.php">To Backend Page</a>
    |
    <a href="beer.php">To Add Beer Page</a>
  </p>

  <div style="display: inline-block;">
    <form onSubmit="return false;">
      <h1>Add Team</h1>
      <label for="teamnameInput" name="teamnameInputLabel">Team name:</label>
      <input type="text" class="form-control" id="teamnameInput" placeholder="Team name" name="teamnameInput" required>
      <br>
      <button type="submit" id="submitbtnAdd" onclick="addTeam()" aria-label="Submit">Submit</button>
      <br>
      <span id="teamAddStatus"></span>
    </form>
  </div>

  <div style="display: inline-block; margin-left: 10px;">
    <form onSubmit="return false;">
      <h1>Add Person</h1>
      <label for="nameInput" name="nameInputLabel">Name:</label>
      <input type="text" class="form-control" id="nameInput" placeholder="Name" name="nameInput" required>
      <br>
      <label for="firstnameInput" name="firstnameInputLabel">First name:</label>
      <input type="text" class="form-control" id="firstnameInput" placeholder="First name" name="firstnameInput" required>
      <br>
      <label for="teamDropDown">Team:</label>

      <select name="teamDropDown" id="teamDropDown" required>
        <?php
        // Connect to database
        include_once("php_includes/db_connect.php");

        $sql = "SELECT t_ID, t_name FROM team";
        $query = mysqli_query($db, $sql);
        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
          echo '<option value="' . $row['t_ID'] . '">' . $row['t_name'] . '</option>';
        }
        ?>
      </select>
      <br>
      <button type="submit" id="submitbtnAdd" onclick="addPerson()" aria-label="Submit">Submit</button>
      <br>
      <span id="personAddStatus"></span>
    </form>
  </div>


  <h1>Team-List</h1>

  <table>
    <thead>
      <tr>
        <th>Team-ID</th>
        <th>Team name</th>
        <th>Beers</th>
        <th>Members</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody id="teamlist">

    </tbody>
  </table>




  <!--AJAX Script bolck-->
  <script>
    // Submit form with ENTER
    document.onkeydown = function() {
      if (window.event.keyCode == '13') {
        addBeer();
      }
    }

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



    // AJAX function that gets infos about teams
    function getTeamInfos() {
      var dbOperation = "VIEW";

      var ajax = ajaxObj("POST", "backend.php");
      ajax.onreadystatechange = function() {
        if (ajaxReturn(ajax) == true) {
          var result = ajax.responseText.trim();
          var subresults = result.split(';');

          var teamIdArray = JSON.parse(subresults[0]);
          var teamNameArray = JSON.parse(subresults[1]);
          var sumbeersArray = JSON.parse(subresults[2]);
          var personInfosArray = JSON.parse(subresults[3]);
          var delTeamArray = JSON.parse(subresults[4]);

          // Update view Modal Content
          for (let i = 0; i < teamIdArray.length; i++) {
            document.getElementById('teamlist').innerHTML += "<tr><td>" + teamIdArray[i] + "</td><td>" + teamNameArray[i] + "</td><td>" + sumbeersArray[i] + "</td><td class=\"personTableTd\"> <table><thead><tr><td>Person-ID</td><td>Name</td><td>First name</td><td>Beers</td><td>Delete</td><td>QR-Code value</td><td>QR-Code</td></thead><tbody class=\"personlist\">" + personInfosArray[i] + "</tbody></table></td><td>" + delTeamArray[i] + "</td></tr>";
          }

          // Generate QR-Code for each person
          var persontable = document.getElementsByClassName("personlist");

          for (let i = 0; i < persontable.length; i++) {
            var tr = persontable[i].getElementsByTagName("tr");
            for (let j = 0; j < tr.length; j++) {
              var td = tr[j].getElementsByTagName("td");
              var personString = "";
              for (let k = 0; k < td.length; k++) {
                if (k < 3) {
                  personString += td[k].innerHTML;
                }
              }

              // Create a new QR code instance
              hash(personString).then((hex) => {
                //Add QR <td>-Element to table for (QR-Code-Value) column
                var x = tr[j].insertCell();
                x.innerHTML = hex;

                //Add QR <td>-Element to table for (QR-Code) colum
                var x = tr[j].insertCell();
                x.innerHTML = '<div id="qrcode' + i + j + '" style="margin: 5px;"></div>';

                var qrcode = new QRCode(document.getElementById("qrcode" + i + j), {
                  text: hex,
                  width: 128,
                  height: 128
                });
              });

            }
          }

        }
      }
      ajax.send("dbOperation=" + dbOperation);
    }


    // AJAX function that adds a team
    function addTeam() {
      var teamname = document.getElementById("teamnameInput").value;
      var dbOperation = "ADD-TEAM";

      if (teamname == "") {
        document.getElementById("teamAddStatus").innerHTML = `<b>Team name is empty</b>`;
        exit();
      } else {
        document.getElementById("teamAddStatus").innerHTML = ``;

        var ajax = ajaxObj("POST", "backend.php");
        ajax.onreadystatechange = function() {
          if (ajaxReturn(ajax) == true) {
            var result = ajax.responseText.trim();

            // Reload Page
            location.reload(true);
            window.location.href = window.location.href;
            window.scrollTo(0, 0);
          }
        }
        ajax.send("dbOperation=" + dbOperation + "&teamname=" + teamname);
      }
    }

    // AJAX function that deletes a team
    function deleteTeam(teamId) {
      var dbOperation = "DELETE-TEAM";

      var ajax = ajaxObj("POST", "backend.php");
      ajax.onreadystatechange = function() {
        if (ajaxReturn(ajax) == true) {
          var result = ajax.responseText.trim();

          // Reload Page
          location.reload(true);
          window.location.href = window.location.href;
          window.scrollTo(0, 0);
        }
      }
      ajax.send("dbOperation=" + dbOperation + "&teamId=" + teamId);
    }


    // AJAX function that adds a person
    function addPerson() {
      var name = document.getElementById("nameInput").value;
      var firstname = document.getElementById("firstnameInput").value;
      var teamId = document.getElementById("teamDropDown").value;
      var dbOperation = "ADD-PERSON";

      if (name == "" || firstname == "" || teamId == "") {
        document.getElementById("personAddStatus").innerHTML = `<b>Name, firstname or teamId is empty</b>`;
        exit();
      } else {
        document.getElementById("personAddStatus").innerHTML = ``;

        var ajax = ajaxObj("POST", "backend.php");
        ajax.onreadystatechange = function() {
          if (ajaxReturn(ajax) == true) {
            var result = ajax.responseText.trim();

            // Reload Page
            location.reload(true);
            window.location.href = window.location.href;
            window.scrollTo(0, 0);
          }
        }
        ajax.send("dbOperation=" + dbOperation + "&name=" + name + "&firstname=" + firstname + "&teamId=" + teamId);
      }
    }

    // AJAX function that deletes a person
    function deletePerson(personId) {
      var dbOperation = "DELETE-PERSON";

      var ajax = ajaxObj("POST", "backend.php");
      ajax.onreadystatechange = function() {
        if (ajaxReturn(ajax) == true) {
          var result = ajax.responseText.trim();

          // Reload Page
          location.reload(true);
          window.location.href = window.location.href;
          window.scrollTo(0, 0);
        }
      }
      ajax.send("dbOperation=" + dbOperation + "&personId=" + personId);
    }


    // Get infos abot the teams on page load
    window.onload = function() {
      getTeamInfos();
    };
  </script>
</body>

</html>