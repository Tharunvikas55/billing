<?php
// Include your database connection code or any required functions
include 'master.php';

// Check if the branchName parameter is set in the POST request
if (isset($_POST['branchName'])) {
    $branchName = $_POST['branchName'];

    // Query to fetch the branch_id based on the branch_name
    $query = "SELECT branch_id FROM branches WHERE branch_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchName);
    $stmt->execute();
    $stmt->bind_result($branchId);
    $stmt->fetch();
    $stmt->close();

    // Return the branch_id without quotes
    echo $branchId;
} else {
    // Handle the case where the branchName parameter is not set
    echo 'error: Branch name not provided';
}
?>

