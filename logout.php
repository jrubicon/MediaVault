<?php
session_save_path('../../mediasession');
session_start();
session_destroy();
// Redirect to the login page:
header('Location: /');
?>
