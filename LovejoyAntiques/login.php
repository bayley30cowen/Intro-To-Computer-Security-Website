<?php
include 'main.php';
// No need for the user to see the login form if they're logged-in so redirect them to the home page
if (isset($_SESSION['loggedin'])) {
	// If the user is logged in redirect to the Request Evaluation page
	header('Location: requestEval.php');
	exit;
}
// CSRF Protection
// When the user logs in, each & every login will require a 'token' that will be checked using Sessions in PHP
$_SESSION['token'] = md5(uniqid(rand(), true));
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- HTML for Login Page -->
		<meta charset="UTF-8">
		<title>Lovejoy Antiques Login Form</title>
		<!-- CSS used for stlying form to be usable and easy to traverse -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="style1.css">
		<!-- Source of Google ReCaptcha -->
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>

	</head>
	
	<body>
		<div class="container">
			<div class="row">
				<div class = "col-md-12">
					<h2><strong>LoveJoy Antique Evaluations: </strong><br>Login Screen</h2><br>
					<!-- On submission, logincheck.php is called. This checks the inputs of the user & if credential match a user in db, user is logged in -->
					<form action="logincheck.php" method="post">
						<div class="form-group">
							<label for="username"></label>
						</div>
						<div class="form-group">
							<!-- Text field for Username. Required tag ensures not empty -->
							<input type="text" name="username" placeholder="Username" class="form-control" required>
							<label for="password"></label>
						</div>
						<div class="form-group">
							<!-- Password text field is also required. The type password 'hides' the input from the user-->
							<input type="password" name="password" placeholder="Password" class="form-control" required>
						</div>
						<div class="form-group">
							<!-- Link to forgot password page -->
							<a href="forgotpassword.php">Forgot Password?</a>
						</div>
						<div class="form-group">
							<!-- Google ReCaptcha on site. Used to stop botnet attacks required for Obfuscation -->
						<div class="g-recaptcha" data-sitekey="6LcGjIYdAAAAANXPT-bad-hcaw464I12s4ty9fIY"></div>
						</div>
						<!-- Input Type hidden allows for data that cannot be seen or modified by users when a form is submitted. Stores the unique token as the value to be passed to the logincheck.php Used in CSRF Protection -->
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<div class="form-group">
							<!-- Submit button -->
							<input type="submit" name="submit" class="btn btn-primary" value="Login">
						</div>
						<!-- Link to registration form -->
						<p>Don't have an account? <a href="index.html">Register here</a>.</p>
					</form>
				</div>
			</div>
		</div>
		<script>
		document.querySelector(".login form").onsubmit = function(event) {
			event.preventDefault();
			var form_data = new FormData(document.querySelector(".login form"));
			var xhr = new XMLHttpRequest();
			xhr.open("POST", document.querySelector(".login form").action, true);
			xhr.onload = function () {
				if (this.responseText.toLowerCase().indexOf("success") !== -1) {
					window.location.href = "requestEval.php";
				} else if (this.responseText.indexOf("2FA") !== -1) {
    				window.location.href = this.responseText.replace("2FA: ", "");
				} else {
					document.querySelector(".msg").innerHTML = this.responseText;
				}
			};
			xhr.send(form_data);
		};
		</script>
	</body>
</html>
