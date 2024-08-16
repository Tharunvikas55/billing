<?php
// fetch_subject_details_invoice.php

include 'admin.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you have a database connection
    $invoiceno = $_POST['invoiceno'];

    $subjectDetails = fetchSubjectDetailsByInvoice($conn, $invoiceno);

    // Return the subject details as JSON
    echo json_encode($subjectDetails);
}

function fetchSubjectDetailsByInvoice($conn, $invoiceno) {
    $subjectDetails = array();

$result = $conn->query("SELECT invoices.subject_name, subjects.fees 
FROM invoices
JOIN subjects ON invoices.branch_id = subjects.branch_id
WHERE invoices.invoice_number = '$invoiceno'
  AND JSON_CONTAINS(invoices.subject_name, JSON_ARRAY(subjects.subject_name))");


if (!$result) {
    die("Query failed: " . $conn->error);
}



    while ($row = $result->fetch_assoc()) {
        $subjectDetails[] = $row;
    }

    return $subjectDetails;
}


?>
