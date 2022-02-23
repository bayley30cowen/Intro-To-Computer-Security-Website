<?php
//User must be logged in to access this
include 'main.php';
check_loggedin($con);
//If form submitted & image has been selected.
if (isset($_POST['submit']) && isset($_FILES['antique_image'])) {
	//Gets the name of the image, the size and a temp name of the image along with any occuring errors.
	$img_name = $_FILES['antique_image']['name'];
	$img_size = $_FILES['antique_image']['size'];
	$tmp_name = $_FILES['antique_image']['tmp_name'];
	$error = $_FILES['antique_image']['error'];
	//Choice of contact detail stored from selection of Radio Buttons
	$choice = $_POST['contactChoice'];
	//Description of Antique is sanitized against SQL injection & XSS
	$desc = mysqli_real_escape_string($con, $_POST['desc']);
	$desc = htmlspecialchars($desc);
	//User ID is taken from the ID stored in the session, which is stored on login.
	$userid = $_SESSION['id'];
	//If there is no errors
	if ($error === 0) {
		//And the image does not exceed 1.25MB
		if ($img_size > 1250000) {
			echo 'Sorry, your file is too large. Please use a file below 1.25MB';
			header("Refresh:3; url=requestEval.php");
		}else {
			//Image extension extracted from image name
			$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
			//Converts extension to lowercase for easier comparison checks
			$img_ex_lc = strtolower($img_ex);
			//Array of allowed extensions, i.e. image extensions
			$allowed_exs = array("jpg", "jpeg", "png"); 
			//If extension of file uploaded is an image
			if (in_array($img_ex_lc, $allowed_exs)) {
				//New name of file is created. 
				//Renaming file protects the database from attacks, when users rename photos to SQL Injection statements
				$new_img_name = uniqid("IMG-", true).'.'.$img_ex_lc;
				//Path which is stored in database, is created by adding uploads/ before image name as all images stored in a folder called uploads.
				$img_upload_path = 'uploads/'.$new_img_name;
				//Moves image file to uploads folder.
				move_uploaded_file($tmp_name, $img_upload_path);
				// Insert into Database. Prepared Statement used for further injection protection.
				$stmt = $con->prepare("INSERT INTO evaluations (description, contactDetail,  image_url, userid) VALUES (?, ?, ?, ?)");
				//Binding Parameters with users inputs, plus user id which is a foreign key in evaluations table
				//Foreign key requried to link account details like email & telephone to each evaluation
				//Email/Telephone not stored in evaluation table, incase these updated, & it being redundant duplicate data.
				$stmt->bind_param('sisi', $desc, $choice ,$new_img_name, $userid);
				$stmt->execute();
				$stmt->close();
				//Successful Evaluation Request
				echo 'Successful Request Submitted! Redirecting back to Evaluation page!';
				header("Refresh:3; url=requestEval.php");
			}else {
				//Wrong file type
				echo 'You cannot upload files of this type. Please upload a .jpg, .jpeg or a .png';
				header("Refresh:2; url=requestEval.php");
			}
		}
	}else {
		//$_FILES returned an error
		echo 'Unknown Error Occured! '. $error;
		header("Refresh:3; url=requestEval.php");
	}
}else {
	//If submit has not occured or no image has been uploaded (this would be caught by HTML)
	header("Refresh:2; url=requestEval.php");
}
?>