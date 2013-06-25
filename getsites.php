<?php
/*
 * Perform a survey of the sites in the urllist and report into both the runcount and the current tables
 *
 * mysql> describe runcount;
 *    +------------+--------------+------+-----+-------------------+----------------+
 *    | Field      | Type         | Null | Key | Default           | Extra          |
 *    +------------+--------------+------+-----+-------------------+----------------+
 *    | ID         | int(12)      | NO   | PRI | NULL              | auto_increment |
 *    | instanceID | int(12)      | NO   |     | -1                |                |
 *    | rundate    | timestamp    | NO   |     | CURRENT_TIMESTAMP |                |
 *    | url        | varchar(256) | NO   |     | NULL              |                |
 *    | httpcode   | int(4)       | NO   | MUL | NULL              |                |
 *    +------------+--------------+------+-----+-------------------+----------------+
 *
 *    mysql> describe current;
 *    +------------+---------+------+-----+---------+----------------+
 *    | Field      | Type    | Null | Key | Default | Extra          |
 *    +------------+---------+------+-----+---------+----------------+
 *    | id         | int(12) | NO   | PRI | NULL    | auto_increment |
 *    | FKruncount | int(12) | NO   | MUL | NULL    |                |
 *    | numok      | int(8)  | NO   | MUL | NULL    |                |
 *    | numbad     | int(8)  | NO   |     | NULL    |                |
 *    +------------+---------+------+-----+---------+----------------+
 *
 */
    $tblruncount = "runcount";
    $tblcurrent = "current";
    $numok = 0;
    $numbad = 0;
    require 'db/sitelerDSN.php';
    include 'urllistshort.php'; // short url list for testing purposes
//    include 'urllist.php';

    function get_curled($url){
    $thecmd = exec("curl -sIL -w \"&code=%{http_code} &url=%{url_effective}\\n\" \"$url\"");
    return $thecmd;
    }

/* First read the db and get the current instanceID.  If the value is less than 1,
 * than set to 1, else increment the value by one and store in variable for
 * use in the insert statement to identify a new run of the script.
 */

// Get the current instanceID of the most recent run
    $query1 = "SELECT MAX(instanceID) FROM runcount";
    $therunid=$db->query($query1);
    $instance = $therunid->fetch(PDO::FETCH_ASSOC);
    $instance = array_shift($instance);

// Instantiate and increment the run counter
    if ($instance < "1") {
            $instance = "1";
        }
        else {
            $instance++;
        }

// Insert a record into table::runcount for each url in the urllist file
foreach ($urls as $url) {
        $thecmd = get_curled($url);
        parse_str($thecmd, $theurl);
        $urly = $theurl[url];
        $code = $theurl[code];
        $query2 = "INSERT INTO $tblruncount (instanceID, url, httpcode) VALUES ('$instance', '$urly', '$code')";
        $db->exec($query2);
        $code = trim($code);
        if ($code === "200"){
            $numok++;
        } else {
            $numbad++;
        }
}
// Count the number of http code 200

// Create a record in the table::current to record the stats on this run (instanceID)
    $query3 = "INSERT INTO current (FKruncount, numok, numbad) VALUES ('$instance', '$numok','$numbad')";
    $db->exec($query3);
//    echo "<br />" . $query3;

?>