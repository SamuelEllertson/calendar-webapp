<?php 
	require_once(dirname(__FILE__) . '/core/securePage.php');
	require_once(dirname(__FILE__) . '/core/header.php');
	require_once(dirname(__FILE__) . '/functions/tableFunctions.php');
	session_start();

	$userID = $_SESSION["userID"];
	$username = $_SESSION["username"];
	$name = $_SESSION["name"];
?>
<style>
	.s_fullWidth{
		width: 100%;
	}
</style>
<title>Manage Calendars</title>

<body>

<header class="w3-container w3-margin-bottom w3-card-4 w3-blue s_banner" style="display: block">
	<h2  style="display: inline-block">YHS Calendar > Manage</h2>
	<a class="w3-right w3-button w3-margin" href="userMain.php">Back</a>
</header>

<div class="w3-row-padding">
	
	<!-- MAIN PANNEL -->
	<div id="mainBox" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue">
		<div class="w3-center w3-padding"><button id="btn_add_calendar" class="w3-button s_fullWidth" onClick="showBox_addCalendar(this)">Add Calendar</button></div>
		
		<div class="w3-center w3-padding"><button id="btn_remove_calendar" class="w3-button s_fullWidth" onClick="showBox_removeCalendar(this)">Remove Calendar</button></div>
		
		<div class="w3-center w3-padding"><button id="btn_add_calendar" class="w3-button s_fullWidth" onClick="showBox_setDefaultCalendar(this)">Set Default Calendar</button></div>
		
		<div class="w3-center w3-padding"><button id="btn_add_calendar" class="w3-button s_fullWidth" onClick="showBox_renameCalendar(this)">Rename Calendar</button></div>
		
		<div class="w3-center w3-padding"><button id="btn_transfer_content" class="w3-button s_fullWidth" onClick="showBox_transferContent(this)">Transfer Tool</button></div>
	</div>
	
	<!-- HIDDEN BY DEFAULT: ADD CALENDAR PANNEL -->
	<div id="addCalendarBox" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>Calendar Name</h3></div>
		<form action="functions/userAjax.php" method="post">
			<input name="type" type="hidden" value="addCalendar">
			<div class="w3-center w3-padding"><input id="addCalendarNameInput" name="calendarName" class="w3-input w3-border" autocomplete="off"></div>
			<div class="w3-center w3-padding"><input class="w3-button w3-blue s_fullWidth" type="submit" value="Create"></div>
		</form>
	</div>
	
	<!-- HIDDEN BY DEFAULT: REMOVE CALENDAR PANNEL -->
	<div id="removeCalendarBox" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>Remove Calendar</h3></div>
		
		<div id="removeCalendarButtonDiv" style="display:flex;flex-direction:column"><?php echo getRemoveCalendarButtons($userID); ?></div>
		
	</div>
	
	<!-- HIDDEN BY DEFAULT: SET DEFAULT CALENDAR PANNEL -->
	<div id="setDefaultCalendarBox" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>Set Default Calendar</h3></div>
		
		<div id="setDefaultCalendarBoxDiv" style="display:flex;flex-direction:column"><?php echo getDefaultCalendarButtons($userID); ?></div>
		
	</div>
	
	<!-- HIDDEN BY DEFAULT: RENAME CALENDAR PANNEL 1 -->
	<div id="renameCalendarBox1" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>Select</h3></div>
		
		<div id="renameCalendarBoxButtonDiv" style="display:flex;flex-direction:column"><?php echo getCalendarButtons($userID); ?></div>
		
	</div>
	
	<!-- HIDDEN BY DEFAULT: RENAME CALENDAR  PANNEL 2 -->
	<div id="renameCalendarBox2" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>Rename</h3></div>
		
		<div class="w3-center w3-padding"><input id="renameCalendarName" class="w3-input w3-border"></div>
		<div class="w3-center w3-padding"><button onclick="renameCalendar()" class="w3-button w3-blue s_fullWidth">Rename</div>
		
	</div>
		
	<!-- HIDDEN BY DEFAULT: TRANSFER CONTENT PANNEL 1 -->
	<div id="transferContentBox1" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>From</h3></div>
		
		<div id="transferContentButtonDiv1" style="display:flex;flex-direction:column"><?php echo getCalendarButtons($userID); ?></div>
		
	</div>
	
	<!-- HIDDEN BY DEFAULT: TRANSFER CONTENT PANNEL 2 -->
	<div id="transferContentBox2" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding"><h3>To</h3></div>
		
		<div id="transferContentButtonDiv2" style="display:flex;flex-direction:column"><?php echo getCalendarButtons($userID); ?></div>
		
	</div>
	
	<!-- HIDDEN BY DEFAULT: TRANSFER CONTENT PANNEL 3 -->
	<div id="transferContentBox3" class="w3-col s3 m3 l3 w3-container w3-border w3-border-blue" style="display: none">
		<div class="w3-center w3-padding">
			<button id="transferContentStartButton" class="w3-button w3-blue s_fullWidth" onClick="transferBegin()">Manual Start</button>
		</div>
	</div>
	
