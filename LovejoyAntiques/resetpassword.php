    <?php
include 'main.php';
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
//This is the message that will be displayed to the user if there are any errors
$msg = '';
//Checking email & reset code from URL
if (isset($_GET['email'], $_GET['code']) && !empty($_GET['code'])) {
    //Get Security Question & Answer, along with old password from Database.
    //Prepared Statement Used for good Practice
    $stmt = $con->prepare('SELECT securityQuestion, securityAnswer, password, username FROM accounts WHERE email = ? AND reset = ?');
    $stmt->bind_param('ss', $_GET['email'], $_GET['code']);
    $stmt->execute();
    $stmt->store_result();
    // If user exists.
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($securityQuestion, $securityAnswer, $passwordOld, $username);
        $stmt->fetch();
        $stmt->close();
        //If not empty
        if (isset($_POST['new_password'], $_POST['confirm_password'])) {
            //Setting User's inputs to variables
            //Protection against XSS & SQL Injection
            $securityAnswerEnt = mysqli_real_escape_string($con, $_POST['securityAnswer']);
            $securityAnswerEnt = htmlspecialchars($securityAnswerEnt);
            $password = mysqli_real_escape_string($con, $_POST['new_password']);
            $password = htmlspecialchars($password);
            $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);
            $confirm_password = htmlspecialchars($confirm_password);

            //To avoid casing issues
            $securityAnswer=strtolower($securityAnswer);
            $securityAnswerEnt=strtolower($securityAnswerEnt);

            //Password Strength Check
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);
            // Used to Check to see if Password is based on username. This checks for palindrome & if user replaces letters like s,e,o & l with number/other charcters alike.
            //Converted to lowercase in case of Casing issues
            $lc_password = strtolower($password);
            $lc_username = strtolower($username);
            $denum_pass = strtr($lc_password,'5301!','seoll');

            //Password Stength
            if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 10 || strlen($password) > 100) {
                $msg = 'Password should be at least 10 (no more than 100) characters in length and should include at least one upper case letter, one number, and one special character.';
            } else if ($confirm_password != $password) {
                //Whether new passwords entered match
                $msg = 'Passwords do not match!';
            } else if (strcmp($securityAnswer , $securityAnswerEnt) !== 0) {
                //Whether security Answer matches one in database. This is required to update the password for added protection.
                $msg = 'Security Answer do not match';
            } else if (password_verify($password, $passwordOld)) {
                //Ensure new password is not the same as old password
                $msg = 'New Password cannot be the same as Old Password';
                //Further Password Entropy
            } else if (($lc_password == $lc_username) || ($lc_password == strrev($lc_username)) || ($denum_pass == $lc_username) || ($denum_pass == strrev($lc_username))) {
                $msg = "Password cannot be based on the username!";
            } else {
                //Prepared SQL Statement updates the password stored in database for user & resets the reset code to empty string
                $stmt = $con->prepare('UPDATE accounts SET password = ?, reset = "" WHERE email = ?');
                //Hashes passworded for protection in database
                $passwordenc = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bind_param('ss', $passwordenc, $_GET['email']);
                $stmt->execute();
                $stmt->close();
                //Sucess message & redirect
                $msg = 'Password has been reset! Redirecting Now!';
                header("Refresh:3; url=login.php");
            }
        }
    } else {
        //No rows from select statement. Possible reset code is not valid/expired.
        exit("Link Expired!<a href='login.php'> Click here to return to the login screen</a>");
    }
} else {
    //Code/Email in URL not found. Incase user just types /resetpassword.php into URL.
    exit("Link Expired!<a href='login.php'> Click here to return to the login screen</a>");
    header("Refresh:3; url=login.php"); 
}
?>
<!DOCTYPE html>
<html lang='en'>
    <!-- HTML for Reset Password Form -->
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<title>Lovejoy Antiques Reset Password</title>	
		<link rel="stylesheet" href="style1.css">
	</head>
	<body>
			<div class="container">
				<div class="row">
					<div class = "col-md-12">
                        <!-- Form elements -->
						<h2><strong>LoveJoy Antique Evaluations: </strong><br>Reset Password</h2><br>
						<p><strong>Please complete your security question & then enter your new password</strong></p>
                        <!-- On Submit, redirects to PHP with email & code in link, used to idenify user & stop anyone accessing this page -->
						<form action="resetpassword.php?email=<?=$_GET['email']?>&code=<?=$_GET['code']?>" method="post" autocomplete=off>
							<div class="form-group">
                                <br>
                                <!-- Displays Security Question which was extracted in SQL Select Statement 
                                This is requried to be correct to allow users to update their password for added Protection-->
								<p style= "text-align:left;"><b>Security Question:</b> <?=$securityQuestion?></p>
							</div>
							<div class="form-group">
                                <!-- Textfield for Security Answer. Only allows for the letters, spaces & hypens-->
								<input type="text" name="securityAnswer" placeholder="Answer to Security Question" pattern="[A-Za-z0-9 -]+" class="form-control" required>
								<label for="password"></label>
							</div>
                            <br>
							<div class="form-group">
                                <!-- Password for user. Min length 10 characters. Type 'Password' hides inputs as *s. Further validation is done in PHP -->
								<input type="password" name="new_password" placeholder="Password" class="form-control" minlength="10" required>
								<label for="confirm_password"></label>
								<small>Must be atleast 10 characters long & contain atleast one uppercase, lowercase, number & special character</small>
							</div>
							<div class="form-group">
                                <!-- Confirmation Password for user. Same validation as password.-->
								<input type="password" name="confirm_password" placeholder="Confirm Password" minlength="10" class="form-control" required>
							</div>
                            <!-- Error messahes displayed here in red -->
                                <p class="msg"><?=$msg?></p><br>
							<div class="form-group">
                                <!-- Submit Button. Onclick, executes PHP in document -->
								<input type="submit" class="btn btn-primary" value="Submit">
							</div>
					</form>
				</div>
			</div>
		</div>>
	</body>
</html>
