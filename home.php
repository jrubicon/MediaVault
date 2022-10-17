<?php
include "mv_con.php";

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
if(isset($_POST['delbutton'])){
	$delfromsys = $con->prepare("DELETE FROM uploads WHERE id = ?");
	$delfromsys->bind_param('s', $_POST['delItem']);
	$delfromsys->execute();
	$dirpath = __DIR__ . $_POST['delPath'];
	unlink($dirpath);
	header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">

		<title>MediaVault - Home</title>
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
        <div class="productmenu">
					<h1>Product Catalog</h1>
				</div>

				<?php
				$getfolders = $con->prepare("SELECT DISTINCT folder FROM uploads");
				$getfolders->execute();
				$getfolders->store_result();
				$allfolders = array();

				if($getfolders->num_rows){
					$getfolders->bind_result($folder);
					while($getfolders->fetch()){
						array_push($allfolders, $folder);
					}
				?>

				<div class="image-menu">
					<?php
					$i = 1;
					?>
					<button class='tablinks' id='defaultOpen' onclick='opentab(event, "<?= $allfolders[0] ?>")'><?= $allfolders[0] ?></button>
					<?php
					for($i;$i<sizeof($allfolders);++$i){
						?>
						<button class='tablinks' onclick='opentab(event, "<?= $allfolders[$i] ?>")'><?= $allfolders[$i] ?></button>
						<?php
					}
					?>
					<?php
					 //<button class='tablinks' onclick=''>Export Excel</button>
					?>
				</div>

				<div class="productsection">
					<?php
					//Big loop of all folders
					foreach($allfolders as $foldername){
					?>
						<div class="download-tab-content" id="<?= $foldername ?>">
							<h1 style="text-align:center"><?= $foldername ?></h1>
							<div class="productrows">
								<table class="upload_table">
									<tr>
										<?php
										if($_SESSION['adminaccess'] == TRUE){
											echo "<th id='admField'>Admin Del</th>";
											echo "<th id='admField'>Admin Id</th>";
										}
										?>
										<th></th>
										<th>Image</th>
										<th>Name</th>
										<th>SKU</th>
										<th>EXT</th>
										<th>Category</th>
										<th>Upload Date</th>
									</tr>
									<?php
									$getuploads = $con->prepare("SELECT * FROM uploads WHERE folder = ?");
									$getuploads->bind_param('s', $foldername);
									$getuploads->execute();
									$getuploads->store_result();

									if($getuploads->num_rows){
										$getuploads->bind_result($fid, $pathdir, $ufolder, $uext, $uname, $udesc, $ucat, $uploaddate, $numdl, $sku);
										while($getuploads->fetch()){
											?>
											<tr id="row">
												<?php
												if($_SESSION['adminaccess'] == TRUE){
													echo "<td id='admField'>
																	<form method='post'>
																		<input type='hidden' name='delPath' value='".$pathdir."'>
																		<input type='hidden' name='delItem' value='".$fid."'>
																		<input type='submit' name='delbutton' value='Delete'>
																	</form>
																</td>";
													echo "<td id='admField'>".$fid."</td>";
												}
												?>
												<td>
													<a href="<?= $pathdir ?>" download><button>Download</button></a>
												</td>
												<td>
														<?php
														if($uext == "pdf" || $uext == "pptx" || $uext == "xlsx" || $uext == "mp4"){
															?>
															<img class="thumbnails" src="app/File_alt.png" alt="<?= $uname ?>">
															<?php
														}
														else {
															?>
															<img class="thumbnails" src="<?= $pathdir ?>" alt="<?= $uname ?>">
															<?php
														}
														?>
												</td>
												<td><?= $uname ?></td>
												<td><?= $sku ?></td>
												<td><?= $uext ?></td>
												<td><?= $ucat ?></td>
												<td><?= $uploaddate ?></td>
											</tr>
											<?php
										}
									} else {
										echo "<tr><td colspan='6'> No results found. </td></tr>";
									}

									?>
								</table>
							</div>
						</div>
					<?php
					}
					?>
				</div>
				<?php
			} else {
				echo "No folders setup.";
			}
			?>
      </div>

			<script>
				function opentab(evt, tabname){
					var i, tabcontent, tablinks;
					tabcontent = document.getElementsByClassName("download-tab-content");
					for (i = 0; i < tabcontent.length; i++){
						tabcontent[i].style.display = "none";
					}
					tablinks = document.getElementsByClassName('tablinks');
					for (i = 0; i < tablinks.length; i++){
						tablinks[i].className = tablinks[i].className.replace(" ctactive", "");
					}
					document.getElementById(tabname).style.display = "block";
					evt.currentTarget.className += " ctactive";
				}

				var javafolders = <?php echo json_encode($allfolders); ?>;
				javafolders.forEach(function(element){
					document.getElementById(element).style.display = "none";
				});

				document.getElementById("defaultOpen").click();
			</script>
      <footer>
      		<h1>Canine Caviar © 2020 - <?php echo date("Y"); ?></h1>
      </footer>

    </div>
  </body>
</html>
