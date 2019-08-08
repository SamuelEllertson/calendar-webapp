<?php 
session_start();
require_once(dirname(__FILE__) . '/core/header.php');
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
		<input type="hidden" name="type" value="sendEmail" />
		
		<?PHP if($_SESSION["emailNotFound"]){echo "<p>Email Not Found</p>";} ?>
		
		<p>
			<label class="w3-text-grey">Enter the Email associated with your account</label>
			<input  class="w3-input w3-border" name="Email" type="text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
		</p>	
		
		<input class="w3-button w3-blue" name="Submit" type="submit" value="Submit" />
		<br>
		<br>

	</div>
</div>


</body>
</html>