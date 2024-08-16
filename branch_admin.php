<?php
include 'admin.php';
include 'session_helper.php';


// Check if "branch_id" is not set in the session
if (!isset($_SESSION['branch_id'])) {
    // Redirect to login or handle the case where branch_id is not set
    // For example, you can redirect to the login page or display an error message.
    header("Location: master_login.php");
    exit();
}

?>
