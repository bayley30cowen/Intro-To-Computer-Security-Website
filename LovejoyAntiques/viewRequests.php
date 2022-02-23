<?php
include 'main.php';
//Checks if user is logged in (as this page requires users to be logged in to view
//If not logged in, re-directed to login page
check_loggedin($con);
// Ensures only admins can view this page. Otherwise, instantly re-drects Member users to the request page
if ($_SESSION['role'] != 'Admin') {
    header("Location: requestEval.php");
    exit;
}
?>
<!DOCTYPE html>
<!-- Webpage that displays all evaluation requests to an admin -->
<html>
	<head>
		<meta charset="UTF-8">
		<title>Lovejoy Antiques View Evaluation Requests</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="style1.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<ul>
                    <!-- Navigation Bar -->
					<li><a href="requestEval.php">Request an Evaluation</a></li>
                    <li><a href="viewRequests.php" class="active"></i>View Evaluation Requests</a></li>
					<li style="float:right"><a href="logout.php"></i>Logout</a></li>
				</ul>
			</div>
		</nav>
		<div class="container">
			<div class="row">
				<div class = "col-md-12">
					<h2><strong>LoveJoy Antique Evaluations: </strong><br>View Evaluation Requests</h2><br>
                        <?php
                        //Prepare statements are not needed here, since user is not able to input any data!
                        //Using an INNER JOIN allows for the name, email and telephone number of the requestor to be stored in a temp table with the evaluation details
                        //This means, the requestors name & contact details do not need to be stored in the evaluation table. Ensuring no duplicate redundant data is stored.
                        $sql = 'SELECT accounts.fullname, evaluations.description, evaluations.contactDetail, accounts.email, accounts.telephone, evaluations.image_url FROM evaluations INNER JOIN accounts ON evaluations.userid=accounts.id';
                        $result = mysqli_query($con, $sql);
                        $results = mysqli_query($con, $sql);
                        //If no evaluations requests have been submitted by users, this is displayed
                        if ($results->num_rows === 0) {
                            echo "<p>No evaluation requests submitted</p>";
                            exit();
                        }
                        //Otherwise, table generated
                        echo "<table>";
                        echo "<tr>";
                        //Displays the name of the requestor
                        echo "<th>Name of Requestor</th>";
                        //Description of the antique, which was entered by the requestor
                        echo "<th>Description of Antique</th>";
                        //The email or telephone number of the requestor
                        echo "<th>Choice of Contact</th>";
                        //An image of the antique
                        echo "<th>Image of Antique</th>";
                        echo "</tr>";
                        //Whiel loop used to print a row for each evaluation in the table
                        while ($rows = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>".$rows['fullname']."</td>";
                            echo "<td>".$rows['description']."</td>";
                            //If contact choice chosen was 'email', display the email of the requestor in the table
                            if ($rows['contactDetail'] == 0) {
                                echo "<td>".$rows['email']."</td>";
                            }
                            else {
                            //Otherwise, if the user wished ot be contacted by telephone, their telephone number is displayed.
                                echo "<td>".$rows['telephone']."</td>";
                            }
                            //Images are printed to be the same size, for a clean & consistent look.
                            //Through CSS, admins can hover over the image for an enlargered look at the image.
                            echo "<td><img width='200' height='200' src='uploads/".$rows['image_url']."' ></img></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    ?>
				</div>
			</div>
		</div>
	</body>
</html>