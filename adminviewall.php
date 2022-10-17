<?php
include "mv_con.php";

session_save_path('../../mediasession');
session_start();
if (!isset($_SESSION['loggedin'])){
	header('Location: /');
	exit();
}
if ($_SESSION['adminaccess'] != TRUE){
  header('Location: /home');
	exit();
}

//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if(isset($_POST['passuser'])){
	//update user password
	$userpsid = $_POST['passid'];
  $newpass = $_POST['passtext'];
	$newpwup = $con->prepare('UPDATE accounts SET password = ? WHERE id = ?');
  $newpwup->bind_param('ss', password_hash($newpass, PASSWORD_DEFAULT), $userpsid);
	$success = $newpwup->execute();
}
if(isset($_POST['deleteuser'])){
	foreach ($_POST['deluser'] as $deleteuser) {
    $delrun = $con->prepare('DELETE FROM accounts WHERE id = ?');
    $delrun->bind_param('s',$deleteuser);
    $delrun->execute();
  }
	header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">

		<title>Admin Panel - users</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
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

      <div class="main-content">
        <div class="adminpanel">
          <div class="adminbuttons">
            <a href="/keypanel"><button>View All Requests</button></a>
						<a href="/adminaddmedia"><button>Add Media</button></a>
						<a href="/admindelfolder"><button>Delete Folders</button></a>
          </div>

          <div class="accessrequests">
            <h1>All Users</h1>
						<form method="post">
	            <table class="pendingrequests">
	              <tr><th>UserId</th><th>Delete</th><th>Company</th><th>Name</th><th>Username</th><th>Email</th><th>Last Login</th><th>IP</th><th>Role</th></tr>
	              <?php
	              $graballusers = $con->prepare('SELECT * FROM accounts WHERE role != "admin"');
	              $graballusers->execute();
	              $graballusers->store_result();
	              if($graballusers->num_rows){
	                $graballusers->bind_result($userid, $nameofuser, $username, $password, $email, $company, $ip, $lastlogin, $role);
	                while($graballusers->fetch()){
	                  echo "<tr id='row'>
										<td>".$userid."</td>
										<td><input name='deluser[]' type='checkbox' value='".$userid."''></td>
	                  <td>".$company."</td>
	                  <td>".$nameofuser."</td>
	                  <td>".$username."</td>
	                  <td>".$email."</td>
	                  <td>".$lastlogin."</td>
	                  <td>".$ip."</td>
	                  <td>".$role."</td>
	                  </tr>";
	                }
	              }
								if($graballusers->num_rows == 0){
									echo "<tr><td colspan='8'> NO USERS </td></tr>";
								}
	              ?>
	            </table>
							<input style="margin-top:20px" type="submit" name="deleteuser" onclick="return confirm('Are you sure?');" value="Delete User(s)"/>
						</form>
        	</div>

					<div class="passform">
						<h1>Reset A Users Password</h1>
						<form method="post">
							<table>
								<tr>
									<th><label for="passid">User Id:</label></th>
									<td><input type="number" name="passid"/></td>
								</tr>
								<tr>
									<th><label for="passtext">New Password:</label></th>
									<td><input type="text" name="passtext"/></td>
								</tr>
							</table>
							<input style="margin-top:20px;margin-left:150px" type="submit" name="passuser" onclick="return confirm('Confirm Password Reset?');" value="Update User Password"/>
						</form>
					</div>


        </div>
      </div>

      <footer>
      		<h1>Canine Caviar © 2020 - <?php echo date("Y"); ?></h1>
      </footer>

    </div>
  </body>
</html>
