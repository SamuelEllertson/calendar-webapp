<?php
	require_once(dirname(__FILE__) . "/sql.php");
	require_once(dirname(__FILE__) . "/../functions/userControls.php");
	session_start();
	
	
	if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['name']) || empty($_POST['email'])){
		$_SESSION["registerIncomplete"] = true;
		header('Location: ../index.php');	
		exit();
	} else {
		$_SESSION["registerIncomplete"] = false; 	
	}
	
	$username = noHTML($_POST["username"]);
	$password = $_POST["password"]; //noHTML not needed bc gets hashed
	$email = $_POST["email"];  //use noHTML when displaying (which should be never?)
	$name = noHTML($_POST["name"]);

try {
	$SQL = Start();
	
	if(count($SQL->q_getUserID($username)->f_autoReturn()) > 0){ //username already in use
		header("Location: ../index.php");
		$_SESSION["nameTaken"] = true;
		$_SESSION["isRegistered"] = false;
		exit();
	} else {
		$_SESSION["nameTaken"] = false;
	}
	
	$hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
	
	$SQL->q_register($name, $username, $hash, $email, "user")->f_autoVoid();
	
	$userID = $SQL->q_getUserID($username)->f_autoReturn()[0][0];
	
	//add default user settings
	addUserSetting($userID, "defaultCalendar", "");
	addUserSetting($userID, "skipNoSchool", "true");
	addUserSetting($userID, "skipWeekends", "true");
	
	$_SESSION["isRegistered"] = true;
	$_SESSION["noRegister"] = false;
	
	header("Location: ../index.php");
	exit();
} catch(Exception $e) {
	header("Location: ../error.php");
	exit();
} 
?>














