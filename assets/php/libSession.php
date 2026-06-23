<?php
$dbConn = $_MYSQLI_->getConn();
$session = new Zebra_Session($dbConn, SESSKEY, 10800, true, false, 60, 'sessiondata', true, false);