</div>

<form id="transferContentForm" action="userTransfer.php" method="get">
	<input id="tciFrom" type="hidden" name="fromID" value="">
	<input id="tciTo" type="hidden" name="toID" value="">
</form>

<script>
var fromID;
var toID;
var fromButtons;
var toButtons;
var transferContentForm;
var saidOkToDelete = false;
var rename_calendarID;
	
$( document ).ready(function() {
 	
	buttonHandlers();
	
	transferContentForm = $("#transferContentForm");
});	
	
function buttonHandlers(){
	//get from and to buttons
	fromButtons = $("#transferContentBox1 div:eq(1)").children(":nth-child(odd)");
	toButtons = $("#transferContentBox2 div:eq(1)").children(":nth-child(odd)");

	//reset everything
	$(".s_findme_removeButton").off();
	$("#setDefaultCalendarBoxDiv").children().off();
	$("#renameCalendarBoxButtonDiv").children().off();
	fromButtons.off();
	toButtons.off();
	
	
	//then add in the event handlers
	$(".s_findme_removeButton").click(function(e){
		e.preventDefault();
		if(!saidOkToDelete){
			if (window.confirm("Are you sure you want to delete this?")) {
				var calendarID = $(this).siblings().eq(1).val();
				removeCalendarAjax(calendarID);
				$(this).parent().hide();
				saidOkToDelete = true;
			}
		} else{
			var calendarID = $(this).siblings().eq(1).val();
			removeCalendarAjax(calendarID);
			$(this).parent().hide();
			saidOkToDelete = true;
		}
		
	});
	
	$("#setDefaultCalendarBoxDiv").children().click(function(e){
		$(this).siblings().removeClass("w3-blue");
		$(this).addClass("w3-blue");
		var calendarid = $(this).data("calendarid");
		updateUserSetting("defaultCalendar", calendarid);
	}); 
	
	$("#renameCalendarBoxButtonDiv").children().click(function(e){
		$(this).siblings().removeClass("w3-blue");
		$(this).addClass("w3-blue");
		rename_calendarID = $(this).data("calendarid");
		$("#renameCalendarName").focus();
	});
	
	fromButtons.on("click", function(){
		fromButtons.each(function(){
			$(this).removeClass("w3-blue");
		});
		
		fromID = $(this).addClass("w3-blue").data("calendarid");
		if(toID != undefined){
			$("#transferContentStartButton").focus();
		}
	});
	
	toButtons.on("click", function(){
		toButtons.each(function(){
			$(this).removeClass("w3-blue");
		});
		
		toID = $(this).addClass("w3-blue").data("calendarid");
		if(fromID != undefined){
			$("#transferContentStartButton").focus();
		}
	});
}
	
