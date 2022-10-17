<?php
include "mv_con.php";
$setAdminEmailFrom = "noreply@example.com";
$setAdminEmailCC = "admin@example.com";


//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

function random_str(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
    if ($length < 1){
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

if(isset($_GET['request'])){
  $codegen = $_GET['request'];
  $getreq = $con->prepare("SELECT * FROM accountrecovery WHERE codegen = ?");
  $getreq->bind_param('s', $codegen);
  $getreq->execute();
  $getreq->store_result();
  if($getreq->num_rows){
    $getreq->bind_result($sid, $sname, $susername, $semail, $scodegen, $dateofrequest);
    $getreq->fetch();
      if($susername == $_GET['user']){
        //reset password redirect
        header("Location: resetpass.php?code=".$scodegen."&id=".$sid."");
      }
      else {
        die ("ERROR 569: Contact admin");
      }
  }
  else {
    die ("NO REQUEST HAS BEEN MADE WITH THAT AUTHORIZATION CODE");
  }
}
if(isset($_POST['emailofuser'])){
  $userlook = $con->prepare("SELECT name, username, email FROM accounts WHERE email = ?");
  $userlook->bind_param('s', $_POST['emailofuser']);
  $userlook->execute();
  $userlook->store_result();
  if($userlook->num_rows){
    $userlook->bind_result($rname, $rusername, $remail);
    $userlook->fetch();
    $generatedcode = random_str();

    $requestrec = $con->prepare("INSERT INTO accountrecovery (name, username, email, codegen) VALUES (?, ?, ?, ?)");
    $requestrec->bind_param('ssss', $rname, $rusername, $remail, $generatedcode);
    $requestrec->execute();

      $to = $remail;
      $subject = "Media Vault Password Reset: " . $rusername;
      $message = "
      <html>
      <head>
      <title>Media Vault Password Reset: by ".$rusername."</title>
      </head>
      <body>
        <h1>Media Vault Password Reset</h1><br>
        <p>
        Hi ".$rname.",<br>
        Someone has requested a password reset for your account.<br>
        If you did not authorize a password request, feel free to ignore this email.<br><br>
        Click the link below to change your password.<br><br>
        <a href='".$_SERVER['HTTP_HOST']."/forgotpassword.php?request=".$generatedcode."&user=".$rusername."'>
        Change your password
        </a>
        </p>
      </body>
      </html>
      ";
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
      //more headers
      $headers .= 'From: MediaVault <'.$setAdminEmailFrom.'>'."\r\n";
      $headers .= 'Cc: <'.$setAdminEmailCC.'>'."\r\n";
      $headers .= 'X-Priority: 1' . "\r\n";
      $headers .= 'Priority: Urgent' . "\r\n";
      $headers .= 'Important: high';
      mail($to, $subject, $message, $headers);
      header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
  }
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">

		<title>Request Password Reset</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>

		<div class="requestform">

      <div id="forpass" class="requestpass">
        <h1>Forgot Your Password?</h1>
        <p>Please enter your email address.</p>
      </div>

			<form action="/forgotpassword" id="resetform" method="post">

        <label for="emailofuser">
          Email
				</label>
        <input type="email" name="emailofuser" id="emailofuser" required>
				<input name="submit" type="submit" value="Submit">
			</form>
			<script>
				document.getElementById("resetform").onsubmit = function() { displaySubmit() };

				function displaySubmit(){
					alert("Password Reset Submitted. Check Your Email.");
				}
			</script>
      <p><a href="/">Back to login screen</a><p>
		</div>

	</body>
</html>
