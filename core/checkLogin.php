<?php
	require_once(dirname(__FILE__) . '/sql.php');
	require_once(dirname(__FILE__) . "/bruteForceBlock.php");
	session_start();
	$SQL = Start();
	
	if(empty($_POST['username']) || empty($_POST['password'])){
		$_SESSION["noNameOrPass"] = true;
		header('Location: ../index.php');	
		exit();
	} else {
		$_SESSION["noNameOrPass"] = false;
	}
	
	//preliminary captcha check (do not rely on session info, full check under switch captcha)
	if($_SESSION["captcha"]){
		if(!isset($_POST['g-recaptcha-response'])){
			header('Location: ../index.php');
			exit();
		}
	}

try{
	$username = noHTML($_POST['username']);
	$password = $_POST['password'];
	
	$userID = $SQL->q_getUserID($username)->f_autoReturn()[0][0];

	if($userID == null){
		$_SESSION["badLogin"] = true;
		header('Location: ../index.php');
		exit();
	}
	
	//make sure clear to attempt login
	
	$BFBresponse = BruteForceBlock::getLoginStatus($userID, $_SERVER["REMOTE_ADDR"], $throttle_settings, "login");   
	switch ($BFBresponse['status']){
		case 'safe':
			unset($_SESSION["remainingTime"]);
			unset($_SESSION["captcha"]);
			break;
		case 'error':
			$error_message = $BFBresponse['message'];
			header('Location: ../error.php');
			exit();
			break;
		case 'delay':
			$_SESSION["remainingTime"] = $BFBresponse['message'];
			unset($_SESSION["captcha"]);
			header('Location: ../index.php');
			exit();
			break;
		case 'captcha': //main captcha check
			$_SESSION["captcha"] = true;
			unset($_SESSION["remainingTime"]);
			
			//captcha verification
			$secret = "6LdYQhcUAAAAAEhF624ZtC-9sBh3PhR4rcuM1R2n";
			$responseData = json_decode(file_get_s('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']));
			
			//invalid captcha
			if (!($responseData->success)) {
				header('Location: ../index.php');
				exit();
			  }			
			break;
	}
	
	$hash = $SQL->q_getHash($username)->f_autoReturn()[0][0]; 
		
	if(password_verify($password, $hash)){ //password works
		$_SESSION["loggedIn"] = true;
		$_SESSION["badLogin"] = false;
		
		$_SESSION["userID"] = $userID;
		$_SESSION["username"] = $username;
		$_SESSION["name"] = $SQL->q_getName($username)->f_autoReturn()[0][0];
		
		$type = $SQL->q_getUserType($username)->f_autoReturn()[0][0];
		
		if($type == "admin"){
			$_SESSION["admin"] = true;
		} else{
			$_SESSION["admin"] = false;
		}
		
		header("Location: ../userMain.php");
		exit();
	} else{	//wrong password	
		
		BruteForceBlock::addFailedLoginAttempt($userID, $_SERVER["REMOTE_ADDR"], "login");
	
		$_SESSION["loggedIn"] = false;
		$_SESSION["badLogin"] = true;
		header("Location: ../index.php");
	}
} catch(Exception $e) {
	header("Location ../error.php");	
}

?>