function clearAll(){
	$("#addCalendarBox").hide();
	
	$("#removeCalendarBox").hide();
	saidOkToDelete = false;
	
	$("#setDefaultCalendarBox").hide();
	
	$("#renameCalendarBox1").hide();
	$("#renameCalendarBox2").hide();
	
	$("#transferContentBox1").hide();
	$("#transferContentBox2").hide();
	$("#transferContentBox3").hide(); 
	
	$("#mainBox button").each(function(){
		$(this).removeClass("w3-blue");
	});
}

function showBox_addCalendar(me){
	clearAll();
	$("#addCalendarBox").show();
	$(me).addClass("w3-blue");
	$("#addCalendarNameInput").focus();
}
	
function showBox_removeCalendar(me){
	clearAll();
	$("#removeCalendarBox").show();
	$(me).addClass("w3-blue");
}
	
function showBox_setDefaultCalendar(me){
	clearAll();
	$("#setDefaultCalendarBox").show();
	$(me).addClass("w3-blue");
}
	
function showBox_renameCalendar(me){
	clearAll();
	$("#renameCalendarBox1").show();
	$("#renameCalendarBox2").show();
	$(me).addClass("w3-blue");
}
	
function showBox_transferContent(me){
	clearAll();
	$("#transferContentBox1").show();
	$("#transferContentBox2").show();
	$("#transferContentBox3").show();
	$(me).addClass("w3-blue");
}
	
function transferBegin(){
	if(fromID == undefined || toID == undefined){
		alert("please select which calendars to use");
		return;
	}
	
	$("#tciFrom").val(fromID);
	$("#tciTo").val(toID);
	
	transferContentForm.submit();
}
	
function renameCalendar(){
	if(rename_calendarID == undefined){
		alert("Please select a calendar");
		return;
	}
	if($("#renameCalendarName").val() == ""){
		alert("Please enter a new name");
		return;
	}
	var name = $("#renameCalendarName").val();
	renameCalendarAjax(rename_calendarID, name);
	$("#renameCalendarName").val("");
}

function replaceButtons(){
	getButtons("removeCalendarButtons", function(response){
			$("#removeCalendarButtonDiv").html(response);
			buttonHandlers();
		});
		
		getButtons("defaultCalendarButtons", function(response){
			$("#setDefaultCalendarBoxDiv").html(response);
			buttonHandlers();
		});
		
		getButtons("calendarButtons", function(response){
			$("#renameCalendarBoxButtonDiv").html(response);
			$("#transferContentButtonDiv1").html(response);
			$("#transferContentButtonDiv2").html(response);
			buttonHandlers();
		});
}
	
//AJAX +++++++++++++++++++++++++++++++++++++++
function removeCalendarAjax(calendarID){
	
	request = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "removeCalendar",
			"calendarID": calendarID
			
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		console.log("success: "+response);
		replaceButtons();
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log("error: "+ textStatus+" "+errorThrown+" "+jqXHR);
    });
}
	
function renameCalendarAjax(calendarID, name){
	
	request = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "renameCalendar",
			"calendarID": calendarID,
			"name": name
		}
	});
	
	request.done(function (response, textStatus, jqXHR){
		console.log("success: "+response);
		
		//replace old buttonsw with new buttons
		replaceButtons();
		
		
    });
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.log("error: "+ textStatus+" "+errorThrown+" "+jqXHR);
    });
}
	
function updateUserSetting(setting, value){
	
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
	
function getButtons(buttonType, func = undefined){
	
	request1 = $.ajax({
		url: "functions/userAjax.php", 
		type: "post", 
		data: {
			"type": "getButtons",
			"buttonType": buttonType
		}
	});
	
	request1.done(function (response, textStatus, jqXHR){
		console.log("setting successfully changed");
		if(func != undefined){
			func(response);
		}
    });
	request1.fail(function (jqXHR, textStatus, errorThrown){
		console.log(textStatus);
    });
	
}


</script>
</body>
</html>