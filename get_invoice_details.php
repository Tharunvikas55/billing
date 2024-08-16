<?php
include 'admin.php';
include 'session_helper.php';

if (isset($_POST['contactNumber']) && isset($_POST['studentName'])) {
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $studentName = mysqli_real_escape_string($conn, $_POST['studentName']);

    // Query to get invoice details sorted by last updated time and date
    $query = "SELECT due_amount, grand_total FROM invoices WHERE contact_number = '$contactNumber' AND student_name ='$studentName' ORDER BY DATE_FORMAT(CONCAT(invoice_date, ' ', time), '%Y-%m-%d %H:%i:%s') DESC LIMIT 1";
    
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = [
            'dueAmount' => $row['due_amount'],
            'grandtotal' => $row['grand_total'],
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Invoice not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
