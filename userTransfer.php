<?php 
	require_once(dirname(__FILE__) . '/core/securePage.php');
	require_once(dirname(__FILE__) . '/functions/tableFunctions.php');
	session_start();

	$userID = $_SESSION["userID"];
	$username = $_SESSION["username"];
	$name = $_SESSION["name"];

	if(!isset($_GET["fromID"]) || !isset($_GET["fromID"])){
		header("Location: error.php");
		exit();
	}

	$fromID = $_GET["fromID"];
	$toID = $_GET["toID"];

	if(!calendarBelongsToUserID($fromID, $userID) || !calendarBelongsToUserID($toID, $userID)){
		header("Location: core/logout.php");
		exit();
	}

	//all checks passed, start output.
	require_once(dirname(__FILE__) . '/core/header.php'); 

?>
<title>Transfer Content Tool</title>
<style>
	.s_date{
		cursor: cell;
	}
</style>

<body>

<header id="topBanner" class="w3-container w3-margin-bottom w3-card-4 w3-blue s_banner" style="display: block">
	<h2  style="display: inline-block">YHS Calendar > Transfer Content</h2>
	<a class="w3-right w3-button w3-margin" href="userManage.php">Back</a>
</header>

<!-- HIDDEN BY DEFAULT: TOOLBOX-->
<div id="toolHeader" class="s_toolHeader s_boxShadow w3-blue" style="display: none">
	<div><button class="w3-button s_full w3-blue" onclick="startPin()">Start Pin</button></div>
	<div><button class="w3-button s_full w3-blue" onclick="endPin()">End Pin</button></div>
	<div><button class="w3-button s_full w3-blue" onclick="totalClear()">Cancel</button></div>
	<div><button id="transferButton" class="w3-button s_full w3-blue" onClick="transferInformation()" disabled>Transfer</button></div>
	
	<button class="w3-button w3-center s_full" onClick="updateCurrentCell(true)">Save</button>
	<br><br>

	<table class="s_button_table">
		<tr>
			<td><button class="s_color_button s_color_0" onClick="changeColor(0)"></button></td>
			<td><button class="s_color_button s_color_1" onClick="changeColor(1)"></button></td>
		</tr>
		<tr>
			<td><button class="s_color_button s_color_2" onClick="changeColor(2)"></button></td>
			<td><button class="s_color_button s_color_3" onClick="changeColor(3)"></button></td>
		</tr>
		<tr>
			<td><button class="s_color_button s_color_4" onClick="changeColor(4)"></button></td>
			<td><button class="s_color_button s_color_5" onClick="changeColor(5)"></button></td>
		</tr>
	</table>	
	<br>

	<!-- add link button -->
	<button class="w3-button s_full" onClick="toggleLinkBox()">Add Link</button>
	<label class="s_findme_addLink" style="display: none">URL</label>
	<input id="linkURL" class="w3-input w3-border s_findme_addLink" style="display: none">
	<label class="s_findme_addLink" style="display: none">Text</label>
	<input id="linkText" class="w3-input w3-border s_findme_addLink" style="display: none">
	<br class="s_findme_addLink" style="display: none">
	<button id="linkGoButton" class="w3-button s_full s_findme_addLink" onClick="addLink()" style="display: none">Go</button>
	<br><br>


			
	<label>Include:</label>
	<br>
	<input id="skipWeekend" class="w3-check" type="checkbox" <?php if(getUserSetting($userID, "skipWeekends") == "false"){echo " checked ";} ?>  onChange="updateUserSetting('skipWeekends', !$(this).is(':checked'));">	
	<label>Weekends</label>
	<br>
	<input id="skipNoSchool" class="w3-check" type="checkbox" <?php if(getUserSetting($userID, "skipNoSchool") == "false"){echo " checked ";} ?> onChange="updateUserSetting('skipNoSchool', !$(this).is(':checked'));">	
	<label>No School</label>
	<br>
	
</div>


