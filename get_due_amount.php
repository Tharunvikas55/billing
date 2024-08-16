<?php

include 'admin.php';

if (isset($_POST['invoiceno'])) {
    $invoiceno = $_POST['invoiceno'];

    // Fetch due amount based on the provided invoice number
    $query = "SELECT due_amount FROM invoices WHERE invoice_number = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $invoiceno);
    $stmt->execute();
    $stmt->bind_result($dueAmount);
    
    // Initialize dueAmount to 0 in case there is no result
    $dueAmount = 0;
    
    if ($stmt->fetch()) {
        // Return the due amount as JSON
        echo json_encode(['dueAmount' => $dueAmount]);
    } else {
        // Handle the case where the query didn't return any results
        echo json_encode(['error' => 'No due amount found for the provided invoice number']);
    }

    $stmt->close();
} else {
    // Handle the case where invoiceno is not set in the POST request
    echo json_encode(['error' => 'Invoice number not provided']);
}

$conn->close();
?>
