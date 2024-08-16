
<?php
include 'admin.php';
if (isset($_POST['invoiceno'])) {
    $invoiceno = $_POST['invoiceno'];

    // Fetch student details from the database based on the contact number
    $studentDetails = getStudentDetailsByInvoice($conn, $invoiceno);

    

    // Return the student details as JSON
    echo json_encode($studentDetails);
} else {
    // If contact value is not received, return an error message
    echo json_encode(['error' => 'invoiceno not received']);
}



// Function to get student details based on the contact number
function getStudentDetailsByInvoice($conn, $invoiceno) {
    $query = "SELECT student_name, address, contact_number FROM invoices WHERE invoice_number = '$invoiceno'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'studentname' => $row['student_name'],
            'address' => $row['address'],
            'contact' => $row['contact_number'],
            
        ];
    } else {
        // Return an empty array or handle the case when no student is found
        return [];
    }
}

?>