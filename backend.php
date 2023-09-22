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

        // Get number of persons in a team
        $sqlNumPers = "SELECT COUNT(p_ID) as sumpers FROM team JOIN person ON team.t_ID = person.t_ID WHERE team.t_ID =" . $row['t_ID'];
        $queryNumPers = mysqli_query($db, $sqlNumPers);
        $sumpers = 0;
        while ($rowNumPers = mysqli_fetch_array($queryNumPers, MYSQLI_ASSOC)) {
          $sumpers = $rowNumPers['sumpers'];
        }

        if ($calcOption == "oneToOne") {
          $sumbeers[] = $rowBeer['sumbeer'] - $sumpers;

        } else if ($calcOption == "oneToTeamSize") {
          $sumbeers[] = number_format( round(($rowBeer['sumbeer'] - $sumpers) / $sumpers, 2), 2, ',' );
        }
        
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
          // Remove the first beer of person
          $beersPers = $rowBeerPers['sumbeer'] - 1;
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
  } else if ($_POST["dbOperation"] == "UPDATE-CALCOPT") {
    // ########################### START (UPDATE-CALCOPT) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $optionvalue = $_POST["optionvalue"];

    // Insert new Team into database
    $sql = "UPDATE options SET o_value='".$optionvalue."' WHERE o_ID=1";
    $query = mysqli_query($db, $sql);

    //Sending AJAX Response (Answer)
    echo "ok";
    exit();
    // ########################### END (UPDATE-CALCOPT) ########################### 
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

    // Insert new Person into database
    $sql = "INSERT INTO person (p_name, p_firstname, t_id)       
        VALUES('$name', '$firstname', '$teamId')";
    $query = mysqli_query($db, $sql);


    // Get p_ID of newly added person
    $newPersonId = mysqli_insert_id($db);

    // Insert first beer for added person
    $sql = "INSERT INTO beer (p_ID)       
        VALUES('$newPersonId')";
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
  } else if ($_POST["dbOperation"] == "MASS-IMPORT") {
    // ########################### START (MASS IMPORT) ########################### 
    // Connect to database
    include_once("php_includes/db_connect.php");

    $myfile = $_POST["myfile"];

    $lineArray=explode("\n",$myfile);
    for ($i=0; $i < sizeof($lineArray) ; $i++) { 
      
      // Skip first line of csv file
      if ($i > 0) {
        $teamAndUserArray=explode(";",$lineArray[$i]);
        
        //Check if team already exists
        $sql = "SELECT count(t_ID) as numofteams FROM team WHERE t_name='".$teamAndUserArray[0]."'";
        $query = mysqli_query($db, $sql);
        $numOfTeams = 0;
        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
          $numOfTeams = $row['numofteams'];
        }
        
        // Create team if not exsistent
        if ($numOfTeams == 0) {
          // Insert new Team into database
          $sql = "INSERT INTO team (t_name)       
          VALUES('".$teamAndUserArray[0]."')";
          $query = mysqli_query($db, $sql);
        }

        //Get teamId
        $sql = "SELECT t_ID FROM team WHERE t_name='".$teamAndUserArray[0]."'";
        $query = mysqli_query($db, $sql);
        $teamId = 0;
        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
          $teamId = $row['t_ID'];
        }

        // Insert new Person into database
        $sql = "INSERT INTO person (p_name, p_firstname, t_id)       
        VALUES('".$teamAndUserArray[1]."', '".$teamAndUserArray[2]."', '".$teamId."')";
        $query = mysqli_query($db, $sql);

        // Get p_ID of newly added person
        $newPersonId = mysqli_insert_id($db);

        // Insert first beer for added person
        $sql = "INSERT INTO beer (p_ID)       
            VALUES('$newPersonId')";
        $query = mysqli_query($db, $sql);
          
        
      }
      
    }

    //Sending AJAX Response (Answer)
    echo "ok".sizeof($lineArray).$teamAndUserArray[2].$teamAndUserArray[1].$teamAndUserArray[0];
    exit();
    // ########################### END (MASS IMPORT) ########################### 
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
    table,
    th,
    td {
      border: 2px solid black;
      border-collapse: collapse;
      text-align: center;
    }

    div {
      display: inline-block; 
      margin: 10px;
      padding: 10px; 
      border: 2px solid black;
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



  <div>
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



  <div>
    <form onSubmit="return false;">
      <h1>Add Person</h1>
      <label for="firstnameInput" name="firstnameInputLabel">First name:</label>
      <input type="text" class="form-control" id="firstnameInput" placeholder="First name" name="firstnameInput" required>
      <br>
      <label for="nameInput" name="nameInputLabel">Name:</label>
      <input type="text" class="form-control" id="nameInput" placeholder="Name" name="nameInput" required>
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



  <div>
    <form onSubmit="return false;">
      <h1>Calculation of points</h1>
      <?php
        // Get Options from Options-Table
        // Connect to database
        include_once("php_includes/db_connect.php");

        $sql = "SELECT o_ID, o_value FROM options WHERE o_ID = 1";
        $query = mysqli_query($db, $sql);

        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
          if ($row['o_value'] == "oneToOne") {
            echo '
            <input type="radio" id="oneToOne" name="cal_option" value="oneToOne" checked>
            <label for="oneToOne">1 Beer = 1 Point</label>
            <br>
            <input type="radio" id="oneToTeamSize" name="cal_option" value="oneToTeamSize">
            <label for="oneToTeamSize">1 Beer / number of team members = 0,XX Points</label>
            <br>
            <br>
            Current active option:
            <br>
            <b>1 Beer = 1 Point</b>
            <br>';

          } else if ($row['o_value'] == "oneToTeamSize"){
            echo '
            <input type="radio" id="oneToOne" name="cal_option" value="oneToOne">
            <label for="oneToOne">1 Beer = 1 Point</label>
            <br>
            <input type="radio" id="oneToTeamSize" name="cal_option" value="oneToTeamSize" checked>
            <label for="oneToTeamSize">1 Beer / number of team members = 0,XX Points</label>
            <br>
            <br>
            Current active option:
            <br>
            <b>1 Beer / number of team members = 0,XX Points</b>
            <br>';
          }
        }
      ?>
      <button type="submit" id="submitbtnCalc" onclick="updateCalcOfPoints()" aria-label="Submit">Submit</button>
    </form>
  </div>



  <div>
    <form onSubmit="return false;">
      <h1>Mass Import</h1>
      Template: <a href="import.csv" download="import.csv">import.csv</a>
      <br>
      <br>
      <label for="myfile">Datei auswählen:</label>
      <input type="file" id="myfile" name="myfile" accept=".csv" required>
      <br>
      <button type="submit" id="submitbtnAdd" onclick="massImport()" aria-label="Submit">Submit</button>
      <br>
      <span id="massImportStatus"></span>
    </form>
  </div>



  <h1>Team-List</h1>

  <button type="submit" id="submitbtnQrcode" onclick="getTeamInfos(1)" aria-label="Submit">Show QR-Codes</button>
  <br>
  <br>
  <span id="qrcodeStatus"></span>
  <br>
  <br>
  <table style="border-width: 3px;">
    <thead>
      <tr style="border-width: 3px;">
        <th style="border-width: 3px;">Team-ID</th>
        <th style="border-width: 3px;">Team name</th>
        <th style="border-width: 3px;">Beers/Points</th>
        <th style="border-width: 3px;">Members</th>
        <th style="border-width: 3px;">Delete</th>
      </tr>
    </thead>
    <tbody id="teamlist">

    </tbody>
  </table>




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


    // AJAX function that update calc of points
    function updateCalcOfPoints() {
      var optionValue = document.querySelector('input[name="cal_option"]:checked').value;

      var dbOperation = "UPDATE-CALCOPT";

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
      ajax.send("dbOperation=" + dbOperation + "&optionvalue=" + optionValue);
    }


    // AJAX function that gets infos about teams
    function getTeamInfos(option) {
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

          if (option == 0) {
            // ### Do not generate QR-Codes
            for (let i = 0; i < teamIdArray.length; i++) {
              document.getElementById('teamlist').innerHTML += "<tr style=\"border-width: 3px;\"><td style=\"border-width: 3px;\">" + teamIdArray[i] + "</td><td style=\"border-width: 3px;\">" + teamNameArray[i] + "</td><td style=\"border-width: 3px;\">" + sumbeersArray[i] + "</td style=\"border-width: 3px;\"><td style=\"border-width: 3px;\"> <table style=\"margin: 15px 0px 15px 0px; min-width: 100%;\"><thead><tr><td>Person-ID</td><td>Name</td><td>First name</td><td>Beers</td><td>Delete</td><td>QR-Code / Value in the QR-Code</td></thead><tbody class=\"personlist\">" + personInfosArray[i] + "</tbody></table></td><td style=\"border-width: 3px;\">" + delTeamArray[i] + "</td></tr>";
            }
            
          } else if (option == 1) {
            // ### Generate QR-Codes
            document.getElementById("submitbtnQrcode").setAttribute("disabled","");
            document.getElementById("qrcodeStatus").innerHTML = `<small>ℹ️ Reload page to activate "Show QR-Codes"-Button again ℹ️</small>`;
            
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

                //Add QR <td>-Element to table for (QR-Code) colum
                var x = tr[j].insertCell();
                x.innerHTML = '<canvas id="qrcode' + i + j + '" style="margin: 5px;"></canvas><div id="qrcodevalue' + i + j + '"></div>';
                // Create a new QR code instance
                hash(personString).then((hex) => {
                  document.getElementById("qrcodevalue" + i + j).textContent = hex;
                  new QRious({element: document.getElementById("qrcode" + i + j), size: 170, value: hex});
                });
              }
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

      if (confirm("Confirm to delete Team.\n\n(All Members of this team get deleted.\nFurthermore all assosiated beers from a person are also deleted.)") == true) {
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

      if (confirm("Confirm to delete Person.\n\n(All assosiated beers from this person are also deleted.)") == true) {
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

    }

    // AJAX function that adds teams and persons as a mass import
    function massImport() {
      var myfile = document.getElementById("myfile");
      var dbOperation = "MASS-IMPORT";

      if (myfile == "" || myfile.files.length == 0) {
        document.getElementById("personAddStatus").innerHTML = `<b>No file selected or is empty</b>`;
        exit();
      } else {
        document.getElementById("personAddStatus").innerHTML = ``;

        let reader = new FileReader();
        reader.readAsText(myfile.files[0]);
        reader.onload = function() {
          
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
          ajax.send("dbOperation=" + dbOperation + "&myfile=" + reader.result);
        };
      }
    }


    // Get infos abot the teams on page load
    window.onload = function() {
      getTeamInfos(0);
    };
  </script>
</body>

</html>