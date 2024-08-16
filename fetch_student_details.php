<?php
include 'admin.php';

if (isset($_POST['contact'])) {
    $contact = $_POST['contact'];

    // Fetch student details from the database based on the contact number
    $studentDetails = getStudentDetailsByContact($conn, $contact);

    // Return the student details as JSON
    echo json_encode($studentDetails);
} else {
    // If contact value is not received, return an error message
    echo json_encode(['error' => 'Contact not received']);
}

// Function to get student details based on the contact number
function getStudentDetailsByContact($conn, $contact) {
    $query = "SELECT student_name, address, parent_name FROM branch_students WHERE contact = '$contact'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'studentName' => $row['student_name'],
            'address' => $row['address'],
            'parentName' => $row['parent_name'],
        ];
    } else {
        // Return an empty array or handle the case when no student is found
        return [];
    }
}
 
?>
