<?php

include("../../../scripts/pdo_ini.php");

$pdo2 = new PDO(HRISDSN, USER, PASS);

$pdo = new PDO(HRISV2DSN, USER, PASS);

$get_stmt = $pdo2->prepare("SELECT a.id, b.employee_idno, b.lastname, b.firstname, b.mi, 
b.gender, b.civil_status, b.email, b.birthday, b.birth_place, 
b.address, b.prov_address, b.spouse_name, b.spouse_occupation,
b.name_children, b.phone, b.mobile, a.dept_id, a.position_id,
a.emp_cat_id, a.emp_status_id, a.sss, a.tin, 
a.date_hired, b.incase_emergency_contact, 
b.incase_emergency_phone, a.username, a.password FROM tbl_employees a 
LEFT JOIN tbl_profile_info b ON a.profile_id = b.profile_id
WHERE a.username IS NOT NULL OR a.username != '%%'");

$insert_stmt = $pdo->prepare("INSERT INTO `tbl_employee_info`
            (`id`,
             `employee_idno`,
             `lastname`,
             `firstname`,
             `middlename`,
             `gender`,
             `civil_status`,
             `email`,
             `birthdate`,
             `birth_place`,
             `address`,
             `provincial_address`,
             `spouse_name`,
             `spouse_occupation`,
             `childrens_name`,
             `telephone`,
             `mobile`,
             `department`,
             `position`,
             `employee_category`,
             `employee_status`,
             `sss`,
             `tin`,
             `date_hired`,
             `emergency_contact`,
             `emergency_phone`,
             `username`,
             `password`,
             `ACTIVATED`, CITIIDNO) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$get_stmt->execute();
//die(print_r($get_stmt->errorInfo()));
//die(print_r($get_stmt->fetchAll()));
while($row = $get_stmt->fetch()) {
	switch($row[5]){
	case 1: $row[5] = 'M';
	break;
	case 2: $row[5] = 'F';
	break;
	}
	
	switch($row[6]){
	case 1: $row[6] = 'SINGLE';
	break;
	case 2: $row[6] = 'MARRIED';	
	break;
	}
	$insert_stmt->execute(array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12], $row[13],
	$row[14], $row[15], $row[16], $row[17], $row[18], $row[19], $row[20], $row[21], $row[22], $row[23], $row[24], $row[25], $row[26], $row[27], 1, '00001'));
}
$pdo = null;

die("Done");
