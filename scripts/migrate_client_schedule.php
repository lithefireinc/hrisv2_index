<?php

include("../../../scripts/pdo_ini.php");

$pdo2 = new PDO(HRISDSN, USER, PASS);

$pdo = new PDO(HRISV2DSN, USER, PASS);
set_time_limit(0);
$date_today = date("Y-m-d");
$now = date("Y-m-d H:i:s");

$get_cs = $pdo2->prepare("SELECT s_id, s_emp_id, date_scheduled, time_in, time_out, type, clientidno, contact_person, purpose, agenda FROM tbl_schedules");

$insert_cs = $pdo->prepare("INSERT INTO `infobahn_hrisv2`.`tbl_client_schedule`
            (`id`,
             `employee_id`,
             `date_scheduled`,
             `time_in`,
             `time_out`,
             `type`,
             `client_id`,
             `contact_person_id`,
             `purpose_id`,
             `date_requested`,
             `agenda`)
VALUES (?,?,?,?,?,?,?,?,?,?,?);");
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
$get_cs->execute();
//die(print_r($get_training->errorInfo()));
//die(print_r($get_stmt->fetchAll()));
while($row = $get_cs->fetch()) {
	$date_requested =  date('Y-m-d', strtotime($row[2]."-1 day"));
	$insert_cs->execute(array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $date_requested, $row[9]));
	$insert_audit->execute(array($row[0], 4, "Client Schedule", $now, 999999, $row[1], 1, 1, 1, "Migrated From v1", 2, 1));
}
$pdo = null;
$pdo2 = null;

die("Done");
