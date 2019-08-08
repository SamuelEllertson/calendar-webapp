<?php
require_once(dirname(__FILE__) . "/sql.php");
session_start();


try{
	if(!isset($_SESSION["username"])){
		killThis();	
	}
	
	if(!$_SESSION["admin"]){
		killThis();	
	}
	
	$username = $_SESSION["username"];
	
	$SQL = Start();
	
	$userType = $SQL->q_getUserType($username)->f_autoReturn()[0][0];
	
	if(!isset($userType) || $userType == null || $userType != "admin"){
		killThis();	
	}
	
} catch(Exception $e){
	killThis();	
}

function killThis(){
	session_start();
	session_destroy();
	header("Location: index.php");
	exit;
}
?>