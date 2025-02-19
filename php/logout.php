<?php
// Start the session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page or home page
header("Location: /USER_DASHBOARD/getstartedPage.php"); // Change this to your desired page
exit();
?>
