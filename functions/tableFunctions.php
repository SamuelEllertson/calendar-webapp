<?php 
	require_once(dirname(__FILE__) . "/../core/sql.php");
	require_once(dirname(__FILE__) . "/userControls.php");
	require_once(dirname(__FILE__) . "/masterControls.php");
	session_start();
	
function getUserDefaultCalendar($userID){
	try{	
		
		$defaultCalendarID = getUserSetting($userID,"defaultCalendar");

		if($defaultCalendarID != "" and is_numeric($defaultCalendarID)){
			return getUserCalendarDisplay($defaultCalendarID);
		} else {
			$calendarIDS = getCalendarIDs($userID);
			
			if(count($calendarIDS) > 0){
				//calendars > 0 && no default -> should be false. But just in case.
				return getUserCalendarDisplay($calendarIDS[0][0]);
			} else {
				return noCalendarsToDisplay();
			}
		}

	}catch(Exception $e){
		echo $e; //###remove this
	}
}

function getDefaultCalendarButtons($userID){
	$ids = getCalendarIDs($userID);
	
	$numCalendars = count($ids);
	if($numCalendars == 0){
		return noCalendarsToDisplay(); 
	}
	
	$defaultCalendarID = getUserSetting($userID, "defaultCalendar");
	
	$buttonStart = '<button class="w3-button w3-margin-top s_fullWidth" data-calendarID="';
	$buttonStartBlue = '<button class="w3-button w3-margin-top w3-blue s_fullWidth" data-calendarID="';
	$buttonMiddle = '">';
	$buttonEnd = '</button><br />';
	
	$output = '';
	
	for($i = 0; $i < $numCalendars; $i++){
		$calendar = getCalendarInformation($ids[$i]["CalendarID"]);
		
		if($ids[$i][0] != $defaultCalendarID){ //normal Button
			$output .= $buttonStart . $ids[$i][0] . $buttonMiddle . $calendar[0]["Name"] . $buttonEnd;
		} else { //default button -> blue
			$output .= $buttonStartBlue . $ids[$i][0] . $buttonMiddle . $calendar[0]["Name"] . $buttonEnd;
		}
		
	}
	
	return $output;
}

function getUserCalendarDisplay($calendarID){
	if(!calendarExists($calendarID)){
		return noCalendarsToDisplay();
	}
	
	$info = getCalendarInformation($calendarID)[0];
	$days = getCalendarDays($calendarID);
	$numDays = count($days);
	$initialDayOfWeek = date('w', strtotime($days[0]["Date"]));
	
	$calendarStart1 = '<div class="s_calendar" data-calendarID="';
	$calendarStart2 = '">';
	
	$startTitle = '<caption><h2 class="w3-blue w3-center">';
	$endTitle = '</h2></caption>';
	
	$header = '<div class="s_row s_header"><div class="s_cell">Sunday</div><div class="s_cell">Monday</div><div class="s_cell">Tuesday</div><div class="s_cell">Wednesday</div><div class="s_cell">Thursday</div><div class="s_cell">Friday</div><div class="s_cell">Saturday</div></div>';
	
	$startRow = '<div class="s_row">';
	$endRow = '</div>';
	
	$cellStart1 = '<div class="s_cell s_color_';
	$cellStart2 = '" data-dayid="';
	$cellStart3 = '" data-dow="';
	$cellStart4 = '" data-color="';
	$cellStart5 = '">';
	
	$endCell = '</div>';
	
	$emptyCell = '<div class="s_cell"></div>';
	
	$startDate = '<div class="s_date">';
	$endDate = '</div>';
	
	//$textAreaStart = '<textarea class="s_input">'; //replaced with editable div
	//$textAreaEnd = '</textarea>';
	$textAreaStart = '<div class="s_input textarea" contenteditable="true">';
	$textAreaEnd = '</div>';
	
	$calendarEnd = '</div>';
	
	$output = '';
	
	//create calendar container with data: calendarID
	$output .= $calendarStart1 . $calendarID . $calendarStart2;
	
	//add title based on calendar name
	$output .= $startTitle;
	$output .= $info["Name"];
	$output .= $endTitle;
	
	//add header (sun, mon, tues...)
	$output .= $header;
	
	//start row
	$output .= $startRow;
	
	//adding inital blank days
	for($i = 0; $i < $initialDayOfWeek; $i++){
		$output .= $emptyCell;
	}
	 //adding day cells
	$currentDayOfWeek = $initialDayOfWeek;
	for($i = 0; $i < $numDays; $i++){
		if($currentDayOfWeek == 7){
			$currentDayOfWeek = 0;
			$output .= $endRow;
			$output .= $startRow;
		}
		
		$currentDay = $days[$i];
		$dateObject = new DateTime($currentDay["Date"]);
		
		$output .= $cellStart1 . $currentDay["Color"] . $cellStart2 . $currentDay["DayID"] . $cellStart3 . $currentDayOfWeek. $cellStart4 . $currentDay["Color"] . $cellStart5;
		$output .= $startDate . $dateObject->format("n/j") . $endDate;
		$output .= $textAreaStart . $currentDay["Content"] . $textAreaEnd;
		$output .= $endCell;
		
		$currentDayOfWeek++;
	}
	
	for($i = 6; $i >= $currentDayOfWeek; $i--){
		$output .= $emptyCell;
	}
	
	//ending row
	$output .= $endRow;
	
	//end calendar
	$output .= $calendarEnd;
	
	return $output;
}

