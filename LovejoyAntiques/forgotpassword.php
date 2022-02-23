<?php
include 'main.php';
$msg = '';
// If not empty when submitted 
if (isset($_POST['email'])) {
	//Protection against XSS
	//To check its an email, this is done via HTML attribute type=email & empty is checked using required attribute
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$email = htmlspecialchars($email);
    // SQL statements that are prepared, allow for protection against SQL Injection
    $stmt = $con->prepare('SELECT * FROM accounts WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    // Check if the email exists in database
    if ($stmt->num_rows > 0) {
    	$stmt->close();
        // If Email does exist
        // Creates a new & updates the reset code into the db.
    	$uniqid = uniqid();
        $stmt = $con->prepare('UPDATE accounts SET reset = ? WHERE email = ?');
        $stmt->bind_param('ss', $uniqid, $email);
        $stmt->execute();
        $stmt->close();
		//Subject & Body of the email
		$subject = 'Password Reset';
		$reset_link = 'http://users.sussex.ac.uk/~bcc28/G6077/LovejoyAntiques/resetpassword.php?email=' . $email . '&code=' . $uniqid;
		$message = '<p>Please click the following link to reset your password: <a href="' . $reset_link . '">' . $reset_link . '</a></p>';
		//Adding subject & body to email 
		//Using Email Template for nice formatting*/
		$email_template = str_replace('%link%', $message, file_get_contents('forgotpassword.html'));
		if (sendEmail($email, $email_template, $subject)) {
			$msg = 'Message has been sent. Please check your email (and Junk Mail)';
		} else {
			$msg = 'Message cannot be sent. Mail Error Occured';
			header("Refresh:5; url=login.php");
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Lovejoy Antiques: Forgot Password </title>
		<link href="style1.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class = "col-md-12">
					<h2><strong>LoveJoy Antique Evaluations: </strong><br>Forgot Password</h2><br>
					<form action="forgotpassword.php" method="post">
					<div class="form-group">
						<input type="email" name="email" placeholder="Your Email" id="email" class="form-control" required>
					</div>
					<div class="form-group">
						<input type="submit" value="Submit" class="btn btn-primary">
					</div>
					<div class="msg"><?=$msg?></div><br>
					<p>Remember your password? <a href="login.php">Login here</a>.</p>
					</form>	
				</div>
			</div>	
		</div>
	</body>
	</html>
