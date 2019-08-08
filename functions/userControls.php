<?php 
	require_once(dirname(__FILE__) . "/../core/sql.php");
	session_start();
	
function createCalendar($userID, $year, $name){
	try{
		$SQL = Start();
		$SQL->q_createUserCalendar($userID, $year, $name)->f_autoVoid();
		
		$calendarID = $SQL->q_getNewestUserCalendar($userID)->f_autoReturn()[0][0];
		
		$SQL->q_createUserCalendarDays($calendarID)->f_autoVoid();
		
		$count = $SQL->q_getNumberOfCalendars($userID)->f_autoReturn()[0][0];
		
		if($count == 1){
			setUserSetting($userID, "defaultCalendar", $calendarID);
		}
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function deleteCalendar($calendarID){
	try{
		$SQL = Start();
		
		$userID = $SQL->q_getUserIDFromCalendarID($calendarID)->f_autoReturn()[0][0];
		
		$SQL->q_deleteUserCalendar($calendarID)->f_autoVoid();
		
		//updates default calendar setting
		if(getUserSetting($userID, "defaultCalendar") == $calendarID){
			$calendarIDS = getCalendarIDs($userID);
			
			if(count($calendarIDS) > 0){
				//sets default to first in list
				setUserSetting($userID, "defaultCalendar", $calendarIDS[0][0]);
			} else {
				//else if no remaining calendars, unsets setting
				setUserSetting($userID, "defaultCalendar", "");
			}
		}
				
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getCalendarDays($calendarID){
	try{
		$SQL = Start();
		return $SQL->q_getCalendarDays($calendarID)->f_autoReturn();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getCalendarInformation($calendarID){
	try{
		$SQL = Start();
		return $SQL->q_getCalendarInformation($calendarID)->f_autoReturn();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setUserDayContent($dayID, $content){
	try{
		$SQL = Start();
		$SQL->q_setUserDayContent($dayID, $content)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setUserDayColor($dayID, $color){
	try{
		$SQL = Start();
		$SQL->q_setUserDayColor($dayID, $color)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setCalendarName($calendarID, $name){
	try{
		$SQL = Start();
		$SQL->q_setCalendarName($calendarID, $name)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function transferUserInformation($from, $to, $fromArray, $toArray){
	try{
		$SQL = Start();
		
		$cycles = min(sizeof($fromArray), sizeof($toArray));
		
		//content loop
		$iterant = $SQL->q_transferContent($fromDayID, $toDayID)->f_prepare()->stmt;
		for($i = 0; $i < $cycles; $i++){
			$iterant->execute( array($fromArray[$i], $toArray[$i]) );
		}
		
		//color loop
		$iterant = $SQL->q_transferColor($fromDayID, $toDayID)->f_prepare()->stmt;
		for($i = 0; $i < $cycles; $i++){
			$iterant->execute( array($fromArray[$i], $toArray[$i]) );
		}
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function addUserSetting($userID, $setting, $value){
	try{
		$SQL = Start();
		
		$SQL->q_addUserSetting($userID, $setting, $value)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getUserSetting($userID, $setting){
	try{
		$SQL = Start();
		
		return $SQL->q_getUserSetting($userID, $setting)->f_autoReturn()[0][0];
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setUserSetting($userID, $setting, $value){
	try{
		$SQL = Start();
		
		$SQL->q_setUserSetting($userID, $setting, $value)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getCalendarIDs($userID){
	try{
		$SQL = Start();
		
		return $SQL->q_getCalendarIDs($userID)->f_autoReturn();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function calendarExists($calendarID){
	try{
		$SQL = Start();
		
		$result = $SQL->q_calendarExists($calendarID)->f_autoReturn()[0][0];
		
		if($result == $calendarID){
			return true;
		}
		
		return false;
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function calendarBelongsToUserID($calendarID, $userID){
	try{
		$SQL = Start();
		
		$result = $SQL->q_calendarBelongsToUserID($calendarID, $userID)->f_autoReturn()[0][0];
		
		if($result == $calendarID){
			return true;
		}
		
		return false;
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function dayBelongsToUserID($dayID, $userID){
	try{
		$SQL = Start();
		
		$dayInfo = $SQL->q_getDayInformation($dayID)->f_autoReturn()[0];
		
		if(calendarBelongsToUserID($dayInfo["CalendarID"], $userID)){
			return true;
		}
		
		return false;
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getCurrentMasterYear(){
	try{
		$SQL = Start();
		
		return $SQL->q_getMasterSetting("masterYear")->f_autoReturn()[0][0];
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}






?>