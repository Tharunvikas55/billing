<?php
include 'config.php';
include 'session_helper.php';

// if (!function_exists('addStudent')) {
//     function addStudent($conn, $branchID, $studentName, $contact, $contact2, $address, $parentName) {
//         // Check if the student already exists
//         $existingQuery = "SELECT * FROM branch_students WHERE branch_id = '$branchID' AND student_name = '$studentName' AND parent_name = '$parentName' AND (contact = '$contact' OR contact2 = '$contact')";
//         $existingResult = $conn->query($existingQuery);

//         if ($existingResult->num_rows > 0) {
//             // Student with the same details already exists
//             echo '<script>alert("A student with these details already exists.");</script>';
//         } else {
//             // Insert the new student record
//             $sql = "INSERT INTO branch_students (branch_id, student_name, contact, contact2, address, parent_name) VALUES ('$branchID', '$studentName', '$contact', '$contact2', '$address', '$parentName')";
//             if ($conn->query($sql) === TRUE) {
//                 // Redirect to branch_admin_dashboard.php after successful insertion
//                 header("Location: branch_admin_dashboard.php");
//                 exit(); // Ensure that the script stops executing after the header redirect
//             } else {
//                 // Handle the case where the insertion fails
//                 echo "Error: " . $sql . "<br>" . $conn->error;
//             }
//         }
//     }
// }



?>
