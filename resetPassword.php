<?php 
session_start();
require_once('core/header.php');
?>
<script src='https://www.google.com/recaptcha/api.js'></script>
<title>Forgot your password</title>

<body>

<header class="w3-container w3-margin-bottom w3-card-4 w3-blue">
	<h2 style="display: inline-block">Yorkville High School Student Notes</h2>
	<a class="w3-right w3-button w3-margin" href="core/logout.php">Back</a>
</header>

<div class="w3-row-padding">
	
	<div class="w3-half">
	
		<div class="w3-container w3-blue">
			<h2>Reclaim Account</h2>
		</div>
		
		<form id='login' class="w3-container w3-card-4" action='core/reclaimAccount.php' method='post' accept-charset='UTF-8'>	
		<input type="hidden" name="type" value="resetPassword" />
		
		<?PHP 
				 if($_SESSION["ResetLockedOut"]){echo "<p>Too many failed attempts. Locked for 20 minutes.</p>";}
			else if($_SESSION["ResetDelay"]){echo "<p>Please wait at least 5 seconds between attempts</p>";}
			else if($_SESSION["badTry"]){echo "<p>Username or Reset Code Incorrect</p>";}
		
		?>
		
		<p>
			<label class="w3-text-grey">Enter Username</label>
			<input  class="w3-input w3-border" name="Username" type="text" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" required>
		</p>
		
		<p>
			<label class="w3-text-grey">Enter Reset Code (check email for code)</label>
			<input  class="w3-input w3-border" name="Code" type="text" autocomplete="new-password" required>
		</p>	
		
		<p>
			<label class="w3-text-grey">Enter New Password</label>
			<input  class="w3-input w3-border" name="Password" type="password" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" required>
		</p>
		
		<input class="w3-button w3-blue" name="Submit" type="submit" value="Submit" />
		<br>
		<br>

	</div>
</div>


</body>
</html>