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
if (isset($_POST["addfolder"])){
	if(!file_exists(__DIR__."/assets/".$_POST["newfolder"])){
		mkdir(__DIR__."/assets/".$_POST["newfolder"]);
		$mess = 4;
	}
	else {
		//folder already exists
		$mess = 3;
	}
}
if (isset($_POST["addmedia"])){
	if($_POST["addMediaFolder"] != "none"){

		$selected_folder = basename($_POST["addMediaFolder"]);
		$serverpath = "/assets/". $selected_folder."/".basename($_FILES["inputfile"]["name"]);
		$targetfile = $_POST["addMediaFolder"] . "/" . basename($_FILES["inputfile"]["name"]);
		$fileParts = pathinfo($targetfile);

		$checkfordup = $con->prepare("SELECT * FROM uploads WHERE pathdir = ? OR name = ?");
		$checkfordup->bind_param('ss', $serverpath, $_POST["filename"]);
		$checkfordup->execute();
		$checkfordup->store_result();

		if($checkfordup->num_rows || file_exists($serverpath)){
			//there is a duplicate already in system.
			$mess = 5;
		}
		else {
			$insertmediaval = $con->prepare("INSERT INTO uploads (pathdir, folder, ext, name, description, category, downloads, sku)
			VALUES (?,?,?,?,?,?,?,?)");
			$dls = 0;
			$insertmediaval->bind_param('ssssssss', $serverpath, $selected_folder, $fileParts["extension"], $_POST["filename"], $_POST["filedesc"], $_POST["filecate"], $dls, $_POST["filesku"]);
			$insertmediaval->execute();

			move_uploaded_file($_FILES["inputfile"]["tmp_name"], $targetfile);
			//success of media addition
			$mess = 2;
		}
	}
	else {
		//no folder chosen
		$mess = 1;
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">
		
		<title>Admin Panel - Add Media</title>
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
						<a href="/admindelfolder"><button>Delete Folders</button></a>
          </div>

          <div class="addMedia">
            <h1>Add Media</h1>
						<?php
						$dir = __DIR__."/assets/*";
						$directories = glob($dir, GLOB_ONLYDIR);
						?>
						<table>
						<form method='post' enctype="multipart/form-data">
							<tr>
								<th><label for="addMediaFolder">Choose a folder:</label></th>
								<th>
								<select id="addMediaFolder" name="addMediaFolder" required>
									<?php
									echo "<option value='none'>none</option>";
									foreach($directories as $folder) {
										$dirname = basename($folder);
										echo "<option value='".$folder."'>".$dirname."</option>";
									}
									?>
								</select>
								</th>
							</tr>
							<tr>
								<th colspan="2"><label for="filename">File Name:</label></th>
							</tr>
							<tr>
								<td colspan="2"><input type="text" id="filename" name="filename" required></td>
							</tr>
							<tr>
								<th colspan="2"><label for="filedesc">File Description:</label></th>
							</tr>
							<tr>
								<td colspan="2"><textarea id="filedesc" name="filedesc" required></textarea><td>
							</tr>
							<tr>
								<th colspan="2"><label for="filecate">File Category:</label></th>
							</tr>
							<tr>
								<td colspan="2"><input type="text" id="filecate" name="filecate" required></td>
							</tr>
							<tr>
								<th colspan="2"><label for="filesku">File SKU:</label></th>
							</tr>
							<tr>
								<td colspan="2"><input type="text" id="filesku" name="filesku"></td>
							</tr>
							<tr>
								<th colspan="2"><input type="file" name="inputfile" required></th>
							</tr>
							<tr>
								<th colspan="2"><input type="submit" name="addmedia" value="Add file"><th>
							</tr>
						</form>
					</table>
          </div>

          <div class="addFolders">
            <h1>Add Folders</h1>
						<p>Current Folders:</p>
						<ul>
						<?php
						foreach($directories as $folder) {
							$dirname = basename($folder);
							echo "<li>".$dirname."</li>";
						}
						?>
						</ul>
						<table>
							<form method="post">
								<tr>
									<th><label for="newfolder">New Folder Name:</label></th>
								</tr>
								<tr>
									<th><input type="text" name="newfolder" required></th>
								</tr>
								<tr>
									<th><input type="submit" name="addfolder" value="Add folder"></th>
								</tr>
							</form>
						</table>
          </div>

        </div>
      </div>

			<script>
				var form_mess = <?php echo $mess; ?>;
				switch(form_mess){
					case 1:
						alert("ERROR: No Folder Chosen.");
						break;
					case 2:
						alert("Media Added Successfully.");
						break;
					case 3:
						alert("ERROR: Folder Already Exists.");
						break;
					case 4:
						alert("Folder Created Successfully.");
						break;
					case 5:
						alert("ERROR: Duplicate Media Detected In System.");
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
