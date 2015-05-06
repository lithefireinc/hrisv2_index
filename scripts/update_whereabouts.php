<?php
putenv("TZ=ASIA/Manila");
set_time_limit(0);
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=pmmspon_hrisv2", "pmmspon_darryl", "Fre28W38pUaKTnsS");

$interval_obj = $pdo->query("SELECT date_from, date_to, id FROM tbl_schedule_interval where is_active = 1");

$interval = $interval_obj->fetchAll();

$start_date = $date_from = $interval[0]['date_from'];
//$date_to = $interval[0]['date_to'];
$date_to  = date("Y-m-d");
$interval_id = $interval[0]['id'];

$company_obj = $pdo->query("SELECT time_in, time_out FROM tbl_company_setup");

$comp_setup = $company_obj->fetchAll();

$company_time_in = $comp_setup[0]['time_in'];
$company_time_out = $comp_setup[0]['time_out'];

$biometrics_in = date("H:i:s", strtotime($company_time_in."+4hours 30 minutes")); 

$biometrics_out = date("H:i:s", strtotime($biometrics_in."-12 hours"));

$interval = $interval_obj->fetchAll();

$whereabouts_update_stmt = $pdo->prepare("UPDATE tbl_whereabouts SET time_in = ?, time_out = ?, app_type = ?, application_pk = ? WHERE employee_id = ? AND dtr_date = ?");
$whereabouts_update_stmt_2 = $pdo->prepare("UPDATE tbl_whereabouts SET time_in = ?, time_out = ? WHERE employee_id = ? AND dtr_date = ?");

$restday = $pdo->prepare("UPDATE tbl_whereabouts SET restday = 'Y' WHERE employee_id = ? AND dtr_date = ?");
$leave2 = $pdo->prepare("UPDATE tbl_whereabouts SET is_leave = 'Y' WHERE employee_id = ? AND dtr_date = ?");
$cs = $pdo->prepare("UPDATE tbl_whereabouts SET client_schedule = 'Y' WHERE employee_id = ? AND dtr_date = ?");
$force = $pdo->prepare("UPDATE tbl_whereabouts SET force_leave = 'Y' WHERE employee_id = ? AND dtr_date = ?");
$call_log = $pdo->prepare("UPDATE tbl_whereabouts SET call_log = 'Y' WHERE employee_id = ? AND dtr_date = ?");
$training = $pdo->prepare("UPDATE tbl_whereabouts SET training = 'Y' WHERE employee_id = ? AND dtr_date = ?");

$whereabouts_insert_stmt = $pdo->prepare("INSERT INTO tbl_whereabouts (interval_id, employee_id, dtr_date) VALUES (?,?,?)");
$whereabouts_delete_stmt = $pdo->prepare("DELETE FROM tbl_whereabouts WHERE interval_id = '$interval_id' AND employee_id = ?");

$leave_stmt = $pdo->prepare("SELECT a.id, c.description FROM tbl_leave_application a JOIN tbl_application_audit b ON a.id = b.application_pk AND b.app_type_id = 2 AND b.is_active = 1 JOIN tbl_leave_type c ON a.leave_type = c.id WHERE b.status_id = 2 AND a.employee_id = ? AND ? BETWEEN a.date_from AND a.date_to");
$cs_stmt = $pdo->prepare("SELECT a.id FROM tbl_client_schedule a JOIN tbl_application_audit b ON a.id = b.application_pk AND b.app_type_id = 4 AND b.is_active = 1 WHERE b.status_id = 2 AND a.employee_id = ? AND ? = a.date_scheduled");
$training_stmt = $pdo->prepare("SELECT a.id FROM tbl_training a JOIN tbl_application_audit b ON a.id = b.application_pk AND b.app_type_id = 6 AND b.is_active = 1 WHERE b.status_id = 2 AND a.employee_id = ? AND ? BETWEEN a.date_start AND a.date_end");
$tito_stmt = $pdo->prepare("SELECT a.id, a.time_in, a.time_out FROM tbl_tito_application a JOIN tbl_application_audit b ON a.id = b.application_pk AND b.app_type_id = 5 AND b.is_active = 1 WHERE b.status_id = 2 AND a.employee_id = ? AND ? BETWEEN a.date_time_in AND a.date_time_out");
$suspension_stmt = $pdo->prepare("SELECT id FROM tbl_suspension where employee_id = ? AND ? BETWEEN date_from AND date_to AND status = 'Approved'");
$holiday_stmt = $pdo->prepare("SELECT id FROM tbl_holiday where ? = holiday_date");
$force_leave_stmt = $pdo->prepare("SELECT id FROM tbl_force_leave where (employee_id = 0 OR employee_id = ?) AND ? BETWEEN date_from AND date_to AND status = 'Approved'");
$dtr_stmt = $pdo->prepare("SELECT dtr_log as dtr_log FROM tbl_dtr WHERE biometrics_id = ? AND dtr_log BETWEEN ? AND ? ORDER BY dtr_log ASC");
$dtr_stmt2 = $pdo->prepare("SELECT MAX(dtr_log) as dtr_log FROM tbl_dtr WHERE biometrics_id = ? AND dtr_log BETWEEN ? AND ?");
$half = $pdo->prepare("SELECT dtr_log FROM tbl_dtr WHERE biometrics_id = ? AND dtr_date = ? ORDER BY dtr_log ASC");

