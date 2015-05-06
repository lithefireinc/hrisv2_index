<?php

include("../../../scripts/pdo_ini.php");

$pdo2 = new PDO(HRISDSN, USER, PASS);

$pdo = new PDO(HRISV2DSN, USER, PASS);

$insert_emp_group = $pdo->prepare("INSERT INTO tbl_employee_group_members (employee_group_id, employee_id, start_date) VALUES (?,?,?)");

foreach($pdo->query("SELECT id FROM tbl_employee_info") as $row):
$insert_emp_group->execute(array(1, $row['id'], '2012-02-29'));	
endforeach;
