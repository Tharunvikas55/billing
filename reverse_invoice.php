<?php

include 'admin.php';
include 'session_helper.php';

$branchId = $_SESSION['branch_id'];

// Function to generate a new invoice number
function generateNewInvoiceNumber($conn, $branchId, $invoiceDate) {
    $invoiceDateFormatted = date("Ymd", strtotime($invoiceDate));
    
    // Get the latest sequence number for the given branch_id and invoice_date
    $result = $conn->query("SELECT MAX(SUBSTRING(invoice_number, -2)) AS max_sequence FROM invoices WHERE branch_id = '$branchId' AND invoice_date = '$invoiceDate'");

    if ($result === false) {
        die('Error in query: ' . $conn->error);
    }

    $row = $result->fetch_assoc();
    $maxSequence = ($row['max_sequence'] != null) ? $row['max_sequence'] : 0;

    // If the invoice_date is different, start a new sequence from 01
    if ($maxSequence >= 99) {
        $newSequence = 1;
    } else {
        // Increment the sequence number
        $newSequence = $maxSequence + 1;
    }

    // Format the new sequence number with leading zeros
    $formattedSequence = str_pad($newSequence, 2, '0', STR_PAD_LEFT);

    // Combine branch_id, invoice_date, and formatted sequence to create the new invoice number
    $newInvoiceNumber = $branchId . $invoiceDateFormatted . $formattedSequence;

    return $newInvoiceNumber;
}






if (isset($_POST['reverseInvoice'])) {
    // Fetch data from the form
    $invoice_number = $_POST['invoiceno'];
    $invoiceDate = $_POST['invoiceDate'];
    $grandTotal = $_POST['grandTotal'];
    $paidAmount = $_POST['paidAmount'];
    $balanceAmount =$_POST['balanceAmount'];
    $due = abs($_POST['due']);
    $subjectNamesString = json_encode($_POST['subjectName']);


    // Generate a new invoice number
    $newInvoiceNumber = generateNewInvoiceNumber($conn, $branchId, $invoiceDate);

    // $subjectTotalAmounts = $_POST['total'];

    // Calculate due amount
    // $dueamount = ($grandTotal - $paidAmount);
    // $balance_amount = 0;

    // // If paid amount is greater than grand total, update paid amount and set due amount to 0
    // if ($paidAmount > $grandTotal) {
    //     $balance_amount = $paidAmount - $grandTotal;
    //     $dueamount = 0;
    // }

    // Set invoice status based on due amount
    // $invoiceStatus = ($due == 0) ? 'Paid' : 'Due';

    if ($due == 0) {
        if ($balanceAmount > 0) {
            $invoiceStatus = 'Advance paid'; // Consider it as an advance payment
        } else {
            $invoiceStatus = 'Paid';
        }
    } else {
        $invoiceStatus = 'Due';
    }
    
    // Debug information
    echo "Debug: invoice_number=$invoice_number, balanceAmount=$balanceAmount, due=$due, invoiceStatus=$invoiceStatus";
    
    // Assuming you have an 'invoices' table with appropriate column names, update query would look like this:
    $updateQuery = "UPDATE invoices SET 
                    invoice_date = CAST('$invoiceDate' AS DATE),
                    invoice_number = '$newInvoiceNumber',
                    grand_total = '$grandTotal',
                    paid_amount = '$paidAmount',
                    subject_name = '$subjectNamesString',  -- Update column name accordingly
                    balance_amount = '$balanceAmount',   -- Update column name accordingly
                    due_amount = '$due',
                    invoice_status = '$invoiceStatus',
                    time = CURRENT_TIME()
                    WHERE invoice_number = ?";  // Fix the typo in 'invoive_number'

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('s', $invoice_number);

    // Execute the update query
    if ($stmt->execute()) {
        echo "Invoice updated successfully!";
    } else {
        echo "Error updating invoice: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
