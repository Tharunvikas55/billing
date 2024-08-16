<?php
include 'admin.php';
include 'session_helper.php';


// Function to get student ID based on contact number and student name
function getStudentId($conn, $contactNumber, $studentName) {
    $result = $conn->query("SELECT id FROM branch_students WHERE (contact = '$contactNumber' or contact2 ='$contactNumber')AND student_name = '$studentName'");

    if ($result === false) {
        die('Error in query: ' . $conn->error);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }

    return null;
}

// Function to get invoice details by contact number and student name
function getInvoiceDetails($conn, $contactNumber, $studentName) {
    $result = $conn->query("SELECT * FROM invoices WHERE contact_number = '$contactNumber' AND student_name = '$studentName'");

    if ($result === false) {
        die('Error in query: ' . $conn->error);
    }

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

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

if (isset($_POST['payDue'])) {
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $studentName = mysqli_real_escape_string($conn, $_POST['studentName']);
    $actualAmount = mysqli_real_escape_string($conn, $_POST['actualAmount']);

    // Get invoice details
    $invoiceDetails = getInvoiceDetails($conn, $contactNumber, $studentName);

      // Get student ID based on contact number and student name
      $studentId = getStudentId($conn, $contactNumber, $studentName);

    if ($invoiceDetails) {
        $dueAmount = $invoiceDetails['due_amount'];
        $grandtotal = $invoiceDetails['grand_total'];
        $parentname = $invoiceDetails['parent_name'];
        $address = $invoiceDetails['address'];
        $subjectNamesJson = $invoiceDetails['subject_name'];
        // Calculate new due amount, balance amount, and status
        if ($actualAmount >= $dueAmount) {
            $newDueAmount = 0;
            $balanceAmount = $actualAmount - $dueAmount;
            $status = 'Paid';
        } else {
            $newDueAmount = $dueAmount - $actualAmount;
            $balanceAmount = 0;
            $status = 'Due';
        }

        $branchId = $invoiceDetails['branch_id'];
        // Set $invoiceDate to the current date
        $invoiceDate = date("Y-m-d");

        // Generate a new invoice number
        $newInvoiceNumber = generateNewInvoiceNumber($conn, $branchId, $invoiceDate);

        // Insert new invoice details
        $insertQuery = "INSERT INTO invoices (invoice_number, branch_id, student_id, student_name, contact_number, parent_name, address, subject_name, paid_amount, due_amount, balance_amount, grand_total, invoice_status, invoice_date, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TIME(CURRENT_TIMESTAMP))";
        $stmt = $conn->prepare($insertQuery);

        if (!$stmt) {
            die('Error in prepare statement: ' . $conn->error);
        }


        $stmt->bind_param("sssssssssssdss", $newInvoiceNumber, $branchId, $studentId, $studentName, $contactNumber, $parentname, $address, $subjectNamesJson, $actualAmount, $newDueAmount, $balanceAmount, $grandtotal, $status, $invoiceDate);
        
        if (!$stmt->execute()) {
            die('Error in execute statement: ' . $stmt->error);
        }

        $stmt->close();

        // Redirect or provide success response
        header("Location: branch_admin_dashboard.php");
        exit();
    } else {
        echo "Error: Invoice not found.";
    }
} else {
    echo "Error: Invalid request.";
}
?>
