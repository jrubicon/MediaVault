<?php
include "mv_con.php";

// server should keep session data for AT LEAST 1 hour
session_save_path('../../mediasession');
ini_set('session.gc_maxlifetime', 10800);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(10800);
session_start();

//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if ( !isset($_POST['username'], $_POST['password']) ) {
	// Could not get the data that should have been sent.
	die ('Please fill both the username and password field!');
}

//CHECK FOR BLACKLIST
$blacklcheck = $con->prepare('SELECT * FROM connections WHERE remote_address = ?');
$blacklcheck->bind_param('s', $_SERVER['REMOTE_ADDR']);
$blacklcheck->execute();
  // Store the result so we can check if the account exists in the database.
$blacklcheck->store_result();
$bl=NULL;
if ($blacklcheck->num_rows) {
	$blacklcheck->bind_result($remip, $forwip, $failedlogins, $blacklisted);
	$blacklcheck->fetch();
	$bl = $blacklisted;
}
if ($bl == '1'){
    die ('You are currently banned from accessing the vault. Please contact the administrator.');
		//header('Location: 403.php');
}
// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
else {
	$stmt = $con->prepare('SELECT id, name, username, password, email, company, role FROM accounts WHERE username = ?');
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();

    if ($stmt->num_rows > 0) {
      $stmt->bind_result($id, $nameofuser, $username, $password, $email, $company, $grantrole);
      $stmt->fetch();
      // Account exists, now we verify the password.
      // Note: remember to use password_hash in your registration file to store the hashed passwords.
      if (password_verify($_POST['password'], $password)) {
        // Verification success! User has loggedin!
        // Create sessions so we know the user is logged in
        //session_regenerate_id();
        $_SESSION['loggedin'] = TRUE;
				//removed name set to post[username] and set to stored name to avoid issues with case sensitives
        $_SESSION['name'] = $nameofuser;
        $_SESSION['user'] = $username;
				$_SESSION['id'] = $id;
				$_SESSION['adminaccess'] = FALSE;
				$_SESSION['acctype'] = $grantrole;
				//GRANT ADMINISTRATION ACCESS
				if ($grantrole == "admin"){
					$_SESSION['adminaccess'] = TRUE;
				}
				//update users last login time
				$datetoinsert = date('Y-m-d H:i:s');
				$updatelastlogin = $con->prepare('UPDATE accounts SET lastLogin = ? WHERE id = ?');
				$updatelastlogin->bind_param('ss', $datetoinsert, $id);
				$updatelastlogin->execute();

				//DELETE IP LOG IF USER HAS ACCUMULATED SEVERAL FAILS IN ATTEMPTING TO LOGIN
				$delfails = $con->prepare('DELETE FROM connections WHERE remote_address = ?');
				$delfails->bind_param('s', $_SERVER['REMOTE_ADDR']);
				$delfails->execute();
        header('Location: /home');
			  } else {

				//PASSWORD - INVALID LOG IP

				echo '<h1>Oopps...</h1><br>Seems you entered the incorrect password!<br>
				<img src="/RsrDoc/courage.gif" alt="error" style="margin-top: 25px;max-width:100%">
				';

				  $confail = $con->prepare('SELECT * FROM connections WHERE remote_address = ?');
					$confail->bind_param('s', $_SERVER['REMOTE_ADDR']);
					$confail->execute();
					// Store the result so we can check if the account exists in the database.
					$confail->store_result();

					if ($confail->num_rows > 0) {

						//IP exists already
						$confail->bind_result($remip, $forwip, $failedlogins, $blacklisted);
						$confail->fetch();
						$failedlogins++;
  						if($failedlogins > 5){
  							$blacklisted = '1';
  						}
						$updatefa = $con->prepare('UPDATE connections SET failed_logins=?, blacklisted=? WHERE remote_address=?');
						$updatefa->bind_param('sss', $failedlogins, $blacklisted, $_SERVER['REMOTE_ADDR']);
						$updatefa->execute();
					}
					else { //NEW IP TO LOG
						$newipstore = $con->prepare("INSERT INTO connections (remote_address, failed_logins, blacklisted)
													VALUES (?, ?, ?)");
						$one = 1;
						$zero = 0;
						$newipstore->bind_param('sss', $_SERVER['REMOTE_ADDR'], $one, $zero);
						$newipstore->execute();
					}

          }
        } else {

			//USERNAME - INVALID LOG IP
          	echo '<h1>Oopps...</h1><br>Seems you entered the incorrect username!<br>
			  <img src="/RsrDoc/courage.gif" alt="error" style="margin-top: 25px;max-width:100%">';

			  $confail = $con->prepare('SELECT * FROM connections WHERE remote_address = ?');
				$confail->bind_param('s', $_SERVER['REMOTE_ADDR']);
				$confail->execute();
				// Store the result so we can check if the account exists in the database.
				$confail->store_result();

				if ($confail->num_rows > 0) { //IP exists already
					$confail->bind_result($remip, $forwip, $failedlogins, $blacklisted);
					$confail->fetch();
					$failedlogins++;
  					if($failedlogins > 5){
  						$blacklisted = 1;
  					}
					$updatefa = $con->prepare('UPDATE connections SET failed_logins=?, blacklisted=? WHERE remote_address=?');
					$updatefa->bind_param('sss', $failedlogins, $blacklisted, $_SERVER['REMOTE_ADDR']);
					$updatefa->execute();
				}
				else { //NEW IP TO LOG
					$newipstore = $con->prepare("INSERT INTO connections (remote_address, failed_logins, blacklisted)
												VALUES (?, ?, ?)");
					$one = 1;
					$zero = 0;
					$newipstore->bind_param('sss', $_SERVER['REMOTE_ADDR'], $one, $zero);
					$newipstore->execute();
				}
          }

	$stmt->close();
}
?>
