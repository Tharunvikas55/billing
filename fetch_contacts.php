<?php
session_start();

// Include config.php to establish database connection
include 'config.php';

// Check if branch ID is set in session
if (!isset($_SESSION['branch_id'])) {
    echo json_encode(['error' => 'Branch ID not set in session']);
    exit; // Stop script execution
}

// Fetch contact numbers for students in the specified branch
$branchID = $_SESSION['branch_id'];

// Fetching POST data
$searchContact = isset($_POST['searchContact']) ? $_POST['searchContact'] : ''; // Check if searchContact is set
$branchId = isset($_POST['branchId']) ? $_POST['branchId'] : '';

try {
    // Query to fetch student name based on contact numbers present in the specified branch
    $query = "SELECT student_name FROM branch_students WHERE (contact = ? OR contact2 = ?) AND branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $searchContact, $searchContact, $branchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $contacts = $result->fetch_all(MYSQLI_ASSOC);

    if ($contacts) {
        // If contacts are found, return them as JSON response
        echo json_encode($contacts);
    } else {
        // If no contacts are found, return a warning message
        echo json_encode(['message' => 'Contact number not available in this branch']);
    }
} catch (Exception $e) {
    // Handle database errors
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
