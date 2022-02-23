<?php
//As mail is sent out
include 'main.php';
// Output message
$msg = '';
// Verify the ID and email and 2FA code provided in URL are present
//And 2FA session code, is equal to code in URL
if (isset($_GET['id'], $_GET['email'], $_GET['code'], $_SESSION['2FA']) && $_SESSION['2FA'] == $_GET['code']) {
    // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
    $stmt = $con->prepare('SELECT email, 2FA_code, role FROM accounts WHERE id = ? AND email = ?');
    $stmt->bind_param('ii', $_GET['id'], $_GET['email']);
    $stmt->execute();
    // Store the result so we can check if the account exists in the database.
    $stmt->store_result();
    // If the account exists with the email & ID provided...
    if ($stmt->num_rows > 0) {
        //Bind the email, 2FA code and role selected from accounts to the following variables
    	$stmt->bind_result($email, $acc_code, $role);
    	$stmt->fetch();
    	$stmt->close();
        // 2FA Code submitted in the form
        if (isset($_POST['code'])) {
            //Code submitted = the code in the database
            //Santize Input
            $codeEnt = mysqli_real_escape_string($con, $_POST['code']);
            $codeEnt = htmlspecialchars($codeEnt);
            if ($codeEnt == $acc_code) {
                // Code accepted, update the IP address to this login address
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt = $con->prepare('UPDATE accounts SET ip = ? WHERE id = ?');
                $stmt->bind_param('si', $ip, $_GET['id']);
                $stmt->execute();
                $stmt->close();
                //Regenerate session, updating logged in as TRUE
                session_regenerate_id();
			    $_SESSION['loggedin'] = TRUE;
                $_SESSION['id'] = $_GET['id'];
                $_SESSION['role'] = $role;
                $msg = '2FA Code has been accepted! You can now access the website <a href="requestEval.php">here</a>!';
            } else {
                //Code is not accepted, therefore code incorrect/expired
                $msg = 'Incorrect code provided!';
            }
        } else {
            //Send the access code email using the twofactor.html template
            //Creates 6 digit random unique code for the 2FA code
            //mt_rand is the new version of rand()
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            //Updates the 2FA code in the database, to the newly created code
            $stmt = $con->prepare('UPDATE accounts SET 2FA_code = ? WHERE id = ?');
            $stmt->bind_param('si', $code, $_GET['id']);
            $stmt->execute();
            $stmt->close();

            //Setting Subject
            $subject = 'Your Two-Factor Access Code';
            //Replacing the template string with the actual code
            $email_template = str_replace('%code%', $code, file_get_contents('twofactor.html'));
            //Adding subject & body to email 
            if (sendEmail($email, $email_template, $subject)) {
                $msg = 'Message has been sent. Please check your email (and Junk Mail)';
            } else {
                $msg = 'Message cannot be sent. Mail Error Occured';
                header("Refresh:5; url=login.php");
            }
        }
    } else {
        //No user found in select statement
        exit('Incorrect email and/or code provided!');
    }
} else {
    //URL does not contain an email &/or code
    exit('No email and/or code provided!');
}
?>
<!DOCTYPE html>
<html>
	<head>
        <!-- HTML code for Two Factor Page -->
		<meta charset="UTF-8">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<title>Lovejoy Antiques: Two-Factor Authentication</title>	
		<link rel="stylesheet" href="style1.css">
	</head>
	<body>
        <div class="container">
			<div class="row">
				<div class = "col-md-12">
                    <h2><strong>LoveJoy Antique Evaluations: </strong><br>Two-Factor Authentication</h2><br>
                    <p style="padding:10px;margin:0;">Please enter the 6-digit code, that was sent to your email address, below.</p>
                    <!-- Onclick, executes POST method in PHP above -->
                    <form action="" method="post">
                        <div class="form-group">
                            <!-- Input Field for 2FA code. Pattern used to ensure only 6 digits entered -->
                            <input type="text" name="code" placeholder="2FA Code sent via Email" class="form-control" pattern="[0-9A-Za-z]{6}" required>
                        </div>
                        <div class="form-group">
                            <!-- Error/Success Message Displayed Here -->
                            <div class="msg"><?=$msg?></div><BR>
                            <!-- Submit Button -->
                            <input type="submit" value="Submit" class="btn btn-primary">
                        </div>
                    </form>
                </div>
		    </div>
        </div>    
	</body>
</html>
