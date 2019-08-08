<?PHP
require_once(dirname(__FILE__) . "/sql.php");

function generateToken($AdminID){
	//preliminary checks
	if(!is_numeric($AdminID)){ exit();}
	$SQL = Start();
	$getAdmin = $SQL->q_getAdmin($AdminID)->f_autoReturn();
	if(count($getAdmin) != 1){ exit();}
	
	//token code
	$Token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
	
	$SQL->q_addCSRFToken($AdminID, $Token)->f_autoVoid();
	
	removeOldTokens();
	
	return $Token;	
}

function removeOldTokens(){
	$SQL = Start();
	$SQL->q_removeOldCSRFTokens()->f_autoVoid();
}

function getToken($AdminID, $Token){	
	//preliminary checks
	if(!is_numeric($AdminID)){ return false;;}
	$SQL = Start();
	$getAdmin = $SQL->q_getAdmin($AdminID)->f_autoReturn();
	if(count($getAdmin) != 1){ return false;}
	
	//token code
	removeOldTokens();
	
	$result = $SQL->q_getCSRFToken($AdminID, $Token)->f_autoReturn();
	
	if(count($result) != 1){ //fail to validate token
		return false;
	} else { //token found
		$SQL->q_removeCSRFToken($AdminID, $Token)->f_autoVoid();
		return true;
	}
	
}

function isValidToken($AdminID, $Token){
	//preliminary checks
	if(!is_numeric($AdminID)){ return false;;}
	$SQL = Start();
	$getAdmin = $SQL->q_getAdmin($AdminID)->f_autoReturn();
	if(count($getAdmin) != 1){ return false;}
	
	//token code
	$result = $SQL->q_verifyCSRFToken($AdminID, $Token)->f_autoReturn();
	
	if(count($result) == 1){ //token is valid
		return true;
	} else {
		return false;
	}
}


?>