<?php

if(isset($_POST['submit'])){

  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = $_POST['databaseuser'];
  $DATABASE_PASS = $_POST['databasepass'];
  $DATABASE_NAME = $_POST['databasename'];
  $ADMINUSER = $_POST['adminuser'];
  $ADMINPASS = password_hash($_POST['adminpass'], PASSWORD_DEFAULT);
  $ADMINNAME = $_POST['adminname'];
  $ADMINCOMPANY = $_POST['admincomp'];
  $ADMINEMAIL = $_POST['adminemail'];
  $ADMINIP = $_SERVER['REMOTE_ADDR'];
  $ADMINROLE = "admin";

  $dbcon = "<?php function db_connection(){
    \$DATABASE_HOST = '".$DATABASE_HOST."';
    \$DATABASE_USER = '".$DATABASE_USER."';
    \$DATABASE_PASS = '".$DATABASE_PASS."';
    \$DATABASE_NAME = '".$DATABASE_NAME."';
    // Try and connect using the info above.
    \$con = mysqli_connect(\$DATABASE_HOST, \$DATABASE_USER, \$DATABASE_PASS, \$DATABASE_NAME);
    return \$con;
  }";
  file_put_contents('mv_con.php', $dbcon);

  if(!file_exists("/assets")){
    mkdir("/assets", 0777, true);
  }

  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
  if ( mysqli_connect_errno() ) {
  	// If there is an error with the connection, stop the script and display the error.
  	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  $createacc_table = $con->prepare('CREATE TABLE accounts (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255),
    username VARCHAR(255),
    password VARCHAR(255),
    email VARCHAR(255),
    company VARCHAR(255),
    ip VARCHAR(255),
    lastLogin TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role VARCHAR(5),
    PRIMARY KEY (id)
    )');
  $createacc_table->execute();
  $createacc_table->close();

  $createsub_table = $con->prepare('CREATE TABLE submissions (
    id INT NOT NULL AUTO_INCREMENT,
    company VARCHAR(255),
    name VARCHAR(255),
    email VARCHAR(255),
    username VARCHAR(255),
    password VARCHAR(255),
    ctime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(255),
    PRIMARY KEY (id)
    )');
  $createsub_table->execute();
  $createsub_table->close();

  $createcon_table = $con->prepare('CREATE TABLE connections (
    id INT NOT NULL AUTO_INCREMENT,
    remote_address VARCHAR(255),
    failed_logins int(11),
    blacklisted int(1),
    PRIMARY KEY (id)
    )');
  $createcon_table->execute();
  $createcon_table->close();

  $uploads_table = $con->prepare('CREATE TABLE uploads (
    id INT NOT NULL AUTO_INCREMENT,
    pathdir VARCHAR(255),
    folder VARCHAR(255),
    ext VARCHAR(255),
    name VARCHAR(255),
    description TEXT,
    category VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    downloads int(11),
    sku int(11),
    PRIMARY KEY (id)
    )');
  $uploads_table->execute();
  $uploads_table->close();

  $recov_table = $con->prepare('CREATE TABLE accountrecovery (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255),
    username VARCHAR(255),
    email VARCHAR(255),
    codegen int(11),
    dateofrequest TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
    )');
  $recov_table->execute();
  $recov_table->close();

  $insertinto = $con->prepare('INSERT INTO accounts (name, username, password, email, company, ip, role) VALUES (?,?,?,?,?,?,?)');
  $insertinto->bind_param('sssssss', $ADMINNAME, $ADMINUSER, $ADMINPASS, $ADMINEMAIL, $ADMINCOMPANY, $ADMINIP, $ADMINROLE);
  $insertinto->execute();
  $insertinto->close();

  if (!file_exists('../../mediasession')){
      mkdir('../../mediasession',0777, true);
  }

  class DeleteOnExit {
     function __destruct() {
        unlink(__FILE__);
     }
  }
  $delete_on_exit = new DeleteOnExit();
  header('Location: /');

//setup tables accounts, submissions, connections
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Setup</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>

		<div class="requestform">
			<form method="post">
        <h1>Setup</h1>
        <div style="margin-top: 20px;color:white;width:100%;background-color:#3274d6;">
          <p> Start by setting up a mySQL database and entering the name, user, and password</p>
        </div>
        <label for="databasename">
          Database Name
				</label>
        <input type="text" name="databasename" id="databasename" required>
				<label for="databaseuser">
          Database User
        </label>
        <input type="text" name="databaseuser" id="databaseuser" required>
        <label for="databasepass">
          Database Password
				</label>
        <input type="password" name="databasepass" id="databasepass" required>
        <div style="margin-top: 20px;color:white;width:100%;background-color:#3274d6;">
          <p>Setup an administrator account</p>
        </div>
        <label for="admincomp">
          Admin Company
  			</label>
        <input type="text" name="admincomp" id="admincomp" required>
        <label for="adminname">
          Admin Name
  			</label>
        <input type="text" name="adminname" id="adminname" required>
        <label for="adminuser">
          Admin Username
				</label>
        <input type="text" name="adminuser" id="adminuser" required>
        <label for="adminpass">
          Admin Password
				</label>
        <input type="password" name="adminpass" id="adminpass" required>
        <label for="adminemail">
          Admin Email
				</label>
        <input type="email" name="adminemail" id="adminemail" required>

        <input name="submit" type="submit" value="Submit">
			</form>
		</div>

	</body>
</html>
