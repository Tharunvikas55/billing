<?php
// Include your database connection file
include 'config.php'; // Adjust the filename as per your setup

// Function to check if a student already exists
function isStudentExists($conn, $branchID, $studentName, $contact, $contact2, $parentName, $address) {
    // Check if either contact number matches, and also check parent name and address
    $query = "SELECT COUNT(*) FROM branch_students WHERE branch_id = ? AND ((contact = ? OR contact = ?) OR (contact2 = ? OR contact2 = ?)) AND parent_name = ? AND address = ? AND student_name = ?";
    $stmt = $conn->prepare($query);
    // Bind parameters
    $stmt->bind_param("ssssssss", $branchID, $contact, $contact, $contact2, $contact2, $parentName, $address, $studentName);
    // Execute query
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

// Function to check if contact details are unique
function isContactUnique($conn, $contact, $contact2, $parentName, $address) {
    // Check if there are no students with the same contact numbers but different address or parent name
    $query = "SELECT COUNT(*) FROM branch_students WHERE (contact = ? OR contact = ? OR contact2 = ? OR contact2 = ?) AND (parent_name != ? OR address != ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $contact, $contact2, $contact, $contact2, $parentName, $address);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count === 0;
}

// Assuming you have a database connection established

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $branchID = $_POST['branch_id'];
    $studentName = $_POST['studentName'];
    $contact = $_POST['contact'];
    $contact2 = $_POST['contact2'];
    $parentName = $_POST['parentName'];
    $address = $_POST['address'];

    // Check if contact details are unique
    if (!isContactUnique($conn, $contact, $contact2, $parentName, $address)) {
        echo 'contact_not_unique';
    } elseif (isStudentExists($conn, $branchID, $studentName, $contact, $contact2, $parentName, $address)) {
        echo 'student_exists';
    } else {
        // Insert the student details into the database
        $insertQuery = "INSERT INTO branch_students (branch_id, student_name, contact, contact2, address, parent_name) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssss", $branchID, $studentName, $contact, $contact2, $address, $parentName);
        if ($stmt->execute()) {
            echo 'insert_success';
        } else {
            echo 'insert_failed';
        }
        $stmt->close();
    }
}

?>
