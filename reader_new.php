<?php
/*
 * This script reads the database and presents the information within in a usable format
 */
$cols=6;
$counter = 0;
$first = 0;
$tbl='runcount';
/*
mysql> describe runcount;
+------------+--------------+------+-----+-------------------+----------------+
| Field      | Type         | Null | Key | Default           | Extra          |
+------------+--------------+------+-----+-------------------+----------------+
| ID         | int(12)      | NO   | PRI | NULL              | auto_increment |
| instanceID | int(12)      | NO   |     | -1                |                |
| rundate    | timestamp    | NO   |     | CURRENT_TIMESTAMP |                |
| url        | varchar(256) | NO   |     | NULL              |                |
| httpcode   | int(4)       | NO   | MUL | NULL              |                |
+------------+--------------+------+-----+-------------------+----------------+
 */
require 'db/sitelerDSN.php';
//
// Set up some basic reporting values
//
// Number of sites checked per run:
    $querynumchecked = "SELECT MAX(ID) FROM runcount HAVING MAX(instanceID)"; // get max number records
    $numchecked = $db -> query($querynumchecked);
    $numsites=$numchecked->fetch(PDO::FETCH_ASSOC);
    $numsites=  array_shift($numsites);
//    echo "Number of checks total numsites: $numsites";
//
// Previous run instanceID:
    $queryinst = "SELECT MAX(instanceID) FROM runcount HAVING MAX(ID)"; // get max instanceID - 1
    $numruns = $db -> query($queryinst);
    $numinstances= $numruns->fetch(PDO::FETCH_ASSOC);
    $numinstances=  (array_shift($numinstances) - 1);
//    echo "<br />Number of run instances - 1: $numinstances";
//
// Get highest ID value from previous run's instanceID to use to calculate how many sites are checked
    $queryid = "SELECT MAX(ID) FROM runcount WHERE instanceID = $numinstances"; // get max number records
    $numprev = $db -> query($queryid);
    $numprevsites=$numprev->fetch(PDO::FETCH_ASSOC);
    $numprevsites=  array_shift($numprevsites);
//
// In order to only display the most recent run, select the MAX instanceID value for use in select statements
    $querymax = "SELECT MAX(instanceID) FROM runcount";
    $latestrun = $db -> query($querymax);
    $latest=$latestrun->fetch(PDO::FETCH_ASSOC);
    $latest=  array_shift($latest);
//
// Retrieve the number of 200 http codes for this latest run from curent
    $query200 = "SELECT numok FROM current ORDER BY FKruncount DESC LIMIT 1";
    $goodones = $db -> query($query200);
    $num200=$goodones->fetch(PDO::FETCH_ASSOC);
    $num200=  array_shift($num200);

// Get all of the urls associated with a bad code for the latest run
    $thisinstance = ($numinstances + 1);
    $querybums = "SELECT url, httpcode FROM runcount WHERE instanceID = \"$thisinstance\" AND httpcode != \"200\"";
    $thebums = $db -> query($querybums);
    $badlinks = $thebums;
    $craplinks .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"main.css\" />";
    foreach($badlinks as $badlink):
        $craplinks .= "<a href=\"$badlink[0]\" target=\"_blank\">$badlink[0]</a><br />";
    endforeach;
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>SiteSup!</title>
    <!--<link rel="stylesheet" type="text/css" href="main.css" />-->
    <link rel="stylesheet" type="text/css" href="css/reader.css" />
    <meta http-equiv="refresh" content="30" > <!-- 30 second refresh-->
    <script>
// <!--Open url in pop up window
    function wopen(url, name, w, h)
    {
     var win = window.open(url,
      name,
      'width=' + w + ', height=' + h + ', ' + 'location=yes, menubar=yes, ' + 'status=yes, toolbar=yes, scrollbars=yes, resizable=yes');
     win.resizeTo(w, h);
     win.focus();
    }

    function openBad()
    {
    var craplinks = '<?php echo $craplinks; ?>';
    myWindow=window.open('','','width=300,height=400');
    myWindow.document.write(craplinks);
    myWindow.focus();
    }
// -->
    </script>
</head>
<body>
    <div class="reader">
    <table width="100%">
    <tr><td colspan="<?php echo $cols ?>">CDX Websites Health: <?php echo date("D, d M Y"); ?></td></tr>
    <tr>
        <td><?php echo ($numsites-$numprevsites) . " sites checked"?></td>
        <td><?php echo "Number ok: $num200"; ?></td>
        <td><a onClick="openBad()"><?php echo "Number bad: " . (($numsites-$numprevsites)-$num200); ?></a></td>
        <td>Execution: <?php echo ($numinstances+1); ?></td>
        <td></td>
        <td><FORM action="refresh.php" method="post" id="refresh">
<!--            <INPUT TYPE="button" onClick="window.location.reload()" VALUE="Refresh">-->
            <INPUT TYPE="submit" VALUE="Refresh">
            </FORM></td>
    </tr>
    <tr onload="this.style.backgroundColor='#e6e6e6';"onmouseover="this.style.backgroundColor='#e6e6e6';" onmouseout="this.style.backgroundColor='#ffffff';">

        <?php
// In order to only display the most recent run, select the MAX instanceID value for use in select statements
        $querymax = "SELECT MAX(instanceID) FROM runcount";
        $latestrun = $db -> query($querymax);
        $latest=$latestrun->fetch(PDO::FETCH_ASSOC);
        $latest=  array_shift($latest);

// Switch statements to control which query to run
//        $query = "SELECT COUNT(DISTINCT id) FROM $tbl WHERE httpcode != 200";
//        $query = "SELECT * FROM $tbl WHERE httpcode != 200 group by rundate";
        $query = "SELECT * FROM $tbl WHERE instanceID = $latest";
        $noBums = $db -> query($query);
        $answer = $noBums;
        foreach($answer as $bum):
            if (($counter%$cols) === 0) { // create proper number of columns after the first row
                $first = 1;
                echo "</td>
                    <tr onload=\"this.style.backgroundColor='#e6e6e6';\"onmouseover=\"this.style.backgroundColor='#e6e6e6';\" onmouseout=\"this.style.backgroundColor='#ffffff';\">";
            }
            $counter++;
            echo "<td><p2>";
// Test for good or bad httpcode
            if ($bum[4] === "200"){
                echo "<p class=\"good\">$bum[4]</p>";
            }
            else{
                echo "<p class=\"bad\">$bum[4]</p>";
            }
// Get rid of trailing "/index.php" on urls
            $bum3 = str_replace("/index.php"," ","$bum[3]");
            echo "<p class=\"norm\"><a href=$bum3 target=\"popup\" onClick=\"wopen('$bum3', 'popup', 1000, 800)\";>".$bum3."</a></p>";
            echo "<p class=\"counter\">$bum[2]</p></td>";
            if (($counter === ($cols)) AND ($first === 0)) { // Force new row after first line
                echo "</tr><tr>";
            }
            endforeach;
        ?>
    </table>
    </div>
</body>
</html>

