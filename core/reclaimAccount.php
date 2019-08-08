<?php 
	require_once(dirname(__FILE__) . '/sql.php');
	require_once(dirname(__FILE__) . '/bruteForceBlock.php');
	session_start();	

	if(empty($_POST['type'])){
		header('Location: ../forgotPassword.php');	
		exit();
	} 
		
	$type = $_POST['type'];	

	$SQL = Start();

//SENDING EMAIL CODE ================================================
	if($type == "sendEmail"){
		if(empty($_POST['Email'])){
			header('Location: ../forgotPassword.php');	
			exit();
		}
		try{
			$email = $_POST['Email'];
			
			$userInformation = $SQL->q_userInformationFromEmail($email)->f_autoReturn();
			$userID = $userInformation[0]["UserID"];
			
			if(empty($userInformation)){
				$_SESSION["emailNotFound"] = true;
				header('Location: ../forgotPassword.php');
				exit();
			} else{
				unset($_SESSION["emailnotFound"]);
			}
			
			reclaimEmail($email, $userID);
			header('Location: ../resetPassword.php');
		} catch(Exception $e){
			header('Location: ../error.php');
		}
	}

//RESETTING PASSWORD CODE ================================================
	if($type == "resetPassword"){
		
		//preliminary form validation
		if(empty($_POST['Code']) || !is_numeric(trim($_POST['Code'])) || empty($_POST['Password']) || empty($_POST['Username'])){
			header('Location: ../resetPassword.php');	
			exit();
		}
		
		try{
			$username = $_POST['Username'];
			$userID = $SQL->q_getUserID($username)->f_autoReturn()[0][0];
			$code = ltrim($_POST['Code']);
			$password = $_POST['Password'];
			
			//Begin BRUTE FORCE BLOCK CHECK
			$BFBresponse = BruteForceBlock::getLoginStatus($userID, $_SERVER["REMOTE_ADDR"], $throttle_settings, "resetPassword");   
			switch ($BFBresponse['status']){
				case 'safe':
					break;
				case 'error';
					header('Location: ../error.php');
					exit();
					break;
				case 'delay':
					$_SESSION["ResetDelay"] = true;
					header('Location: ../resetPassword.php');
					exit();
					break;
				case 'captcha':
					$_SESSION["ResetLockedOut"] = true;
					header('Location: ../resetPassword.php');
					exit();
					break;
			}
			unset($_SESSION["ResetDelay"]);
			unset($_SESSION["ResetLockedOut"]);
			//END BRUTE FORCE BLOCK CHECK
			
			$result = $SQL->q_getToken($userID)->f_autoReturn()[0]["Token"];			
			
			if(password_verify(rtrim($code), $result)){
				//CORRECT TOKEN
				$SQL->q_removeToken($userID, $result)->f_autoVoid();
				
				$hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
				$SQL->q_updatePassword($hash, $userID)->f_autoVoid();
				
				unset($_SESSION["badTry"]);
				
				header('Location: ../index.php');
				exit();
			} else{
				//INCORRECT TOKEN
				$_SESSION["badTry"] = true;
				//ADD BAD TRY TO BRUTE FORCE BLOCK
				BruteForceBlock::addFailedLoginAttempt($userID, $_SERVER["REMOTE_ADDR"], "resetPassword"); 
				header('Location: ../resetPassword.php');
				exit();
			}
		} catch(Exception $e){
			header('Location: ../error.php');
		}
	}

function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range == 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
}

function reclaimEmail($to, $userID){	
	try{
		$SQL = Start();
		$rand = crypto_rand_secure(1000000000000000,9999999999999999);
		
		$token = password_hash($rand, PASSWORD_BCRYPT, ["cost" => 12]);
		
		$SQL->q_clearUsersTokens($userID)->f_autoVoid();
		$SQL->q_addToken($userID, $token)->f_autoVoid();

		$subject = "Forgot Password";
		$message = "your reset code is: ".$rand." If it has not already opened, you can go to http://calendar.yhscs.us/resetPassword.php and follow the instructions to reset your password";
		$headers = "From: noreply@yhscs.us" . "\r\n";

		return mail($to, $subject, $message, $headers);
	} catch(Exception $e){
		header('Location: ../error.php');
	}
}
?>