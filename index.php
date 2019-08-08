<?php 
	session_start();
	require_once(dirname(__FILE__) . '/core/header.php');
	
?>
<script src='https://www.google.com/recaptcha/api.js'></script>
<title>Login or Register</title>

<body>

<header class="w3-container w3-margin-bottom w3-card-4 w3-blue">
	<h2>Yorkville High School Teacher Calendar</h2>
</header>


<div class="w3-row-padding">
	
	<div class="w3-half">
	
		<div class="w3-container w3-blue">
			<h2>Login</h2>
		</div>

		<form id='login' class="w3-container w3-card-4" action='core/checkLogin.php' method='post' accept-charset='UTF-8'>	
					
			<?php 
			if($_SESSION["noNameOrPass"] == true)
				{echo "Please enter a valid Username and Password";} 
			elseif(isset($_SESSION["remainingTime"]))
				{echo "Please wait ".$_SESSION["remainingTime"]." Seconds before trying again.";}
			elseif($_SESSION["badLogin"] == true)
				{echo "Username or password is incorrect";}
			?>
			
			<p>
				<label class="w3-text-grey">Username</label>
				<input  class="w3-input w3-border" name="username" type="text" maxlength="30" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
			</p>	
					
			<p>
				<label class="w3-text-grey">Password</label>
				<input class="w3-input w3-border" name="password" type="password" maxlength="30" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" required>
			</p>

			<div id="captcha" class="center">
				<?PHP if($_SESSION["captcha"]){echo "<div class='g-recaptcha' data-sitekey='6LdYQhcUAAAAACaR4JxdCzUSLaxlQKxB4ky4wtZk'></div>";} ?>
			</div>   
			
			<input class="w3-button w3-blue" name="submit" type="submit" value="Login" />
			
			<a href="forgotPassword.php" class="w3-right">Forgot Password</a>	
			<br>
			<br>

		</form>
	</div>


	<div class="w3-half">
		
		<div class="w3-container w3-blue">
			<h2>Register</h2>
		</div>
		
		<form id='register' class="w3-container w3-card-4" action='core/register.php' method='post' accept-charset='UTF-8'>	

			<?php 
			if($_SESSION["noRegister"] == true)
				{echo "Registration is turned off";}
			elseif($_SESSION["registerIncomplete"] == true)
				{echo "Please fill out all fields";}
			elseif($_SESSION["nameTaken"] == true)
				{echo "That username is Taken";}
			elseif($_SESSION["badEmail"] == true)
				{echo "Must use a y115 teacher/administrator email";}
			elseif($_SESSION["isRegistered"] == true)
				{echo "Registered Successfully";} 	
			?>
		
			<p>
				<label class="w3-text-grey">Username</label>
				<input class="w3-input w3-border" name="username" type="text" maxlength="30" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" required>
			</p>
				
			<p>
				<label class="w3-text-grey">Password</label>
				<input class="w3-input w3-border" name="password" type="password" maxlength="30" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" required>
			</p>

			<p>
				<label class="w3-text-grey">Name</label>
				<input class="w3-input w3-border" name="name" type="text" maxlength="30" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" required>
			</p>
						
			<p>
				<label class="w3-text-grey">Email</label>
				<input class="w3-input w3-border" name="email" type="text" maxlength="40" placeholder="example@y115.org" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" required>
			</p>	

			<input class="w3-button w3-blue" name="submit" type="submit" value="Register" />	
			<br>
			<br>

		</form>
		
	</div>

</div>

<div class="bottom right">
	<p class="w3-margin">
		Created by Samuel Ellertson, 2017
	</p>
</div>

</body>
</html>