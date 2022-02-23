<?php
include 'main.php';
//Brute force protection using Number of Attempts - Needed for obfuscation
//Function defined in main.php, and by giving main.php as false, does not count as a login attempt. Used to check if the user has any login atempts left, or if they have used all 5 attempts for the 10 mins
$login_attempts = loginAttempts($con, FALSE);
//If login attempts = TRUE & User has no attempts left, let user know to try again in 10 mins
if ($login_attempts == 0) {
	exit('Unable to login! Please try again in 10 mins');
}


// Token generated in login.php is checked whether it hasnt been sent OR whether its different to the token in the session
if (!isset($_POST['token']) || $_POST['token'] != $_SESSION['token']) {
	//Token invalid, user must try again
	exit('Incorrect token provided!');
}


// Code for checking Captcha has been completed!
if (isset($_POST['submit']) && $_POST['g-recaptcha-response'] != "") {
	// Secret Key for Captcha
    $secret = '6LcGjIYdAAAAAO4hhuI4VBPYBs3VHts6G3wEs_1z';
	//Verification of Response i.e. has response been completed correctly
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
    $responseData = json_decode($verifyResponse);
    // If successful completion, continue
	if ($responseData->success) {
		;
    } else {
		//Otherwise, exit PHP code and notify user
		exit("Error with Captcha! Please try again!<br><a href='login.php'> Click to return to the login form</a>");
	}
} else {
	//If not completed, notify user
	exit("Please complete Captcha to Login!<a href='login.php'> Click to return to the login form</a>");

}

// Protection against SQL injections & Cross-site scripting
$usernameentered = $usernameentered = mysqli_real_escape_string($con, $_POST['username']);
$usernameentered = htmlspecialchars($usernameentered);
$passwordentered = $passwordentered = mysqli_real_escape_string($con, $_POST['password']);
$passwordentered = htmlspecialchars($passwordentered);
// No empty checks needed due to 'required' property in Input Tags


// Code to prepare our SQL statement, preparing the SQL statement will further help with the prevention of SQL injection.
//Gets the user ID, password, activation code, role, ip & email of the user from the inputted username
$stmt = $con->prepare('SELECT id, password, activation_code, role, ip, email FROM accounts WHERE username = ?');
// Bind username entered to parameter username 
$stmt->bind_param('s', $usernameentered);
$stmt->execute();
// Store the result so we can check if the account exists in the database.
$stmt->store_result();
// Check if the account exists:
if ($stmt->num_rows > 0) {
	//Bind the data retrieeved to variables.
	$stmt->bind_result($id, $password, $activation_code, $role, $ip, $email);
	$stmt->fetch();
	$stmt->close();
	// Account exists, now we verify the password. To do this, use function password_verify()
	//Does not required $password from data to be unhashed
	if (password_verify($passwordentered, $password)) {
		// Check if the account is activated, i.e. does not have an activation code stored in database
		if ($activation_code != 'activated') {
			// If user hasn't activated their account, output this msg which gives a link to resend account activation
			echo 'Please activate your account to login, <a href="resendactivation.php">Click here</a> to resend the activation email!';
		} else if ($_SERVER['REMOTE_ADDR'] != $ip) {
			// 2FA required if IP stored in database is not the same as current IP. The IP stored in the database is the IP of the session when user registered
			//uniqid creates a unique code for 2FA
			$_SESSION['2FA'] = uniqid();
			//User id, email & 2FA code stored in URL. Used so only verified users can access this link
			$link = 'twofactor.php?id=' . $id . '&email=' . $email . '&code=' . $_SESSION['2FA'];
			//Redirect to this page
			header("location: $link");
		} else {
			// User login was successful 
			// Created a sessions to know the user that is logged in.
			//This function Updates the current session id with a newly generated one
			session_regenerate_id();
			//Loggedin set as TRUE, username, id & role all stored in the session
			$_SESSION['loggedin'] = TRUE;
			$_SESSION['name'] = $usernameentered;
			$_SESSION['id'] = $id;
			$_SESSION['role'] = $role;
			//if user is a member, redirected to Request Evaluation Page
			if ($role == 'Member') {
				header("Refresh:1; url=requestEval.php");
			}
			else {
				//Otherwise, if user is an Admin, redirect to view Evaluations Admin Page
				header("Refresh:1; url=viewRequests.php");
			}
		}
	} else {
		// Incorrect Password. Users number of attempts decreased by 1
		$login_attempts = loginAttempts($con, TRUE);
		echo 'Incorrect Username/Password, you have ' . $login_attempts . ' attempts remaining!';
		//Redirected to Login page
		header("Refresh:2; url=login.php");
	}
} else {
	// Incorrect Username, Users number of attempts decreased by 1
	$login_attempts = loginAttempts($con, TRUE);
	echo 'Incorrect Username/Password, you have ' . $login_attempts . ' attempts remaining!';
	header("Refresh:2; url=login.php");
}
?>
