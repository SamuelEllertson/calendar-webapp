<?php 
	require_once(dirname(__FILE__) . "/../core/sql.php");
	session_start();
	
function createMasterCalendar($start, $end){
	try{
		$SQL = Start();
		
		deleteMasterCalendar();

		$currentDate = new DateTime($start);
		$endDate     = new DateTime($end);

		$iterate = $SQL->q_createMasterDay("","","")->f_prepare()->stmt;
		
		for($currentDate; $currentDate <= $endDate; $currentDate->add(new DateInterval('P1D'))){
			$iterate->execute(array("", 0, $currentDate->format("Y-m-d")));
		}
	}catch(Exception $e){
		echo $e; //###remove later
	}
}
	
function deleteMasterCalendar(){
	try{
		$SQL = Start();
		$SQL->q_truncateMasterTable()->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setMasterDayContent($dayID, $content){
	try{
		$SQL = Start();
		$SQL->q_setMasterDayContent($dayID, $content)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setMasterDayColor($dayID, $color){
	try{
		$SQL = Start();
		$SQL->q_setMasterDayColor($dayID, $color)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getMasterSetting($setting){
	try{
		$SQL = Start();
		
		return $SQL->q_getMasterSetting($setting)->f_autoReturn()[0][0];
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function setMasterSetting($setting, $value){
	try{
		$SQL = Start();
		
		$SQL->q_setMasterSetting($setting, $value)->f_autoVoid();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

function getMasterCalendarDays(){
	try{
		$SQL = Start();
		return $SQL->q_getMasterCalendarDays()->f_autoReturn();
		
	}catch(Exception $e){
		echo $e; //###remove later
	}
}

?>