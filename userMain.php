<?php 
	require_once(dirname(__FILE__) . '/core/securePage.php');
	require_once(dirname(__FILE__) . '/core/header.php');
	require_once(dirname(__FILE__) . '/functions/tableFunctions.php');
	session_start();

	$userID = $_SESSION["userID"];
	$username = $_SESSION["username"];
	$name = $_SESSION["name"];
?>
<script src='https://www.google.com/recaptcha/api.js'></script>
<title>View Calendars</title>


<body>
<header class="w3-container w3-margin-bottom w3-card-4 w3-blue s_banner" style="display: block">
	<h2  style="display: inline-block">Yorkville High School Teacher Calendar</h2>
	<a class="w3-right w3-button w3-margin" href="core/logout.php">Logout</a>
</header>

	
	<!-- ToolBox -->
	<div id="toolboxColumn" class="s_toolboxContainer">
		<div id="toolBox" class="w3-border w3-border-blue s_toolbox">
		
			<button class="w3-button s_fullWidth" onClick="updateCurrentCell(true)">Save</button>
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
			
			<!-- add link button -->
			<br>
			<button class="w3-button" onClick="toggleLinkBox()">Add Link</button>
			<label class="s_findme_addLink" style="display: none">URL</label>
			<input id="linkURL" class="w3-input w3-border s_findme_addLink" style="display: none">
			<label class="s_findme_addLink" style="display: none">Text</label>
			<input id="linkText" class="w3-input w3-border s_findme_addLink" style="display: none">
			<br class="s_findme_addLink" style="display: none">
			<button id="linkGoButton" class="w3-button w3-blue s_findme_addLink" onClick="addLink()" style="display: none">Go</button>
				

		</div>
	</div>
	
	<!-- Calendar -->
	<div id="mainContainer" class="s_container">
		<?php echo getUserDefaultCalendar($userID); ?>
	</div>
	
	<!-- Calendar List-->
	<div id="calendarListColumn" class="s_calendarList">
		<div id="calendarList" class="w3-border w3-border-blue s_calendarListBox">
			<!-- Calendar Label -->
			<h3 class="w3-center">Calendars</h3>
			
			<!-- Calendar Buttons -->
			<div id="calendarListButtons" class="w3-center w3-margin">
				<?php echo getDefaultCalendarButtons($userID); ?>
			</div>

			<br>
			
			<!-- manage button-->
			<div class="w3-center w3-margin">
				<a href="userManage.php" class="w3-button w3-blue s_fullWidth">Manage</a>
			</div>
			
			<?php 
			//if admin, add in admin tools button
			if($_SESSION["admin"]){
				echo '<div class="w3-center w3-margin">
				<a href="adminMain.php" class="w3-button w3-blue s_fullWidth">Admin Tools</a>
				</div>';
			}
			?>
			
			
		</div>
	</div>


</body>

<script>
var buttons;
var initialText;
var currentCell;
	
$( document ).ready(function() {
    buttons = $("#calendarListButtons").children(":nth-child(odd)");
	
	buttons.on("click", function(){
		getCalendar($(this).data("calendarid"));
		updateCalendarButtonColors(this);
	});
	
	
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
			var color = $(this).parent().data("color");
			updateDay(dayID, newText, color);
		}
	});
	
	$(".s_date").on("click", function(){
		initialText = $(this).siblings(".textarea").html();
		currentCell = $(this).parent();
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
	
function updateCurrentCellSync(showMessage = false){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		var newText = currentCell.children().eq(1).html();
		var color = currentCell.data("color");
		updateDaySync(dayID, newText, color, showMessage);
	}
}

function changeColor(color){
	if(currentCell != null){
		var dayID = currentCell.data("dayid");
		var newText = currentCell.children().eq(1).html();
		
		currentCell.removeClass("s_color_0 s_color_1 s_color_2 s_color_3 s_color_4 s_color_5").addClass("s_color_"+color).attr("data-color", color);
		
		updateDay(dayID, newText, color);
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
	
function updateCalendarButtonColors(current){
	$("#calendarListButtons button").removeClass("w3-blue");
	if(current != undefined){
		$(current).addClass("w3-blue");
	}
}
	
//AJAX REQUESTS =================================
function getCalendar(calendarID){
	request = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "getCalendarDisplay",
			"calendarID": calendarID
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
		if(showMessage){showError();}
    });
}
	
function updateDaySync(dayID, newText, color, showMessage = false){
	request = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		async : false,
		data: {
			"type": "updateDay",
			"dayID": dayID, 
			"newText": newText,
			"color": color
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		console.log("day successfully updated");
		if(showMessage){showSuccess();}
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
		if(showMessage){showError();}
    });
}
	
	
</script>











</html>