<!-- TOOLBOX STANDIN -->
<div id="standin" class="s_toolHeader s_toolHeaderStandinDiv">
<div class="s_toolHeader s_boxShadow w3-blue s_toolHeaderStandin"></div>
</div>


<div id="tables" class="w3-row-padding">
	
	<div id="fromTable" class="w3-col l6 m6 s6">
		<?php echo getUserCalendarDisplay($fromID) ?>
	</div>
	
	<div id="toTable" class="w3-col l6 m6 s6">
		<?php echo getUserCalendarDisplay($toID) ?>
	</div>
	
</div>

<script>
var fromCells;
var toCells;
	
var fromArray = [];
var toArray = [];
	
var startCell;
var startCellIndex;
	
var to_startCell;
var to_startCellIndex;
	
var endCell;
var endCellIndex;
	
var length;
var currentCell;
	
var started = false;
var ended = false;
var isClosed = false;

//settings
var skipNoSchool = <?php if(getUserSetting($userID, "skipNoSchool") == "false"){echo "false";}else{echo "true";} ?>;
var skipWeekends = <?php if(getUserSetting($userID, "skipWeekends") == "false"){echo "false";}else{echo "true";} ?>;

var initialText;
	
$( document ).ready(function() {
	
	newCalendarHandlers();
	linkHandlers();
	
	$("#standin").on("mouseover",function(){
		$("#standin").hide();
		$("#toolHeader").show();
	});
	
	$("#toolHeader").on("mouseleave",function(){
		$("#toolHeader").hide();
		$("#standin").show();
	});
	
	$("#tables").on("mouseover",function(){
		$("#toolHeader").hide();
		$("#standin").show();
	});
	
	//handler for key presses
	$(document).keyup(function(e) {
		if (e.keyCode == 27) { //ESCAPE KEY`
			totalClear();
		}
	});
});
	
$( window ).unload(function() {
  //update current day on unload
	updateCurrentCell();
});
	
function toggleLinkBox(){
	$(".s_findme_addLink").toggle();
}	

function addLink(){
	var linkURL = $("#linkURL").val();
	var linkText = $("#linkText").val();
	
	if(linkURL == "" || linkText == ""){
		alert("Please enter a URL and display text");
		return;
	}
	if(currentCell == undefined){
		alert("Please select a date");
		return;
	}
	
	if(linkURL.indexOf("http") == -1){ //force absolute url
		linkURL = "http://" + linkURL;
	}
	
	var linkHTMLString = '<a class="s_findme_link" target="_blank" contenteditable="true" href="' + linkURL + '">' + linkText + '</a>'
	
	currentCell.children().eq(1).append(linkHTMLString);
	
	linkHandlers();
	updateCurrentCell();
}
	
function linkHandlers(){
	
	$(".s_findme_link").hover(function(){
		console.log("hit");
		$(this).attr('contenteditable','false');
	}, function(){
		$(this).attr('contenteditable','true');
	});
}

function totalClear(){
	started = false;
	ended = false;
	isClosed = false;
	startCell = undefined;
	endCell = undefined;
	to_startCell = undefined;
	length = 0;
	clearSelected();
	updateTransferButton();
}
	
function changeColor(color){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		var newText = currentCell.children().eq(1).html();
		
		currentCell.removeClass("s_color_0 s_color_1 s_color_2 s_color_3 s_color_4 s_color_5").addClass("s_color_"+color).attr("data-color", color);
		
		updateDay(dayID, newText, color);
	}
}

function clearSelected(){
	$(".s_cellSelected").each(function(){
		
		$(this).removeClass("s_cellSelected");
		void this.offsetWidth; //used to trigger a reflow bc async javascript is dumb.
	});
}
	
function updateCurrentCell(showMessage = false){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		var newText = currentCell.children().eq(1).html();
		var color = currentCell.data("color");
		updateDay(dayID, newText, color, showMessage);
	}
}
	
