<?php
session_start();
require_once(dirname(__FILE__) . "/../core/securePage.php");
require_once(dirname(__FILE__) . "/userControls.php");
require_once(dirname(__FILE__) . "/tableFunctions.php");


$type = $_POST["type"];

switch ($type) {
    case "getCalendarDisplay":
        if(!isset($_POST["calendarID"])){exit();}
		$calendarID = $_POST["calendarID"];
		if(!calendarBelongsToUserID($calendarID, $_SESSION["userID"])){exit();}
		echo getUserCalendarDisplay($calendarID);
        break;	
		
	case "updateDay":
        if(!isset($_POST["dayID"]) || !isset($_POST["newText"]) || !isset($_POST["color"])){exit();}
		$dayID = $_POST["dayID"];
		$newText = $_POST["newText"];
		$color = $_POST["color"];
		if(!dayBelongsToUserID($dayID, $_SESSION["userID"])){exit();}
		setUserDayContent($dayID, $newText);
		setUserDayColor($dayID, $color);
		echo "Update Successful";
		break;
		
	case "addCalendar":
		if(!isset($_POST["calendarName"]) || !isset($_SESSION["userID"])){exit();}
		$userID = $_SESSION["userID"];
		$name = $_POST["calendarName"];
		$year = getCurrentMasterYear();
		createCalendar($userID, $year, $name);
		header("Location: ../userMain.php");
		break;
		
	case "removeCalendar":
		if(!isset($_POST["calendarID"]) || !isset($_SESSION["userID"])){exit();}
		$calendarID = $_POST["calendarID"];
		$userID = $_SESSION["userID"];
		if(!calendarBelongsToUserID($calendarID, $userID)){exit();}
		deleteCalendar($calendarID);
		break;
		
	case "transferContent":
		if(!isset($_SESSION["userID"]) || !isset($_POST["fromID"]) || !isset($_POST["toID"]) || !isset($_POST["fromArray"]) || !isset($_POST["toArray"])){exit();}
		$userID = $_SESSION["userID"];
		$fromID = $_POST["fromID"];
		$toID = $_POST["toID"];
		$fromArray = $_POST["fromArray"];
		$toArray = $_POST["toArray"];
		
		if(!calendarBelongsToUserID($fromID, $userID) || !calendarBelongsToUserID($toID, $userID)){
			exit();
		}
		transferUserInformation($fromID, $toID, $fromArray, $toArray);
		break;
		
	case "updateUserSetting":
		if(!isset($_SESSION["userID"]) || !isset($_POST["setting"]) || !isset($_POST["value"])){
			exit();
		}
		$userID = $_SESSION["userID"];
		$setting = $_POST["setting"];
		$value = $_POST["value"];
		
		setUserSetting($userID, $setting, $value);
		break;
		
	case "renameCalendar":
		if(!isset($_SESSION["userID"]) || !isset($_POST["calendarID"]) || !isset($_POST["name"])){
			exit();
		}
		$userID = $_SESSION["userID"];
		$calendarID = $_POST["calendarID"];
		$name = $_POST["name"];
		if(!calendarBelongsToUserID($calendarID, $userID)){exit();}
		setCalendarName($calendarID, $name);
		break;
		
	case "getButtons":
		if(!isset($_SESSION["userID"]) || !isset($_POST["buttonType"])){
			exit();
		}
		$userID = $_SESSION["userID"];
		$buttonType = $_POST["buttonType"];
		
		if($buttonType == "calendarButtons"){
			echo getCalendarButtons($userID);
		} 
		else if($buttonType == "defaultCalendarButtons"){
			echo getDefaultCalendarButtons($userID);
		}
		else if($buttonType == "removeCalendarButtons"){
			echo getRemoveCalendarButtons($userID);
		}
		break;
}


?>