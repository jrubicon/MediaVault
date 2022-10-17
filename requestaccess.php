<?php
include "mv_con.php";

//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (isset($_POST['submit']) && !isset($_POST['usernameofuser'], $_POST['passwordofuser'], $_POST['companyofuser'], $_POST['nameofuser'], $_POST['emailofuser']) ) {
	// Could not get the data that should have been sent.
	die ('Please fill in all fields: Company, name, email, username, password.');
}

$succ = 0;

if(isset($_POST['usernameofuser'], $_POST['passwordofuser'], $_POST['companyofuser'], $_POST['nameofuser'], $_POST['emailofuser'])){
	$email = $_POST['emailofuser'];
	$multisubs = $con->prepare('SELECT * FROM submissions WHERE email = ?');
	$multisubs->bind_param('s', $email);
	$multisubs->execute();
	$multisubs->store_result();
	if ($multisubs->num_rows){
		die ('An account with that email has already requested access, please contact the administrator.');
	}
	$lookfordups = $con->prepare('SELECT * FROM accounts WHERE email = ?');
	$lookfordups->bind_param('s', $email);
	$lookfordups->execute();
	$lookfordups->store_result();
	if ($lookfordups->num_rows){
		die ('An account with that email already exists, please contact the administrator.');
	}
	else {
		$username = $_POST['usernameofuser'];
	  $pass = password_hash($_POST['passwordofuser'], PASSWORD_DEFAULT);
	  $comp = $_POST['companyofuser'];
	  $name = $_POST['nameofuser'];
	  $email = $_POST['emailofuser'];
	  $ip = $_SERVER['REMOTE_ADDR'];
	  $submission = $con->prepare("INSERT INTO submissions (company, name, email, username, password, ip) VALUES (?,?,?,?,?,?)");
	  $submission->bind_param("ssssss", $comp, $name, $email, $username, $pass, $ip);
	  $succ = $submission->execute();
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">
		
		<title>Request Access</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>

		<div class="requestform">
      <div id="ReqSub" class="requestsubmitted">
        <h1>Form Submitted!</h1>
        <p>Please wait to be contacted</p>
      </div>
			<form method="post">
				<label for="companyofuser">
          Company
				</label>
        <input type="text" name="companyofuser" id="companyofuser" required>
				<label for="nameofuser">
          Name
        </label>
        <input type="text" name="nameofuser" id="nameofname" required>
        <label for="emailofuser">
          Email
				</label>
        <input type="email" name="emailofuser" id="emailofuser" required>
        <label for="usernameofuser">
          Username
				</label>
        <input type="text" name="usernameofuser" id="usernameofuser" required>
        <label for="passwordofuser">
          Password
				</label>
        <input type="password" name="passwordofuser" id="passwordofuser" required>
				<input name="submit" type="submit" value="Submit">
			</form>
      <p><a href="/">Back to login screen</a><p>
		</div>

		<script>
		  var formsub = document.getElementById('ReqSub');
		  var succbool = <?php echo $succ; ?>;
		  if(succbool){
		    formsub.style.display = "block";
		    alert("Form Submitted Successfully, Your Account Will Be Approved Pending Review Shortly.");
		  }
		</script>

	</body>
</html>
