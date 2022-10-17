<?php
session_save_path('../../mediasession');
session_start();
if (isset($_SESSION['loggedin'])) {
	header('Location: /home');
	exit();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex">
		<meta name="robots" content="none">

		<title>Vault Login</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>
		<div class="login">
			<div class="vaultlogo">
				<img src="app/vaultimg.png" alt="Canine Caviar Media Vault">
				<h1>CC Media Vault Login</h1>
			</div>
			<form action="/authenticate" method="post">
				<div>
					<label for="username">
						<i class="fas fa-user"></i>
					</label>
					<input type="text" name="username" placeholder="Username" id="username" required>
				</div>
				<div>
					<label for="password">
						<i class="fas fa-lock"></i>
					</label>
					<input type="password" name="password" placeholder="Password" id="password" required>
				</div>
				<div>
					<input type="submit" value="Login">
				</div>
			</form>
			<p>
				<a style="color: blue; text-decoration: none" href="/requestaccess">No access? Request it here</a><br><br>
				<a style="color: blue; text-decoration: none" href="/forgotpassword">Forgot Password?</a>
			</p>
		</div>
	</body>
</html>
