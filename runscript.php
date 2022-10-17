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
ini_set('display_error', 1);

	//DB connection
$con = db_connection();
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$maindir = __DIR__."/assets/";
$assetFolders =  array_diff(scandir($maindir), array('.','..'));
foreach(glob($maindir.'*', GLOB_ONLYDIR) as $dir) {
    $dirname = basename($dir);
		$innerdir = $maindir . $dirname;
		$imgpaths = "/assets/". $dirname ."/";
		//now scan the innerdir files
		$innerfiles = array_diff(scandir($innerdir), array('.','..'));

		foreach($innerfiles as $filename){
			$fileparts = pathinfo($filename);
			$uploadpath = $imgpaths . $filename;
			$upload = $con->prepare("INSERT INTO uploads (pathdir, folder, ext) VALUES (?,?,?)");
			$upload->bind_param('sss', $uploadpath, $dirname, $fileparts['extension']);
			$upload->execute();
			$upload->close();
		}

}

?>
