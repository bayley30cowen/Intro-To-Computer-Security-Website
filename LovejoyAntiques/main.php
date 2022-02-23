<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// This file contains the database connection, initializing of sessions
include_once 'config.php';
// Start the session
session_start();
// Establish a connection to the database
$con = mysqli_connect(db_host, db_username, db_password, db_name);
if (mysqli_connect_errno()) {
	exit('Connection to Database Failed: ' . mysqli_connect_error());
}
// Update the charset
mysqli_set_charset($con, db_charset);

// The below function will check if the user is logged-in. If they are not, redircted back to login page. Stops users typing in URL of a page without logging in. For example used on Request Evaluation page & View Evaluations Page
function check_loggedin($con, $redirect_file = 'login.php') {
    if (!isset($_SESSION['loggedin'])) {
    	// If the user is not logged in redirect to the login page.
    	header('Location: ' . $redirect_file);
    	exit;
    }
}
//Brute force protection using login attempts. Users are given 5 attempts to login
function loginAttempts($con, $update = TRUE) {
	//Gets IP and date, which if failed attenpt, is saved in DataBase
	$ip = $_SERVER['REMOTE_ADDR'];
	$now = date('Y-m-d H:i:s');
	if ($update) {
		//SQL Injection protected by using Prepared Statements
		//INSERTS in db login_attempts the ip-address and date/time of the failed login
		//If IP of the failed attempt is a duplicate in the table, i.e. user has failed now more than once on same device, that row is updated instead with the attempts-1
		$stmt = $con->prepare('INSERT INTO login_attempts (ip_address, `date`) VALUES (?,?) ON DUPLICATE KEY UPDATE attempts_left = attempts_left - 1, `date` = VALUES(`date`)');
		$stmt->bind_param('ss', $ip, $now);
		$stmt->execute();
		$stmt->close();
	}
	//Finds all failed attempts for a specific IP
	$stmt = $con->prepare('SELECT * FROM login_attempts WHERE ip_address = ?');
	$stmt->bind_param('s', $ip);
	$stmt->execute();
	$stmt->store_result();
	// Check there is a row:
	if ($stmt->num_rows > 0) {
		//Bind the data retrieeved to variables.
		$stmt->bind_result($id, $ips, $attempts, $date);
		$stmt->fetch();
		$stmt->close();
		//If the user has run out of attempts
		// The user can try to again in 10 mins
		$expire = date('Y-m-d H:i:s', strtotime('+10 minutes', strtotime($date)));
		if ($now > $expire) {
			//If user has expiray time.
			//Delete the record from the table.
			$stmt = $con->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
			$stmt->bind_param('s', $ip);
			$stmt->execute();
			$stmt->close();
			$attempts = 5;
			return $attempts;
		}
		else {
			return $attempts;
		}
	}
	return 5;
}
function sendEmail($email, $email_template, $subject) {
	require 'PHPMailer.php';
	require 'Exception.php';
	require 'SMTP.php';
	require 'credential.php';

	//PHPMailer setup
	$mail = new PHPMailer(true);
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = EMAIL;                 // SMTP username
	$mail->Password = PASS;                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;                                    // TCP port to connect to

	$mail->setFrom(EMAIL, 'Lovejoy Antique Evaluations'); //From email created for this coursework
	$mail->addAddress($email);     // Add a recipient
	$mail->addReplyTo(EMAIL);
	$mail->isHTML(true);           // Set email format to HTML
	//Adding subject & body to email 
	$mail->Subject = $subject;
	$mail->Body    = $email_template;
	//Sending the email
	if(!$mail->send()) {
		return 0;
	} else {
		return 1;
	}
}
?>