function newCalendarHandlers(){
	
	fromCells = $("#fromTable .s_date").parent();
	toCells = $("#toTable .s_date").parent();
	
	$(".textarea").on("focusin", function(){
		currentCell = $(this).parent();
		initialText = $(this).html();
	});
	
	$(".textarea").on("focusout", function(){
		if($(this).html() != initialText){
			var dayID = $(this).parent().data("dayid");
			var newText = $(this).html();
			var color = $(this).parent().data("color");
			updateDay(dayID, newText, color);
		}
	});
	
	$(".s_date").on("click", function(){
		currentCell = $(this).parent();
		
		if(started && ended && (toCells.index(currentCell) != -1)){
			to_startPin(); //clicked toTable + needs closing -> close
		}
		else if(started && !ended && (toCells.index(currentCell) != -1)){
			ended = true;
			endCell = startCell;
			endCellIndex = fromCells.index(endCell);
			to_startPin(); //if only started, but clicks in toTable -> do everything for them
		}
		else if(toCells.index(currentCell) != -1){
			return; //clicked toTable + cant be closed -> do nothing 
		}
		else if(!started){
			startPin(); //if havent started -> startpin
		} 
		else if(fromCells.index(currentCell) < fromCells.index(startCell)){
			startPin(); //if cell is before first -> startpin
		}
		else if(!ended){
			endPin(); //if havent ended ->endpin
		} else if(fromCells.index(currentCell) > fromCells.index(endCell)){
			endPin(); //if cell is after last -> endpin
		}
		else if(currentCell.is(startCell)){
			endPin(); //if clicking startCell again -> endPin()
		}
		else if(currentCell.is(endCell)){
			startPin(); //if clicking endCell again ->startPin()
		}
		else if(fromCells.index(currentCell) < (((fromCells.index(endCell) - fromCells.index(startCell)) / 2 ) + fromCells.index(startCell))){
			startPin(); //if clicked middle cell closer to front -> startPin
		}
		else if(fromCells.index(currentCell) >= (((fromCells.index(endCell) - fromCells.index(startCell)) / 2 ) + fromCells.index(startCell))){
			endPin(); //if clicked middle cell clsoer to end -> endPin()
		}
		else{
			console.log("shoudlnt fire");
		}
	});
	
	//prevent selecting date text
	$(".s_date").attr('unselectable', 'on');
	$(".s_date").css("-webkit-user-select","none");
	$(".s_date").css("-moz-user-select","none");
	$(".s_date").css("-ms-user-select","none");
	$(".s_date").css("-o-user-select","none");
	$(".s_date").css("user-select","none");
}

function startPin(){
	if(currentCell == undefined){
		return;
	}
	if(toCells.index(currentCell) != -1){ //in the to calendar
		to_startPin();
		return;
	}
	clearSelected();
	started = true;
	startCell = currentCell;
	startCell.children().eq(1).addClass("s_cellSelected");
	startCellIndex = fromCells.index(startCell);
	if(ended && (startCellIndex < endCellIndex)){
		clearSelected();
		fill();
	}
	if(to_startCell != undefined && isClosed){
		currentCell = to_startCell; //needed for to_startPin to work properly
		to_startPin(); //recalculate the toTable area for them if they chose a start cell
	}
	updateTransferButton();
}
	
function endPin(){
	if(currentCell == undefined){
		return;
	}
	if(toCells.index(currentCell) != -1){ //in the to calendar
		return;
	}
	clearSelected();
	ended = true;
	endCell = currentCell;
	endCell.children().eq(1).addClass("s_cellSelected");
	endCellIndex = fromCells.index(endCell);
	if(started){
		clearSelected();
		fill();
	}
	if(to_startCell != undefined && isClosed){
		currentCell = to_startCell; //needed for to_startPin to work properly
		to_startPin(); //recalculate the toTable area for them if they chose a start cell
	}
	updateTransferButton();
}
	
