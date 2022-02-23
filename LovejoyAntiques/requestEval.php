<?php
//Main.php required to perform check_loggedin function.
//Check the user is logged in, befopre they can access this page. Otherwise redirected to login page.
//Stops users with the URL from accessing page without logging in
include 'main.php';
check_loggedin($con);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<!-- WebPage where users can request an evaluation of their antique -->
		<title>Lovejoy Antiques Request Evaluation Form</title>
		<!-- Stylesheets used for consitence format -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="style1.css">
	</head>
	<body>
		<nav>
			<div>
				<ul>
					<li><a href="requestEval.php" class="active">Request an Evaluation</a></li>
					<!-- This link on nav bar is only shown to Admins. If user is a member, this will not appear on the navigation bar -->
					<?php if ($_SESSION['role'] == 'Admin'): ?>
						<li><a href="viewRequests.php"></i>View Evaluation Requests</a></li>
					<?php endif; ?>
					<!-- Link to log users out once done -->
					<li style="float:right"><a href="logout.php"></i>Logout</a></li>
				</ul>
			</div>
		</nav>
		<div class="container">
			<div class="row">
				<div class = "col-md-12">
					<h2><strong>LoveJoy Antique Evaluations: </strong><br>Request Evaluation Form</h2><br>
					<!-- On submission, check is done on details submitted -->
					<form action="requestCheck.php" method="post" autocomplete="off" enctype="multipart/form-data">
						<div class="form-group">	
						<u><p style="text-align:left">Please select the image of the Antique to upload</p></u>
						<!-- Input type of file, allows for a file to be uplaoded. This is required along with a description & a choice of contact -->
							<input type="file" name="antique_image" required>
						</div>
						<br>
						<div class="form-group">
							<u><p style="text-align:left">Please enter a description of the Antique</p></u>
							<!-- Textarea allows user to leave a large description of the antique if wanted by the user. This is also required. -->
							<!-- In Addition, CSS is used to give the textarea a red, dashed appearance if not completed, and a green border if completed. -->
							<textarea autofocus name="desc" spellcheck minlength="10" rows="5" cols="50"  placeholder="Enter a brief description of the item..." required></textarea>
						</div>
						<br>
						<!-- Instead of dropdown, as only two options, radio buttons used for contact choice. User can choose only between Email & Telephone contact -->
						<div class="form-group">
							<u><p style="text-align:left">How would you like to be contacted?</p></u>
							<input type="radio" id="telephone" value=0 name="contactChoice"  required>
							<label style="margin-right: 30px" for="email">Email</label>
							<input type="radio" id="email" value=1 name="contactChoice" required>
							<label for="telephone">Telephone</label><br>
						</div>
						<!-- Submission button -->
						<div class="form-group">
							<input type="submit" name="submit" class="btn btn-primary" value="Request">
						</div>
				</div>
			</div>
		</div>
	</body>
</html>
