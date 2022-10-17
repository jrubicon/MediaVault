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

$mess = 0;

if (isset($_POST["del_folder"])){
	$syspath = $_POST["delfolder"];
	$deldir = __DIR__."/assets/".$_POST["delfolder"];
	$delobject = array_diff(scandir($deldir), array('.','..'));

	$delsysEntry = $con->prepare("DELETE FROM uploads WHERE folder = ?");
	$delsysEntry->bind_param('s', $syspath);
	$delsysEntry->execute();

	foreach ($delobject as $obj){
		$delpath = $deldir."/".$obj;
		unlink($delpath);
	}
	rmdir($deldir);

	if(file_exists($deldir))
		$mess = 2;
	else
		$mess = 1;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">
		
		<title>Admin Panel - Delete Folders</title>
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
						<a href="/keypanel"><button>View Requests</button></a>
            <a href="/adminviewall"><button>View All Users</button></a>
						<a href="/adminaddmedia"><button>Add Media</button></a>
          </div>

          <div class="DelFolders">
            <h1>Delete Folders</h1>
						<h3>WARNING: WILL DELETE ALL CONTENTS INSIDE</h3>
						<p>Current Folders:</p>
						<ul>
						<?php
            $dir = __DIR__."/assets/*";
            $directories = glob($dir, GLOB_ONLYDIR);
						foreach($directories as $folder) {
							$dirname = basename($folder);
							echo "<li>".$dirname."</li>";
						}
						?>
						</ul>
            <form method="post">
              <label for="delfolder">Delete Folder:</label><br>
              <select id="delfolder" name="delfolder" required>
                <?php
                echo "<option value='none'>none</option>";
                foreach($directories as $folder) {
                  $dirname = basename($folder);
                  echo "<option value='".$dirname."'>".$dirname."</option>";
                }
                ?>
              </select><br>
              <input type="submit" name="del_folder" value="Delete folder">
            </form>
            </div>
          </div>

        </div>
      </div>

			<script>
				var form_mess = <?php echo $mess; ?>;
				switch(form_mess){
					case 1:
						alert("Folder Deleted Successfully.");
						break;
					case 2:
						alert("Folder Could Not Be Deleted.");
						break;
					default:
						//do nothing
						break;
				}
			</script>

      <footer>
      		<h1>Canine Caviar © 2020 - <?php echo date("Y"); ?></h1>
      </footer>

    </div>
  </body>
</html>
