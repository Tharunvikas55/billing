<?php
include 'config.php';

// Master Login
function masterLogin($conn, $username, $password) {
    $sql = "SELECT * FROM master WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    return ($result->num_rows == 1);
}

// Admin Login
function adminLogin($conn, $username, $password) {
    $sql = "SELECT branch_id FROM branch_admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Fetch the branch_id from the result
        $row = $result->fetch_assoc();
        $branch_id = $row['branch_id'];

        // Set the branch_id in the session
        $_SESSION['branch_id'] = $branch_id;

        // Return success status and branch_id
        return array('success' => true, 'branch_id' => $branch_id);
    } else {
        // Return failure status
        return array('success' => false);
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




function addSubject($conn, $subjectName, $fees, $tax, $selectedBranches) {
    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO subjects (subject_name, fees, tax, branch_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if the preparation was successful
    if ($stmt === false) {
        echo "Error preparing the query: " . $conn->error;
        return;
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






// Get Branches for Master
function getMasterBranches($conn) {
    $sql = "SELECT * FROM branches";
    $result = $conn->query($sql);

    $branches = array();
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }

    return $branches;
}

// Get Master Dashboard Data
function getMasterDashboardData($conn) {
    $totalStudentsResult = $conn->query("SELECT COUNT(*) FROM branch_students");
    $totalSubjectsResult = $conn->query("SELECT COUNT(*) FROM subjects");
    $totalDueAmountResult = $conn->query("SELECT SUM(total) FROM invoices WHERE status = 'not_paid'");
    $totalInvoiceAmountResult = $conn->query("SELECT SUM(grand_total) FROM invoices");

    // Check for query execution errors
    if (!$totalStudentsResult || !$totalSubjectsResult || !$totalDueAmountResult || !$totalInvoiceAmountResult) {
        // Handle the error and return an empty array
        echo "Error executing one or more queries: " . $conn->error;
        return array();
    }

    $totalStudents = $totalStudentsResult->fetch_row()[0];
    $totalSubjects = $totalSubjectsResult->fetch_row()[0];
    $totalDueAmount = $totalDueAmountResult->fetch_row()[0];
    $totalInvoiceAmount = $totalInvoiceAmountResult->fetch_row()[0];

    return array(
        'totalStudents' => $totalStudents,
        'totalSubjects' => $totalSubjects,
        'totalDueAmount' => $totalDueAmount,
        'totalInvoiceAmount' => $totalInvoiceAmount
    );
}



// Handle Create Branch Form Submission
// if (isset($_POST['createBranch'])) {
//     $branchName = $_POST['branchName'];

//     createBranch($conn, $branchName);
//     header("Location: master_dashboard.php");
//     exit();
// }


// Function to get all students from all branches
function getAllStudents($conn) {
    $sql = "SELECT * FROM branch_students";
    $result = $conn->query($sql);

    $students = array();

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    return $students;
}

// Function to get branch name by branch ID
function getBranchName($conn, $branchID) {
    $sql = "SELECT branch_name FROM branches WHERE id = $branchID";
    $result = $conn->query($sql);

    // Check if the query was successful
    if ($result === false) {
        // Handle query error (replace this with appropriate error handling)
        return "Error: " . $conn->error;
    }

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['branch_name'];
    }

    // Return a default value if no rows are found
    return "N/A";
}

// Update Student Function
function updateStudent($conn, $studentID, $editStudentName, $editContact, $editAddress, $editParentName) {
    $studentID = $conn->real_escape_string($studentID);
    $editStudentName = $conn->real_escape_string($editStudentName);
    $editContact = $conn->real_escape_string($editContact);
    $editAddress = $conn->real_escape_string($editAddress);
    $editParentName = $conn->real_escape_string($editParentName);

    $sql = "UPDATE branch_students SET student_name = '$editStudentName', contact = '$editContact', address = '$editAddress', parent_name = '$editParentName' WHERE id = $studentID";

    // Execute the query
    $result = $conn->query($sql);

    // Check if the query was successful
    if ($result === false) {
        // Handle query error (replace this with appropriate error handling)
        return "Error: " . $conn->error;
    }

    // Return success message or result as needed
    return "Student updated successfully";
}

// Function to delete a student
function deleteStudent($conn, $studentID) {
    $studentID = $conn->real_escape_string($studentID);

    // Delete the student with the given ID
    $conn->query("DELETE FROM branch_students WHERE id = $studentID");
}

function getBranchSubjects($conn, $branchId) {
    $query = "SELECT s.subject_name FROM subjects s
              JOIN subject_branches sb ON s.id = sb.subject_id
              WHERE sb.branch_id = ?";

    // Use prepared statements to avoid SQL injection
    $stmt = $conn->prepare($query);

    // Check if the preparation was successful
    if ($stmt === false) {
        // Handle query error (replace this with appropriate error handling)
        echo "Error preparing the query: " . $conn->error;
        return array();
    }

    // Bind parameters
    $stmt->bind_param("s", $branchId);

    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    $subjects = array();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    // Close the statement
    $stmt->close();

    return $subjects;
}


// Function to get invoice details for a specific branch
function getInvoiceDetailsByBranch($conn, $branchId) {
    $branchId = mysqli_real_escape_string($conn, $branchId);
    $result = $conn->query("SELECT * FROM invoices WHERE branch_id = '$branchId'");
    
    $invoiceDetails = array();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $invoiceDetails[] = $row;
        }
    }

    return $invoiceDetails;
}
// Function to get branch ID by name
function getBranchIdByName($conn, $branchName) {
    // Use prepared statements to prevent SQL injection
    $query = "SELECT branch_id FROM branches WHERE branch_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchName);
    $stmt->execute();
    $stmt->bind_result($branchId);
    
    if ($stmt->fetch()) {
        return $branchId;
    } else {
        return false;
    }
}

// Function to get invoice details by invoice number
function getInvoiceDetailsByNumber($conn, $invoiceNumber) {
    $result = $conn->query("SELECT * FROM invoices WHERE invoice_number = '$invoiceNumber'");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

// Function to get subject details by name
function getSubjectDetailsByName($conn, $subjectName) {
    $result = $conn->query("SELECT * FROM subjects WHERE subject_name = '$subjectName'");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}


// Function to perform the search in the database
function searchInvoices($conn, $branchId, $searchTerm) {
    $query = "SELECT * FROM invoices WHERE branch_id = ? 
              AND (invoice_number LIKE ? OR student_name LIKE ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $branchId, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $invoiceDetails = array();
    while ($row = $result->fetch_assoc()) {
        $invoiceDetails[] = $row;
    }

    $stmt->close();
    
    return $invoiceDetails;
}

?>
