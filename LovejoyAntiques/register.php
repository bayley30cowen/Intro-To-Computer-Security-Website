<?php
// Main.php includes db connection
include 'main.php';

if (mysqli_connect_errno()) {
	// Checks for server error
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}


// Sanitizing user inputs - Protection against SQL Injection attacks by escaping special chars which include " & '
$username = mysqli_real_escape_string($con, $_POST['username']);
$fullname = mysqli_real_escape_string($con, $_POST['fullname']);
$email = mysqli_real_escape_string($con, $_POST['email']);
$telephone = mysqli_real_escape_string($con, $_POST['telephone']);
$password = mysqli_real_escape_string($con, $_POST['password']);
$confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);
$securityQuestion = mysqli_real_escape_string($con, $_POST['securityQuestion']);
$securityAnswer = mysqli_real_escape_string($con, $_POST['securityAnswer']);
// Further sanitizing - Prevention of XSS by changing special chars like <> to html entities
$username = htmlspecialchars($username);
$fullname = htmlspecialchars($fullname);
$email = htmlspecialchars($email);
$telephone = htmlspecialchars($telephone);
$password = htmlspecialchars($password);
$confirm_password = htmlspecialchars($confirm_password);
$securityQuestion = htmlspecialchars($securityQuestion);
$securityAnswer = htmlspecialchars($securityAnswer);
// Checks for invalid email, empty fields done through HTML. E.g. using type='email' in the input tag & using 'required' in input tag
// Username & fullname have also been checked using HTML 'pattern' tag & must contain only characters and numbers, and letters, spaces & hypens (for double barrel names) respectively.


//Password Strength Check. Ensures atleast 1 uppercase, lowercase, number & special char is present, along wiht a min length
$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number    = preg_match('@[0-9]@', $password);
$specialChars = preg_match('@[^\w]@', $password);
if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 10) {
	exit("Password should be at least 10 characters in length and should include at least one upper case letter, one number, and one special character. <a href='index.html'>Click to return to the registration form</a>");
}
// Ensuring no data entered is above the database maximum capacity. Telephone does not need to be checked as HTML pattern attribute within input tag is used to ensure 11 digits entered,
if(strlen($username) > 49 || strlen($fullname) > 99 || strlen($email) > 99 || strlen($password) > 100 || strlen($securityQuestion) > 99)  {
	exit("One of the inputs you have entered has has exceeded the maximum charcter length. Please revise this! <a href='index.html'>Click to return to the registration form</a>");
}
// Check pwds match
if ($confirm_password != $password) {
	exit("Passwords do not match!<a href='index.html'> Click to return to the registration form</a>");
}
// Check to see if Password is based on username. This checks for palindrome & if user replaces letters like s,e,o & l with number/other charcters alike.
$lc_password = strtolower($password);
$lc_username = strtolower($username);
$denum_pass = strtr($lc_password,'5301!','seoll');
if (($lc_password == $lc_username) || ($lc_password == strrev($lc_username)) || ($denum_pass == $lc_username) || ($denum_pass == strrev($lc_username))) { 
	exit("Password cannot be based on the username!<a href='index.html'> Click to return to the registration form</a>"); 
}


//Firstly Ensure no account has the same username & email since these must be unique in database design
//Prepared statements protect users against SQL injection attacks since the query and the data are sent to the database server separately.
$stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ? OR email = ?');
// Binding parameters protects against SQl injection attacks
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();
// Result is stored to check if the account exists in the database.
if ($stmt->num_rows > 0) {
	// If username and/or email already exist - exit
	echo "Username and/or email already exists! <a href='index.html'>Click to return to the registration form</a>";
} else {
	$stmt->close();
	// If Username doesnt exists, insert new account into db
	$stmt = $con->prepare('INSERT INTO accounts (username, fullname, password, email, telephone ,activation_code, securityQuestion, securityAnswer, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
	// Hashing the password and use password_verify when a user attempts to logs in.
	$passwordenc = password_hash($password, PASSWORD_DEFAULT);
	//Creates unique activation code
	$activate = uniqid();
	//Gets IP
	$ip = $_SERVER['REMOTE_ADDR'];
	//Storing all user inputs, with password hashed, IP for CSRF check/2FA check & activation code
	$stmt->bind_param('sssssssss', $username, $fullname ,$passwordenc, $email, $telephone , $activate, $securityQuestion, $securityAnswer, $ip);
	$stmt->execute();
	$stmt->close();
	// User is sent the activation email.
	$subject = 'Account Activation Required';
	$activate_link = 'http://users.sussex.ac.uk/~bcc28/G6077/LovejoyAntiques/activate.php?email=' . $email . '&code=' . $activate;
	$message = '<p>Please click the following link to activate your account!: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';

	//Using Email Template for nice formatting
	$email_template = str_replace('%link%', $message, file_get_contents('activation.html'));
	//Adding subject & body to email
	if (sendEmail($email, $email_template, $subject)) {
			echo 'Message has been sent. Please check your email (and Junk Mail)';
	} else {
			echo 'Message cannot be sent. Mail Error Occured';
			header("Refresh:5; url=login.php");
	}
}
?>
