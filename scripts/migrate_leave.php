<?php

include("../../../scripts/pdo_ini.php");

$pdo2 = new PDO(HRISDSN, USER, PASS);

$pdo = new PDO(HRISV2DSN, USER, PASS);
set_time_limit(0);
$date_today = date("Y-m-d");
$now = date("Y-m-d H:i:s");

$get_leave = $pdo2->prepare("SELECT id, employee_id, leave_datefrom, leave_dateto, no_days_leave, call_log_id, dateapplied, reason FROM tbl_leaves WHERE dateapplied < '2012-01-01'");

$insert_leave = $pdo->prepare("INSERT INTO `infobahn_hrisv2`.`tbl_leave_application`
            (`id`,
             `employee_id`,
             `date_from`,
             `date_to`,
             `no_days`,
             `portion`,
             `leave_type`,
             `date_requested`,
             `reason`)
VALUES (?,?,?,?,?,?,?,?,?);");
$insert_audit = $pdo->prepare("INSERT INTO `infobahn_hrisv2`.`tbl_application_audit`
            (`application_pk`,
             `app_type_id`,
             `app_type`,
             `action_timestamp`,
             `approver_id`,
             `requestor`,
             `employee_group_id`,
             `app_group_id`,
             `app_tree_id`,
             `remarks`,
             `status_id`,
             `is_active`)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?);");
$get_leave->execute();
//die(print_r($get_training->errorInfo()));
//die(print_r($get_stmt->fetchAll()));
while($row = $get_leave->fetch()) {
	//$date_requested =  date('Y-m-d', strtotime($row[2]."-1 day"));
	$no_days = $row[4];
	$portion = "WHOLE DAY";
	$leave_type = 1;
	if($no_days == 0.5){
		$portion = "FIRST HALF";
	}
	
	if($row[5]){
		$leave_type = 2;
	}
	
	if((($no_days/0.5)%2 == 1) && $no_days != 0.5){
		$no_days = $no_days-0.5;
	}
	
	$insert_leave->execute(array($row[0], $row[1], $row[2], $row[3], $no_days, $portion, $leave_type, $row[6], $row[7]));
	$insert_audit->execute(array($row[0], 2, "Leave", $now, 999999, $row[1], 1, 1, 1, "Migrated From v1", 2, 1));
}
$pdo = null;
$pdo2 = null;

die("Done");
