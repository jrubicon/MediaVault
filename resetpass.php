<?php
include "mv_con.php";

//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if(isset($_POST['userpass'])){
  $rcode = $_POST['rcode'];
  $rid = $_POST['ird'];
  $pass = password_hash($_POST['userpass'], PASSWORD_DEFAULT);

  $getreq = $con->prepare("SELECT * FROM accountrecovery WHERE id = ? AND codegen = ?");
  $getreq->bind_param('ss', $rid, $rcode);
  $getreq->execute();
  $getreq->bind_result($sid, $sname, $susername, $semail, $scodegen, $dateofrequest);
  $getreq->fetch();
	$getreq->close();

  $accountupdate = $con->prepare("UPDATE accounts SET password = ? WHERE username = ? AND email = ?");
  $accountupdate->bind_param('sss', $pass, $susername, $semail);
  $accountupdate->execute();
	$accountupdate->close();

  $delreq = $con->prepare("DELETE FROM accountrecovery WHERE id= ?");
  $delreq->bind_param('s', $rid);
	$delreq->execute();

  header('Location: /');
}
else if(isset($_GET['code']) && isset($_GET['id'])){

	$codeval = $_GET['code'];
	$idval = $_GET['id'];
  $validate = $con->prepare("SELECT * FROM accountrecovery WHERE codegen = ? AND id = ?");
  $validate->bind_param('ss', $codeval, $idval);
  $validate->execute();
  $validate->store_result();

  if($validate->num_rows){
		?>
			<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<meta name="robots" content="noindex">
					<meta name="robots" content="none">
					
					<title>Reset Password</title>
					<link href="style.css" rel="stylesheet" type="text/css">
					<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
				</head>
				<body>

					<div class="requestform">

			      <div id="forpass" class="requestpass">
			        <h1>Reset Password</h1>
			        <p>Reset Your Password</p>
			      </div>

						<form action="/resetpass" method="post">
			        <input type="hidden" name="rcode" value="<?=$_GET['code']?>">
			        <input type="hidden" name="ird" value="<?=$_GET['id']?>">

			        <input type="password" name="userpass" id="userpass" required>
							<input name="submit" type="submit" value="Submit">
						</form>
					</div>

				</body>
			</html>
		<?php
  }
  else {
    die ("NO REQUEST HAS BEEN MADE WITH THAT AUTHORIZATION CODE");
  }

} else {
  header('Location: /');
}
