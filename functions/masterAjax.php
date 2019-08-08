<?php
require_once(dirname(__FILE__) . "/../core/adminSecurePage.php");
require_once(dirname(__FILE__) . "/userControls.php");
require_once(dirname(__FILE__) . "/masterControls.php");
require_once(dirname(__FILE__) . "/tableFunctions.php");
session_start();

$type = $_POST["type"];


switch ($type) {
    case "recycleMasterCalendar":
		if(!isset($_POST["startDay"]) || !isset($_POST["endDay"]) || !$_SESSION["admin"]){
			exit();
		}
		$startDay = $_POST["startDay"];
		$endDay = $_POST["endDay"];
        deleteMasterCalendar();
		createMasterCalendar($startDay, $endDay);
        break;
		
	case "getMasterCalendar":
		if(!$_SESSION["admin"]){
			exit();
		}
		echo getMasterCalendarDisplay();
		break;
		
	case "updateMasterCalendarContent":
		if(!$_SESSION["admin"] || !isset($_POST["dayID"]) || !isset($_POST["content"])){
			exit();
		}
		$dayID = $_POST["dayID"];
		$content = $_POST["content"];
		setMasterDayContent($dayID, $content);
		break;
		
	case "updateMasterCalendarColor":
		if(!$_SESSION["admin"] || !isset($_POST["dayID"]) || !isset($_POST["color"])){
			exit();
		}
		$dayID = $_POST["dayID"];
		$color = $_POST["color"];
		setMasterDayColor($dayID, $color);
		break;
		
	case "updateMasterCalendarDay":
		if(!$_SESSION["admin"] || !isset($_POST["dayID"]) || !isset($_POST["color"]) || !isset($_POST["content"])){
			echo $_SESSION["admin"] . " " .$_POST["dayID"] . " " .$_POST["content"] . " " . $_POST["color"];
			exit();
		}
		$dayID = $_POST["dayID"];
		$color = $_POST["color"];
		$content = $_POST["content"];
		setMasterDayColor($dayID, $color);
		setMasterDayContent($dayID, $content);
		break;
	
}


?>