<?php
include 'main.php';
$msg = '';
// First we check if the email and code exists, these variables will appear as parameters in the URL sent to the user!
//GET method is safe to use, as getting code & email from the URL, not being entered by users into a field. Still sanitizing incase user knows this.
$email = mysqli_real_escape_string($con, $_GET['email']);
$code = mysqli_real_escape_string($con, $_GET['code']);

if (isset($_GET['email'], $_GET['code']) && !empty($_GET['code'])) {
	//Prepare Statement 1. Protects Against SQL Injection & 2. Is used to all stored data from the user where the code & email in URL match.
	$stmt = $con->prepare('SELECT * FROM accounts WHERE email = ? AND activation_code = ?');
	// Binding the ? marks to the email & code retrieved from URL.
	$stmt->bind_param('ss', $email, $code);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		$stmt->close();
		// Account exists with the requested email and code.
		$stmt = $con->prepare('UPDATE accounts SET activation_code = ? WHERE email = ? AND activation_code = ?');
		// Set the new activation code to 'activated', this is how we can check if the user has activated their account already.
		$activated = 'activated';
		//Updates the activation code to the text 'activated'.
		$stmt->bind_param('sss', $activated, $email, $code);
		$stmt->execute();
		$stmt->close();
		//Displayed on page activate.php
		$msg = 'Your account is now activated, you can now <br><a href="login.php">Login here</a> or be redirected in a moment!';
		//Redirects user to login page if activation successful
		header("Refresh:4; url=login.php");
	} else {
		//User is most likely already activated, i.e. this link is an old link sent to the user. Gives option to login or resend activation email!
		$msg = 'The account is already activated or this link has expired! To login click <a href="login.php">here</a><br> Or to activate your account, <a href="resendactivation.php">click here</a> to resend the activation email!';
	}
} else {
	//Code & Email not found in URL. This may mean user has just typed url into browser. Redircted to login page.
	$msg = 'Activation code & email not found! <a href="login.php">Login here</a> or wait to be redirected';
	header("Refresh:3; url=login.php");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Lovejoy Antiques - Account Activation Page</title>
		<link href="style1.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div>
		<!-- displays error/success message! --> 
			<p><?=$msg?></p>
		</div>
	</body>
</html>
