<?php 
	require_once(dirname(__FILE__) . '/core/adminSecurePage.php');
	require_once(dirname(__FILE__) . '/core/header.php');
	require_once(dirname(__FILE__) . '/functions/tableFunctions.php');
	require_once(dirname(__FILE__) . '/functions/masterControls.php');
	session_start();

	$userID = $_SESSION["userID"];
	$username = $_SESSION["username"];
	$name = $_SESSION["name"];
?>
<style>
	.s_toolboxContainer{
		position: fixed;
		margin: 2px 2px 2px 2px;
		left: 5px;
		top: 94px;
		width: 14%;
		max-width: 200px;
		box-shadow: 0px 2px 20px #888888;
	}
	.s_toolbox{
		display: flex;
		flex-direction: column;
		width: 100%;
	}
	.s_container{
		width: 80%;
		transform: translateX(calc(18% + 25px));
		margin: 2px 2px 2px 2px; 
	}
</style>
<script src='https://www.google.com/recaptcha/api.js'></script>
<title>Master Calendar</title>


<body>
<header class="w3-container w3-margin-bottom w3-card-4 w3-blue s_banner" style="display: block">
	<h2  style="display: inline-block">Admin Tools</h2>
	<a class="w3-right w3-button w3-margin" href="userMain.php">Back</a>
</header>

	
	<!-- ToolBox -->
	<div id="toolboxColumn" class="s_toolboxContainer">
		<div id="toolBox" class="w3-border w3-border-blue s_toolbox">
		
			<button class="w3-button" onClick="updateCurrentCell(true)">Save</button>
			<br>
			<button class="w3-button w3-red" onClick="noSchool()">No School (f2)</button>	
			<br>
			<button class="w3-button w3-blue" onClick="defaultCell()">Default (f4)</button>	
			<br>
	
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
			<button class="w3-button s_full w3-blue" onClick="toggleLinkBox()">Add Link</button>
			<label class="s_findme_addLink" style="display: none">URL</label>
			<input id="linkURL" class="w3-input w3-border s_findme_addLink" style="display: none">
			<label class="s_findme_addLink" style="display: none">Text</label>
			<input id="linkText" class="w3-input w3-border s_findme_addLink" style="display: none">
			<br class="s_findme_addLink" style="display: none">
			<button id="linkGoButton" class="w3-button s_full s_findme_addLink w3-blue" onClick="addLink()" style="display: none">Go</button>
			<br>
			
			<button id="createNewButton" class="w3-button w3-blue" onClick="createNewOpen()">New School Year</button>
			
				<label class="s_finder" style="display: none">Start Date</label>
				<input id="startDay"  style="display: none" type="date" name="startdate" class="w3-input w3-border s_finder" />
				<br class="s_finder" style="display: none">
				<label class="s_finder" style="display: none">End Date</label>
				<input id="endDay" style="display: none" type="date" name="endDate" class="w3-input w3-border s_finder" />
				<br class="s_finder" style="display: none">
				<button style="display: none" class="w3-button w3-blue w3-center s_finder" onClick="recycleMasterCalendarAjax()">Go</button>
			
		</div>
	</div>
	
	<!-- Calendar -->
	<div id="mainContainer" class="s_container">
		<?php echo getMasterCalendarDisplay(); ?>
	</div>

</body>

<script>
var initialText;
var currentCell;
	
$( document ).ready(function() {
	
	linkHandlers();
	newCalendarHandlers();
	
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

function newCalendarHandlers(){
	//set initial text to test if it changed later
	$(".textarea").on("focusin", function(){
		initialText = $(this).html();
		currentCell = $(this).parent();
	});
	
	//tests if text changed, and updates day if it did
	$(".textarea").on("focusout", function(){
		if($(this).html() != initialText){
			var dayID = $(this).parent().data("dayid");
			var newText = $(this).html();
			updateCurrentCell();
		}
	});
	
	$("body").on("keydown", function(e){
		if (e.keyCode == 113) { //f2 key
			noSchool();
		}
		if (e.keyCode == 115) { //f2 key
			defaultCell();
		}
	});
	
	$(".s_date").on("click", function(){
		initialText = $(this).siblings(".textarea").html();
		currentCell = $(this).parent();
	});
}
	
function createNewOpen(){
	$(".s_finder").toggle();
}

	
function updateCurrentCell(showMessage = false){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		var newText = currentCell.children().eq(1).html();
		updateContentAjax(dayID, newText, showMessage);
	}
}

function changeColor(color){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		var newText = currentCell.children().eq(1).html();
		
		currentCell.removeClass("s_color_0 s_color_1 s_color_2 s_color_3 s_color_4 s_color_5").addClass("s_color_"+color).attr("data-color", color);
		
		updateColorAjax(dayID, color);
	}
}
	
function showSuccess(){
	var tempObj = $("#mainContainer div h2");
	tempObj.attr("class","w3-center w3-green");
	setTimeout(function(){ tempObj.attr("class","w3-center w3-blue"); },700)
	
}
		
function showError(){
	$("#banner").fadeOut(0).delay(2000).fadeIn(0);
	$("#hiddenError").fadeIn(500).delay(1000).fadeOut(500);
}

function noSchool(){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		currentCell.children().eq(1).html("No School");
		
		currentCell.removeClass("s_color_0 s_color_1 s_color_2 s_color_3 s_color_4 s_color_5").addClass("s_color_3").attr("data-color", 3);
		
		updateContentAjax(dayID, "No School");
		updateColorAjax(dayID, 3);
		
	}
}
	
function defaultCell(){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		currentCell.children().eq(1).html("");
		
		currentCell.removeClass("s_color_0 s_color_1 s_color_2 s_color_3 s_color_4 s_color_5").addClass("s_color_0").attr("data-color", 0);
		
		updateContentAjax(dayID, "");
		updateColorAjax(dayID, "0");
		
	}
}
		
//AJAX REQUESTS =================================

function getCalendar(){
	request = $.ajax({
		url: "functions/masterAjax.php", 
		type: "post", 
		data: {
			"type": "getMasterCalendar"
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		$("#mainContainer").html(response);
		newCalendarHandlers();
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		$("#mainContainer").html(response);
    });
}
	
function updateColorAjax(dayID, color, showMessage = false){
	request = $.ajax({
		url: "functions/masterAjax.php", 
		type: "post", 
		data: {
			"type": "updateMasterCalendarColor",
			"dayID": dayID, 
			"color": color
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		console.log("success: "+response);
		if(showMessage){showSuccess();}
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log("error: "+ textStatus+" "+errorThrown+" "+jqXHR);
    });
}
	
function updateContentAjax(dayID, newText, showMessage = false){
	request = $.ajax({
		url: "functions/masterAjax.php", 
		type: "post", 
		data: {
			"type": "updateMasterCalendarContent",
			"dayID": dayID, 
			"content": newText
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
	
function recycleMasterCalendarAjax(){
	var startDay = $("#startDay").val();
	var endDay = $("#endDay").val();
	
	if(startDay == "" || endDay == ""){
		return;
	}
	
	createNewOpen(); //hide display on clicking go
	
	request = $.ajax({
		url: "functions/masterAjax.php", 
		type: "post", 
		data: {
			"type": "recycleMasterCalendar",
			"startDay": startDay, 
			"endDay": endDay
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		console.log(response);
		getCalendar();
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
		//if(showMessage){showError();}
    });
}
	
	
</script>



</html>