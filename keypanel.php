<?php
include "mv_con.php";

$setAdminEmailFrom = "noreply@example.com";
$setAdminEmailCC = "admin@example.com";

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

if (isset($_POST["reqaction_submit"])){
  foreach ($_POST['approval'] as $approved) {
    $adduser = $con->prepare('INSERT INTO accounts (name, username, password, email, company, ip, role)
    SELECT name, username, password, email, company, ip, "user" FROM submissions WHERE id = ?');
    $adduser->bind_param('s',$approved);
    $adduser->execute();
		$insertedid = $adduser->insert_id;

    $delentry = $con->prepare('DELETE FROM submissions WHERE id = ?');
    $delentry->bind_param('s',$approved);
    $delentry->execute();

		$grabemail = $con->prepare('SELECT email FROM accounts WHERE id = ?');
		$grabemail->bind_param('s',$insertedid);
		$grabemail->execute();
		$grabemail->store_result();
		$grabemail->bind_result($emailnotif);
		$grabemail->fetch();

		$to = $emailnotif;
		$subject = "Your MEDIA VAULT Request Has Been Approved.";
		$message = "
		  <html>
		  <head>
		  <title>Your MEDIA VAULT Request Has Been Approved.</title>
		  </head>
		  <body>
		    <h1>Your MEDIA VAULT Request Has Been Approved.</h1><br>
				<p>A request from this email account (".$emailnotif."), has been approved for media vault access.</p>
		  	<p>You can access the vault via ".$_SERVER['HTTP_HOST'].".</p>
		  </body>
		  </html>
		  ";
		  $headers = "MIME-Version: 1.0" . "\r\n";
		  $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
		  //more headers
		  $headers .= 'From: <'.$setAdminEmailFrom.'>'."\r\n";
		  $headers .= 'Cc: <'.$setAdminEmailCC.'>'."\r\n";
		  $headers .= 'X-Priority: 1' . "\r\n";
		  $headers .= 'Priority: Urgent' . "\r\n";
		  $headers .= 'Important: high';
		  mail($to, $subject, $message, $headers);

  }
  foreach ($_POST['disapproval'] as $disapproved) {
    $delentry2 = $con->prepare('DELETE FROM submissions WHERE id = ?');
    $delentry2->bind_param('s',$disapproved);
    $delentry2->execute();
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

		<title>Admin Panel - requests</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>
    <div class="site-container">

      <nav class="navtop">
  			<div class="navtop-logo">
  				<h1>Media Vault</h1>
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
            <a href="/adminviewall"><button>View All Users</button></a>
						<a href="/adminaddmedia"><button>Add Media</button></a>
						<a href="/admindelfolder"><button>Delete Folders</button></a>
          </div>

          <div class="accessrequests">
            <h1>Pending Requests</h1>
            <a href="/requestaccess">Request Access Form</a>
            <form method="post">
            <table class="pendingrequests">
              <tr><th>Approve</th><th>Delete</th><th>ID</th><th>Company</th><th>Name</th><th>Email</th><th>User</th><th>Req. Time</th><th>IP</th></tr>
              <?php
                $grabrequests = $con->prepare('SELECT * FROM submissions');
                $grabrequests->execute();
                $grabrequests->store_result();
                if($grabrequests->num_rows){
                  $grabrequests->bind_result($reqid, $reqcomp, $reqname, $reqemail, $requser, $reqpass, $reqtime, $reqip);
                  while($grabrequests->fetch()){
                    echo "<tr id='row'>
                    <td><input name='approval[]' type='checkbox' value='".$reqid."''></td>
                    <td><input name='disapproval[]' type='checkbox' value='".$reqid."''></td>
                    <td>".$reqid."</td>
                    <td>".$reqcomp."</td>
                    <td>".$reqname."</td>
                    <td>".$reqemail."</td>
                    <td>".$requser."</td>
                    <td>".$reqtime."</td>
                    <td>".$reqip."</td>
                    </tr>";
                  }
                }
                if($grabrequests->num_rows == 0){
                  echo "<tr><td colspan='9'> NO CURRENT REQUESTS </td></tr>";
                }
               ?>
            </table>
            <input style="margin-top:20px" name="reqaction_submit" type="submit" value="Submit">
          </form>
          </div>

        </div>
      </div>

      <footer>
      		<h1>Media Vault © 2020 - <?php echo date("Y"); ?></h1>
      </footer>

    </div>
  </body>
</html>