function fill(){
	var workingCell;
	length = endCellIndex - startCellIndex + 1; 
	fromArray = new Array();
	
	for(i = 0; i < endCellIndex - startCellIndex + 1; i++){
		
		workingCell = $(fromCells[startCellIndex + i]);
		
		if(skipNoSchool){
			if( workingCell.hasClass("s_color_3") ){
				length--;
				continue;
			}
		}
		if(skipWeekends){ 
			if( workingCell.data("dow") == "0" || workingCell.data("dow") == "6" ){
				length--;
				continue;
			}
		}
		
		workingCell.children().eq(1).addClass("s_cellSelected");
		fromArray.push( $(fromCells[startCellIndex + i]).data("dayid") );
	}
	
}
	
function to_startPin(){
	var workingCell;
	if(!started || !ended){
		return;
	}
	to_startCell = currentCell;
	to_startCellIndex = toCells.index(currentCell);
	isClosed = true;
	
	clearSelected(); //for animation consistency
	fill();			 //for animation consistency
		
	toArray = new Array();
	for(i = 0; i < length; i++){
		
		workingCell = $(toCells[to_startCellIndex + i]);
		
		if(skipNoSchool){
			if( workingCell.hasClass("s_color_3") ){
				length++;
				continue;
			}
		}
		if(skipWeekends){ 
			if( workingCell.data("dow") == "0" || workingCell.data("dow") == "6" ){
				length++;
				continue;
			}
		}
		
		workingCell.children().eq(1).addClass("s_cellSelected");
		toArray.push( workingCell.data("dayid") );
	}
	updateTransferButton();
}
	
function updateTransferButton(){
	if(started && ended && isClosed){
		$("#transferButton").prop( "disabled", false );
	} else{
		$("#transferButton").prop( "disabled", true );
	}
}

	
//AJAX +++++++++++++++++++++++++++++++
function updateUserSetting(setting, value){
	if(setting == "skipWeekends"){
		if(value){
			skipWeekends = true;
		}else{
			skipWeekends = false;
		}
		
		if(isClosed && to_startCell != undefined){
			currentCell = to_startCell;
			to_startPin();
		} else{
			clearSelected();
			fill();
		}
	}
	if(setting == "skipNoSchool"){
		if(value){
			skipNoSchool = true;
		}else{
			skipNoSchool = false;
		}
		
		if(isClosed && to_startCell != undefined){
			currentCell = to_startCell;
			to_startPin();
		} else{
			clearSelected();
			fill();
		}
	}
	
	request1 = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "updateUserSetting",
			"setting": setting,
			"value": value
		}
	});
	
	request1.done(function (response, textStatus, jqXHR){
		console.log("setting successfully changed");
    });
	request1.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
    });
	
}
	
function refreshCalendars(){
	request1 = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "getCalendarDisplay",
			"calendarID": <?php echo $fromID ?>
		}
	});
	
	request1.done(function (response, textStatus, jqXHR){
		$("#fromTable").html(response);
    });
	request1.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
		if(showMessage){showError();}
    });
	
	request2 = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "getCalendarDisplay",
			"calendarID": <?php echo $toID ?>
		}
	}); 
	
	request2.done(function (response, textStatus, jqXHR){
		$("#toTable").html(response);
		newCalendarHandlers();
    });
	request2.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
    });
}
	
function transferInformation(){
	console.log("transferInformation called");
	request = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "transferContent",
			"fromID": <?php echo $fromID; ?>,
			"toID": <?php echo $toID; ?>,
			"fromArray": fromArray,
			"toArray": toArray
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		isClosed = false;
		started = false;
		ended = false;
		refreshCalendars();
		updateTransferButton();
		totalClear();
		console.log("transfer succesful");
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
		//if(showMessage){showError();} //###change this
    });
	
}
	
function updateDay(dayID, newText, color, showMessage = false){
	request = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "updateDay",
			"dayID": dayID, 
			"newText": newText,
			"color": color
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		console.log(response);
		if(showMessage){showSuccess();}
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
		//if(showMessage){showError();}
    });
}
	
function showSuccess(){
	var tempObj = $("#topBanner");
	tempObj.removeClass("w3-blue").addClass("w3-green");
	setTimeout(function(){ tempObj.removeClass("w3-green").addClass("w3-blue"); },700)
	
}

function testThis(){
	console.log("hit");
}
</script>

</body>
</html>