$call_log_stmt = $pdo->prepare("SELECT id FROM tbl_call_log where leave_id = ?");

foreach($pdo->query("SELECT id, date_hired, biometrics_id from tbl_employee_info where resigned = 'N'") as $employee):
$emp_id = $employee['id'];
	$whereabouts_delete_stmt->execute(array($emp_id));
	
	$start_date = ($employee['date_hired'] > $date_from ? $employee['date_hired'] : $date_from);
	//die($start_date);
	while($start_date <= $date_to):
	$day = date('w', strtotime($start_date)); 
		
			
		$prev_date = date('Y-m-d', strtotime("$start_date-1 day"));
		$next_date = date('Y-m-d', strtotime("$start_date+1 day"));
			
		$whereabouts_insert_stmt->execute(array($interval_id, $emp_id, $start_date));
		$whereabouts_update_stmt_2->execute(array("ABSENT", "ABSENT", $emp_id, $start_date));
		
		if($day == 0 || $day == 6){
			$whereabouts_update_stmt_2->execute(array("REST DAY", "REST DAY", $emp_id, $start_date));
			$restday->execute(array($emp_id, $start_date));
		}
		
		$holiday_stmt->execute(array($start_date));
		$holiday_array = $holiday_stmt->fetchAll();
		//if($start_date == '2012-01-20' && $emp_id == 1)
		//die(print_r($tito_array[0]['time_in']."-".$tito_array[0]['time_out']));
		if(count($holiday_array)){
			$whereabouts_update_stmt_2->execute(array("HOLIDAY", "HOLIDAY", $emp_id, $start_date));
		}
		
		//LEAVES
		$leave_stmt->execute(array($emp_id, $start_date));
		$leave_array = $leave_stmt->fetchAll();
		//if($start_date == '2012-01-23' && $emp_id == 1)
		//die(print_r($leave_array));
		if(count($leave_array)){
			$whereabouts_update_stmt->execute(array(strtoupper($leave_array[0]['description']), strtoupper($leave_array[0]['description']), 2, $leave_array[0]['id'], $emp_id, $start_date));
			$leave2->execute(array($emp_id, $start_date));
			$call_log_leaves = array("SICK LEAVE", "EMERGENCY LEAVE", "UNPAID SICK LEAVE");
			if(in_array(strtoupper($leave_array[0]['description']), $call_log_leaves)){
				$call_log_stmt->execute(array($leave_array[0]['id']));
				$call_log_array = $call_log_stmt->fetchAll();
				if(count($call_log_array)){
					
					$call_log->execute(array($emp_id, $start_date));
				}
			}
		}
		
		//CLIENT SCHEDULE
		
		
		$cs_stmt->execute(array($emp_id, $start_date));
		$cs_array = $cs_stmt->fetchAll();
		if(count($cs_array)){
			$whereabouts_update_stmt->execute(array("CLIENT SCHEDULE", "CLIENT SCHEDULE", 4, $cs_array[0]['id'], $emp_id, $start_date));
			$cs->execute(array($emp_id, $start_date));
		}
		
		//TRAINING
		
		
		$training_stmt->execute(array($emp_id, $start_date));
		$training_array = $training_stmt->fetchAll();
		//if($start_date == '2012-01-30' && $emp_id == 7)
		//die(print_r($training_array));
		if(count($training_array)){
			$whereabouts_update_stmt->execute(array("TRAINING", "TRAINING", 6, $training_array[0]['id'], $emp_id, $start_date));
			$training->execute(array($emp_id, $start_date));
		}
		
		$dtr_stmt->execute(array($employee['biometrics_id'], $prev_date." ".$biometrics_out, $start_date." ".$biometrics_in));
		$time_in_array = $dtr_stmt->fetchAll();
		//die(print_r($time_in_array));
		$time_in = "";
		if(!empty($time_in_array[0]['dtr_log'])){
		$time_in = date('H:i:s', strtotime($time_in_array[0]['dtr_log']));
		}
		if(empty($time_in)){
			$half->execute(array($employee['biometrics_id'], $start_date));
			$half_day_array = $half->fetchAll();
			
			if(count($half_day_array) > 1){
				$time_in = date('H:i:s', strtotime($half_day_array[0]['dtr_log']));
			}
			}
		
		
		$dtr_stmt->execute(array($employee['biometrics_id'], $start_date." ".$biometrics_in, $next_date." ".$biometrics_out));
		$time_out_array = $dtr_stmt->fetchAll();
		
		//if($start_date == '2012-01-24' && $emp_id == 1)
		//die(print_r($half_day_array));
		//die(print_r($time_out_array).print_r($time_in_array));
		//die($time_in.$time_out);
		$time_out = "";
		$time_out_cnt = count($time_out_array)-1;
		if(!empty($time_out_array[0]['dtr_log']))
		$time_out = date('H:i:s', strtotime($time_out_array[$time_out_cnt]['dtr_log']));
		if(!empty($time_in) && !empty($time_out)){
			$whereabouts_update_stmt_2->execute(array($time_in, $time_out, $emp_id, $start_date));
		}
		//if($start_date == '2012-01-24' && $emp_id == 1)
		//die($time_in.$time_out);
		$tito_stmt->execute(array($emp_id, $start_date));
		$tito_array = $tito_stmt->fetchAll();
		//if($start_date == '2012-01-24' && $emp_id == 1)
		//die($time_in.$time_out);
		//die(print_r($tito_array[0]['time_in']."-".$tito_array[0]['time_out']));
		if(count($tito_array)){
			$whereabouts_update_stmt_2->execute(array($tito_array[0]['time_in'], $tito_array[0]['time_out'], $emp_id, $start_date));
		}
		
		$force_leave_stmt->execute(array($emp_id, $start_date));
		$force_leave_array = $force_leave_stmt->fetchAll();
		//if($start_date == '2012-01-20' && $emp_id == 1)
		//die(print_r($tito_array[0]['time_in']."-".$tito_array[0]['time_out']));
		if(count($force_leave_array)){
			$whereabouts_update_stmt_2->execute(array("FORCE LEAVE", "FORCE LEAVE", $emp_id, $start_date));
			$force->execute(array($emp_id, $start_date));
		}
		
		$suspension_stmt->execute(array($emp_id, $start_date));
		$suspension_array = $suspension_stmt->fetchAll();
		//if($start_date == '2012-01-20' && $emp_id == 1)
		//die(print_r($tito_array[0]['time_in']."-".$tito_array[0]['time_out']));
		if(count($suspension_array)){
			$whereabouts_update_stmt_2->execute(array("SUSPENDED", "SUSPENDED", $emp_id, $start_date));
		}
		
	
		
		
		$start_date = date('Y-m-d', strtotime("$start_date+1 day"));
	endwhile;
	//$start_date = $date_from;
endforeach;

$data['success'] = true;
$data['data'] = "Whereabouts successfully updated";
die(json_encode($data));
