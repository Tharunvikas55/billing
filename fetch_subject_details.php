<?php
include 'admin.php';

if (isset($_POST['subjectName'])) {
    $subjectName = $_POST['subjectName'];

    // Fetch subject details from the database based on the subject name
    $subjectDetails = getSubjectDetailsByName($conn, $subjectName);

    // Return the subject details as JSON
    echo json_encode($subjectDetails);
} else {
    // If subject name value is not received, return an error message
    echo json_encode(['error' => 'Subject name not received']);
}

// Function to get subject details based on the subject name
function getSubjectDetailsByName($conn, $subjectName) {
    $query = "SELECT fees, tax FROM subjects WHERE subject_name = '$subjectName'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'price' => $row['fees'],
            'tax' => $row['tax'],
        ];
    } else {
        // Return an empty array or handle the case when no subject is found
        return [];
    }
}
?>
