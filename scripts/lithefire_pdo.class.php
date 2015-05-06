<?php

class Lithefire_pdo{

	private $pdo;

	function Lithefire_pdo($dsn, $user, $pass){
		$this->pdo = new PDO($dsn, $user, $pass);
	}
	
	function getAllRecords($table, $fields, $start, $limit, $sort, $filter, $group, $having = ""){
		
		
		$stmt = $this->pdo->prepare("SELECT ".$fields." FROM ".$table." ".$filter." ".$group." ".$having);
	}
}
