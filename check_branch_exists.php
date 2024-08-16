<?php

include 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $branchName = $_POST['branchName'];

    $branchCreated = createBranch($conn, $branchName);
    if (!$branchCreated) {
        // Branch creation failed, echo the error message directly into the paragraph tag
        echo 'branch_exists';
    } else {
       
        echo'insert_success';
    }
}


// Create Branch
function createBranch($conn, $branchName) {
    // Check if the branch name already exists
    $checkExistingBranch = "SELECT * FROM branches WHERE branch_name = '$branchName'";
    $result = $conn->query($checkExistingBranch);

    if ($result->num_rows > 0) {
        return false;
    } else {
        // Branch name doesn't exist, proceed with inserting
        $sql = "INSERT INTO branches (branch_name) VALUES ('$branchName')";
        $conn->query($sql);
        return true;
    }
}
?>