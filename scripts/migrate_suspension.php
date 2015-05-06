<?php

include("../../../scripts/pdo_ini.php");

$pdo2 = new PDO(HRISDSN, USER, PASS);

$pdo = new PDO(HRISV2DSN, USER, PASS);
set_time_limit(0);
$date_today = date("Y-m-d");
$time = date("H:i:s");
$now = date("Y-m-d H:i:s");

$get_suspension = $pdo2->prepare("SELECT a.id, b.id AS employee_id, suspension_date_from, suspension_date_to, 
no_days_suspension, suspension_dateapplied, reason FROM tbl_suspension a JOIN tbl_employees b ON a.profile_id = b.profile_id");

$insert_suspension = $pdo->prepare("INSERT INTO `tbl_suspension`
            (`id`,
             `employee_id`,
             `date_from`,
             `date_to`,
             `portion`,
             `no_days`,
             `date_requested`,
             `requested_by`,
             `reason`,
             `status`,
             `DCREATED`,
             `TCREATED`,
             `DMODIFIED`,
             `TMODIFIED`)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);");

$get_suspension->execute();
//die(print_r($get_training->errorInfo()));
//die(print_r($get_stmt->fetchAll()));
while($row = $get_suspension->fetch()) {
	
	$insert_suspension->execute(array($row[0], $row[1], $row[2], $row[3], 'WHOLE DAY', $row[4], $row[5], 999999, $row[6], 2, $date_today, $time, $date_today, $time));
	//$insert_audit->execute(array($row[0], 4, "Client Schedule", $now, 999999, $row[1], 1, 1, 1, "Migrated From v1", 2, 1));
}
$pdo = null;
$pdo2 = null;

die("Done");
