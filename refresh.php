<?php
/*
 * Runs the getsites.php script in the BG and causes the Refresh button on the
 * reader.php page to become inactive until execution of this script is complete
 */
exec("php getsites.php &");
echo "header(\"location:javascript://history.go(-1)\")";
exit();
?>