function getCalendarButtons($userID){
	$ids = getCalendarIDs($userID);
	
	$numCalendars = count($ids);
	if($numCalendars == 0){
		return noCalendarsToDisplay(); 
	}
	
	$buttonStart = '<button class="w3-button w3-margin-top s_fullWidth" data-calendarID="';
	$buttonMiddle = '">';
	$buttonEnd = '</button><br />';
	
	$output = '';
	
	for($i = 0; $i < $numCalendars; $i++){
		$calendar = getCalendarInformation($ids[$i]["CalendarID"]);
				
		$output .= $buttonStart . $ids[$i][0] . $buttonMiddle . $calendar[0]["Name"] . $buttonEnd;
	}
	
	return $output;
}

function getRemoveCalendarButtons($userID){
	$ids = getCalendarIDs($userID);
	
	$numCalendars = count($ids);
	if($numCalendars == 0){
		return noCalendarsToDisplay();; 
	}
	
	$button1 = '<div class="w3-right-align w3-border w3-border-blue s_remCal"><input type="hidden" name="type" value="removeCalendar"><input type="hidden" name="calendarID" value="';
	$button2 = '"><label>';
	$button3 = '</label><button type="submit" class="w3-red w3-border-0 s_findme_removeButton">X</button></div>';
	
	$output = '';
	
	for($i = 0; $i < $numCalendars; $i++){
		$calendar = getCalendarInformation($ids[$i]["CalendarID"]);
				
		$output .= $button1 . $ids[$i][0] . $button2 . $calendar[0]["Name"] . $button3;
	}
	
	return $output;
}

function noCalendarsToDisplay(){
	//called when there are no calendars to display, should have a create calendar option.
	return "<p>Calendar(s) not found</p>"; //###rework this later
}

function getMasterCalendarDisplay(){
	
	$days = getMasterCalendarDays();
	$numDays = count($days);
	$initialDayOfWeek = date('w', strtotime($days[0]["Date"]));
	
	$calendarStart = '<div class="s_calendar">';
	
	$startTitle = '<caption><h2 class="w3-blue w3-center">';
	$endTitle = '</h2></caption>';
	
	$header = '<div class="s_row s_header"><div class="s_cell">Sunday</div><div class="s_cell">Monday</div><div class="s_cell">Tuesday</div><div class="s_cell">Wednesday</div><div class="s_cell">Thursday</div><div class="s_cell">Friday</div><div class="s_cell">Saturday</div></div>';
	
	$startRow = '<div class="s_row">';
	$endRow = '</div>';
	
	$cellStart1 = '<div class="s_cell s_color_';
	$cellStart2 = '" data-dayid="';
	$cellStart3 = '" data-dow="';
	$cellStart4 = '" data-color="';
	$cellStart5 = '">';
	
	$endCell = '</div>';
	
	$emptyCell = '<div class="s_cell"></div>';
	
	$startDate = '<div class="s_date">';
	$endDate = '</div>';
	
	//$textAreaStart = '<textarea class="s_input">'; //replaced with editable div
	//$textAreaEnd = '</textarea>';
	$textAreaStart = '<div class="s_input textarea" contenteditable="true">';
	$textAreaEnd = '</div>';
	
	$calendarEnd = '</div>';
	
	$output = '';
	
	//create calendar container with data: calendarID
	$output .= $calendarStart;
	
	//add title based on calendar name
	$output .= $startTitle;
	$output .= "Master Calendar";
	$output .= $endTitle;
	
	//add header (sun, mon, tues...)
	$output .= $header;
	
	//start row
	$output .= $startRow;
	
	//adding inital blank days
	for($i = 0; $i < $initialDayOfWeek; $i++){
		$output .= $emptyCell;
	}
	 //adding day cells
	$currentDayOfWeek = $initialDayOfWeek;
	for($i = 0; $i < $numDays; $i++){
		if($currentDayOfWeek == 7){
			$currentDayOfWeek = 0;
			$output .= $endRow;
			$output .= $startRow;
		}
		
		$currentDay = $days[$i];
		$dateObject = new DateTime($currentDay["Date"]);
		
		$output .= $cellStart1 . $currentDay["Color"] . $cellStart2 . $currentDay["DayID"] . $cellStart3 . $currentDayOfWeek. $cellStart4 . $currentDay["Color"] . $cellStart5;
		$output .= $startDate . $dateObject->format("n/j") . $endDate;
		$output .= $textAreaStart . $currentDay["Content"] . $textAreaEnd;
		$output .= $endCell;
		
		$currentDayOfWeek++;
	}
	
	for($i = 6; $i >= $currentDayOfWeek; $i--){
		$output .= $emptyCell;
	}
	
	//ending row
	$output .= $endRow;
	
	//end calendar
	$output .= $calendarEnd;
	
	return $output;
} 

?>