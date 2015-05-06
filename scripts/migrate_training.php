<?php

include("../../../scripts/pdo_ini.php");

$pdo2 = new PDO(HRISDSN, USER, PASS);

$pdo = new PDO(HRISV2DSN, USER, PASS);

$date_today = date("Y-m-d");
$now = date("Y-m-d H:i:s");

$get_training = $pdo2->prepare("SELECT training_id, emp_id, training_type, training_title, training_details, training_loc, start_date,
end_date, suppidno, t_starttime, t_endtime FROM tbl_training");

$insert_training = $pdo->prepare("INSERT INTO `infobahn_hrisv2`.`tbl_training`
            (`id`,
             `employee_id`,
             `type`,
             `training_type_id`,
             `date_start`,
             `date_end`,
             `start_time`,
             `end_time`,
             `supplier_id`,
             `location`,
             `title`,
             `details`,
             `date_requested`)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?);");
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
$get_training->execute();
//die(print_r($get_training->errorInfo()));
//die(print_r($get_stmt->fetchAll()));
while($row = $get_training->fetch()) {
	$date_requested =  date('Y-m-d', strtotime($row[6]."-1 day"));
	$insert_training->execute(array($row[0], $row[1], "Supplier", $row[2], $row[6], $row[7], $row[9], $row[10], $row[8], $row[5], $row[3], $row[4], $date_requested));
	$insert_audit->execute(array($row[0], 6, "Client Schedule", $now, 999999, $row[1], 1, 1, 1, "Migrated From v1", 2, 1));
}
$pdo = null;
$pdo2 = null;

die("Done");
