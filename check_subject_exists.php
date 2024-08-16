<?php

include 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $subjectName = $_POST['subjectName'];
    $fees = $_POST['fees'];
    $tax = $_POST['tax'];
    $selectedBranches = $_POST['branches'];


    // Check if at least one branch is selected
    if (empty($selectedBranches)) {
        // Handle the case where no branches are selected
        echo "Please select at least one branch for the subject.";
        exit();
    }

    $subjectExists = false;
foreach ($selectedBranches as $branchId) {
    if (isSubjectExists($conn, $subjectName, $branchId)) {
        $subjectExists = true;
        break; 
    }
}

// If validation passed, call addSubject function
if (!$subjectExists) {
    addSubject($conn, $subjectName, $fees, $tax, $selectedBranches);
} else {

        echo' subject_exists';
}
    
}

function isSubjectExists($conn, $subjectName, $branchId) {
    $query = "SELECT COUNT(*) FROM subjects WHERE subject_name = ? AND branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $subjectName, $branchId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

function addSubject($conn, $subjectName, $fees, $tax, $selectedBranches) {
    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO subjects (subject_name, fees, tax, branch_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if the preparation was successful
    if ($stmt === false) {
        echo "Error preparing the query: " . $conn->error;
        return;
    }
    else 
    {
        echo 'insert_success';
        //header("Location: master_dashboard.php");
    }

    $conn->begin_transaction();

    try {
        // Iterate over selected branches and insert a separate row for each
        foreach ($selectedBranches as $branchId) {
            // Bind parameters
            $stmt->bind_param("sdds", $subjectName, $fees, $tax, $branchId);

            // Execute the prepared statement
            $stmt->execute();

            // Get the subject_id of the recently inserted subject
            $subjectId = $stmt->insert_id;

            // Note: Do not call associateSubjectWithBranches here

        }

        // Call the associateSubjectWithBranches function outside the loop
        associateSubjectWithBranches($conn, $subjectId, $selectedBranches);

        // Commit the transaction
        $conn->commit();

    } catch (mysqli_sql_exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        // Close the statement
        $stmt->close();
    }
}

function associateSubjectWithBranches($conn, $subjectId, $branches) {
    foreach ($branches as $branchId) {
        $sql = "INSERT INTO subject_branches (subject_id, branch_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        // Check if the preparation was successful
        if ($stmt === false) {
            echo "Error preparing the query: " . $conn->error;
            return;
        }

        // Bind parameters
        $stmt->bind_param("ss", $subjectId, $branchId);

        // Execute the prepared statement
        $stmt->execute();

        // Close the statement
        $stmt->close();
    }
}

?>