<?php
include "mv_con.php";

// server should keep session data for AT LEAST 1 hour
session_save_path('../../mediasession');
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: /');
	exit();
}

//DB connection
$con = db_connection();
if (mysqli_connect_errno()) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$stmt = $con->prepare('SELECT id, name, username, password, email, company, role FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($id, $nameofuser, $username, $password, $email, $company, $grantrole);
$stmt->fetch();
$stmt->close();
$success = 0;
$nomatch = 0;

if(isset($_POST['pwchangesubmit'])){
  $oldpw = $_POST['oldpassword'];
  $newpass = $_POST['newpassword'];
  if(password_verify($_POST['oldpassword'], $password)){
    $newpwup = $con->prepare('UPDATE accounts SET password = ?
      WHERE id = ?');
    $newpwup->bind_param('ss', password_hash($newpass, PASSWORD_DEFAULT), $_SESSION['id']);
    $success = $newpwup->execute();
  }
  else {
    $nomatch = 1;
  }
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">
		
		<title>MediaVault - Profile Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<style>
		table {
				font-family: arial, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			td, th {
				border: 1px solid #dddddd;
				text-align: left;
				padding: 8px;
			}
			tr:nth-child(even) {
				background-color: #dddddd;
			}
	</style>
  <body>
    <div class="site-container">

      <nav class="navtop">
        <div class="navtop-logo">
          <h1>Canine Caviar - Media Vault</h1>
        </div>
        <div class="navtop-links">
          <a href="/home"><i class="fas fa-home"></i>Home</a>
          <a href="/profile"><i class="fas fa-user-circle"></i>Profile</a>
					<a href="/requestitems"><i class="fas fa-envelope"></i>Requests</a>
          <a href="/logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
          <?php
          if($_SESSION['adminaccess'] == TRUE){
            echo "<a href='/keypanel'><i class='fas fa-unlock-alt'></i>Admin</a>";
          }
          ?>
        </div>
      </nav>

  		<div class="content">
  			<h2>Profile Page</h2>
        <?php
        if($success == 1){
          echo "<div id='updateresponse'>Password updated successfully.</div>";
        }
        if($nomatch == 1){
          echo "<div id='updateresponse'>Password does not match.</div>";
        }
        ?>
  			<div>
  				<h1>Your account details are below:</h1>
  				<table>
            <tr>
  						<td>Name:</td>
  						<td><?=$nameofuser?></td>
  					</tr>
  					<tr>
  						<td>Username:</td>
  						<td><?=$_SESSION['user']?></td>
  					</tr>
  					<tr>
  						<td>Company:</td>
  						<td><?=$company?></td>
  					</tr>
  					<tr>
  						<td>Email:</td>
  						<td><?=$email?></td>
  					</tr>
            <tr>
  						<td>Role:</td>
  						<td><?=$grantrole?></td>
  					</tr>
  				</table>
  				<p><strong>To change your password please enter your old one and your desired new one.</strong></p>
          <form class="pwchange" method="post">
            <div>
              <label for="oldpassword">
                Old Password:
              </label>
              <input type="text" name="oldpassword" id="oldpassword" required>
            </div>
            <div>
              <label for="newpassword">
                New Password:
              </label>
              <input type="text" name="newpassword" id="newpassword" required>
            </div>
            <div>
              <input name="pwchangesubmit" type="submit" value="Change Password">
            </div>
          </form>
  				</div>
  		</div>

    </div>
	</body>
  <footer>
      <h1>Canine Caviar © 2020 - <?php echo date("Y"); ?></h1>
  </footer>
</html>
