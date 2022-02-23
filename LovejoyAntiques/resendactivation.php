<?php
//Include Main.php for database connection
//MailSetup also needed, as activation code sent via Email
include 'main.php';
// Output error/success message
$msg = '';
//isset() will check if the email has been entered by the user.
if (isset($_POST['email'])) {
    //SANITIZE INPUT against XSS & SQL Injection
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $email = htmlspecialchars($email);

    //Prepare our SQL, preparing the SQL statement will prevent SQL injection.
	//Activation Code is selected from database, where email = email entered by user on form & activation code is not empty or already activated
    $stmt = $con->prepare('SELECT activation_code FROM accounts WHERE email = ? AND activation_code != "activated"');
    // In this case we can use the account ID to get the activation code
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $stmt->store_result();
    //Check the account, with this email, exists in database
    if ($stmt->num_rows > 0) {
        //if it does indeed exist and needs to be activated
        $stmt->bind_result($activation_code);
        $stmt->fetch();
        $stmt->close();
        //Account exist, the $msg variable will be used to show the output message (on the HTML form)
        $subject = 'Account Activation Required';
		$activate_link = 'http://users.sussex.ac.uk/~bcc28/G6077/LovejoyAntiques/activate.php?email=' . $email . '&code=' . $activation_code;
		$message = '<p>Please click the following link to activate your account!: <a href="' . $activate_link . '">' . $activate_link . '</a></p>';
		$email_template = str_replace('%link%', $message, file_get_contents('activation.html'));
		//Sending the email
		if (sendEmail($email, $email_template, $subject)) {
			$msg = 'Message has been sent. Please check your email (and Junk Mail)';
		} else {
			$msg = 'Message cannot be sent. Mail Error Occured';
			header("Refresh:5; url=login.php");
		}
    } else {
		//Account already Activated or Email entered invalid
        $msg = "No activation is required or email doesn't exist in database";
    }
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>LoveJoy Antiques: Resend Activation Email</title>
		<!-- Stylesheets used for CSS Formatting -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="style1.css">
	</head>
	<body>
        <div class="container">
			<div class="row">
				<div class = "col-md-12">
                    <h2><strong>LoveJoy Antique Evaluations: </strong><br>Resend Activation Email</h2><br>
                    <form action="resendactivation.php" method="post">
                        <div class="form-group">
                            <label for="email"></label>
                        </div>    
                        <div class="form-group">
							<!-- Form text field to allow user to enter email. Required Field, with pattern to ensure a valid email format is entered -->
				            <input type="email" name="email" placeholder="Your Email" class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                        </div>
                        <div class="form-group">
							<!-- Submit Button -->
				            <input type="submit" class="btn btn-primary" value="Submit">
                        </div>
						<!-- Outputs Error/Success Message  -->
                        <p><?=$msg?></p>
						<p>Account already activated? <a href="login.php">Login here</a>.</p>
                    </form>
                </div>
            </div>
        </div> 
	</body>
</html>
