<?php

include 'admin.php';

if (isset($_POST['invoiceNumber'])) {
    $invoiceno = $_POST['invoiceNumber'];

    // Fetch due amount based on the provided invoice number
    $query = "SELECT balance_amount FROM invoices WHERE invoice_number = ?";
    
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        // Handle the case where the query preparation failed
        echo json_encode(['error' => 'Failed to prepare the query']);
        exit();
    }

    $stmt->bind_param("s", $invoiceno);
    $stmt->execute();
    $stmt->bind_result($balanceAmount);
    
    if ($stmt->fetch()) {
        // Return the due amount as JSON
        echo json_encode(['balanceAmount' => $balanceAmount]);
    } else {
        // Handle the case where the query didn't return any results
        echo json_encode(['error' => 'No balance amount found for the provided invoice number']);
    }

    $stmt->close();
} else {
    // Handle the case where invoiceno is not set in the POST request
    echo json_encode(['error' => 'Invoice number not provided']);
}

$conn->close();
?>
