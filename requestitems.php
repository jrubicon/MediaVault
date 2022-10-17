<?php
include "mv_con.php";
$setAdminEmailTo = "info@example.com";
$setAdminEmailCC = "admin@example.com";

session_save_path('../../mediasession');
session_start();
if (!isset($_SESSION['loggedin'])){
	header('Location: /');
	exit();
}

//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$notice = 0;

if(isset($_POST['subreq'])){
  $userreq = $_POST['req-user'];
  $namereq = $_POST['req-name'];
  $emailreq = $_POST['req-email'];
  $telreq = $_POST['req-tel'];
  $compreq = $_POST['req-comp'];
  $subreq = $_POST['req-sub'];
  $bodyreq = $_POST['req-body'];
  $notice = 1;

  $to = $setAdminEmailTo;
  $subject = "MEDIA VAULT REQ: " . $subreq;
  $message = "
  <html>
  <head>
  <title>MEDIA VAULT REQ: by ".$namereq.", company:".$compreq."</title>
  </head>
  <body>
    <h1>MEDIA VAULT REQ: ".$subreq."</h1><br>
    <span style='color:red'>User:</span><br>
    ".$userreq."<br>
    <span style='color:red'>Name:</span><br>
    ".$namereq."<br>
    <span style='color:red'>Email:</span><br>
    ".$emailreq."<br>
    <span style='color:red'>Phone:</span><br>
    ".$telreq."<br>
    <span style='color:red'>Company:</span><br>
    ".$compreq."<br>
    <span style='color:red'>Sub:</span><br>
    ".$subreq."<br>
    <span style='color:red'>Message:</span><br>
    ".$bodyreq."<br>
  </body>
  </html>
  ";
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
  //more headers
  $headers .= 'From: <'.$emailreq.'>'."\r\n";
  $headers .= 'Cc: <'.$setAdminEmailCC.'>, <'.$emailreq.'>'."\r\n";
  $headers .= 'X-Priority: 1' . "\r\n";
  $headers .= 'Priority: Urgent' . "\r\n";
  $headers .= 'Important: high';
  mail($to, $subject, $message, $headers);
}

$getuser = $con->prepare('SELECT name, username, email, company FROM accounts WHERE id = ?');
$getuser->bind_param('s', $_SESSION['id']);
$getuser->execute();
$getuser->bind_result($nameofuser, $username, $email, $company);
$getuser->fetch();

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">

		<title>MediaVault - Requests</title>
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
        <div class="userrequestitems-content">
          <div class="contreq-title">
    				<h1>Submit A Request</h1>
    			</div>
          <div id='userreqform-succ'>
            <p>Request Submitted Successfully.</p>
          </div>
          <form method="post">
            <input type="hidden" name="req-user" value="<?php echo $username; ?>" required/>
            <label for="req-name">Name</label>
            <input type="text" name="req-name" value="<?php echo $nameofuser; ?>" required/>
            <label for="req-email">Email</label>
            <input type="email" name="req-email" value="<?php echo $email; ?>" required/>
            <label for="req-tel">Telephone (optional)</label>
            <input type="tel" name="req-tel"/>
            <label for="req-comp">Company</label>
            <input type="text" name="req-comp" value="<?php echo $company; ?>" required/>
            <label for="req-sub">Subject</label>
            <input type="text" name="req-sub" required/>
            <label for="req-body">Message</label>
            <textarea class="itemreq-inp" type="text" name="req-body" required/></textarea>
            <input type="submit" name="subreq" value="Submit"/>
          </form>
        </div>
  		</div>
    </div>

    <footer>
      <h1>Media Vault © 2020 - <?php echo date("Y"); ?></h1>
    </footer>
   <script>
     var formsub = document.getElementById('userreqform-succ');
     var succbool = <?php echo $notice; ?>;
     if(succbool){
       formsub.style.display = "block";
       alert("Form Submitted Successfully");
     }
   </script>
    </div>
  </body>
</html>
