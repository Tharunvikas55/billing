<?php

include 'admin.php';
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the studentName parameter is set in the POST data
    if (isset($_POST['studentName'])) {

        // Prepare a SQL statement to fetch student details based on the selected student name
        $stmt = $conn->prepare("SELECT address, parent_name FROM branch_students WHERE student_name = ?");
        $stmt->bind_param("s", $_POST['studentName']);
        
        // Execute the statement
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();

        // Check if there is a result
        if ($result->num_rows > 0) {
            // Fetch the row
            $row = $result->fetch_assoc();

            // Return the student details as JSON
            echo json_encode($row);
        } else {
            // If no matching student found, return an empty JSON object
            echo json_encode([]);
        }

        // Close the statement and the database connection
        $stmt->close();
        $conn->close();
    } else {
        // If studentName parameter is not set, return an error message
        echo "Error: Missing studentName parameter";
    }
} else {
    // If the request method is not POST, return an error message
    echo "Error: Only POST requests are allowed";
}
?>
