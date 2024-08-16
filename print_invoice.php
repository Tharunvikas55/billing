<?php
include 'master.php';  // Include your master.php file with necessary database connection and functions

if (isset($_GET['invoice_number'])) {
    $invoiceNumber = $_GET['invoice_number'];

    // Fetch invoice details by invoice number
    $invoiceDetails = getInvoiceDetailsByNumber($conn, $invoiceNumber);

    if (!$invoiceDetails) {
        // Handle the case where the invoice details are not found
        echo "Invoice details not found for the specified invoice number.";
        exit();
    }
} else {
    // Redirect to the master dashboard if the invoice number is not specified
    header("Location: master_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoiceNumber; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
        }

        .container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h1 {
            color: #007bff;
        }

        h4 {
            color: #343a40;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .btn-print {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center mb-4">Punchbiz Learn and Expert Tuition Center</h1>

        <div class="row text-center">
            <div class="col-md-6">
                <h4>Invoice Details:</h4>
                <p><strong>Invoice Number:</strong> <?php echo $invoiceDetails['invoice_number']; ?></p>
                <p><strong>Invoice Date:</strong> <?php echo substr($invoiceDetails['invoice_date'], 0, 10); ?></p>
                <!-- Add other relevant invoice details here -->
            </div>
            <div class="col-md-6">
                <h4>Student Details:</h4>
                <p><strong>Student Name:</strong> <?php echo $invoiceDetails['student_name']; ?></p>
                <p><strong>Contact Number:</strong> <?php echo $invoiceDetails['contact_number']; ?></p>
                <!-- Add other relevant student details here -->
            </div>
        </div>

        <h4 class="mt-4">Subject-wise Details:</h4>
        <?php
        $subjectName = $invoiceDetails['subject_name'];
        $subjectArray = json_decode($subjectName);

        if (is_array($subjectArray) && count($subjectArray) > 0) {
            // Display subject-wise details in a table
            echo '<table class="table table-bordered">';
            echo '<thead class="thead-dark"><tr><th>Subject Name</th><th>Price</th><th>Tax</th><th>Total</th></tr></thead>';
            echo '<tbody>';
            foreach ($subjectArray as $subjectGroup) {
                foreach ($subjectGroup as $subject) {
                    echo '<tr>';
                    echo '<td>' . $subject . '</td>';

                    // Fetch subject details from the database based on the subject name
                    $subjectDetails = getSubjectDetailsByName($conn, $subject);

                    if ($subjectDetails) {
                        // Display subject details
                        echo '<td>' . $subjectDetails['fees'] . '</td>';
                        echo '<td>' . $subjectDetails['tax'] . '</td>';

                        // Calculate total based on price and tax
                        $total = $subjectDetails['fees'] + ($subjectDetails['tax'] / 100) * $subjectDetails['fees'];
                        echo '<td>' . $total . '</td>';
                    } else {
                        // Handle the case where subject details are not found
                        echo '<td colspan="3">Subject details not available.</td>';
                    }

                    echo '</tr>';
                }
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No subject-wise details available.</p>';
        }
        ?>

        
<div class="col-md-11">
    <!-- Right side payment details -->
    <h4 class="text-right">Payment Details:</h4>
    <div class="table-responsive ml-auto" style="max-width: 50%;">
    <table class="table table-bordered">
        <tr>
            <td><strong>Grand Total:</strong></td>
            <td><?php echo $invoiceDetails['grand_total']; ?></td>
        </tr>
        <tr>
            <td><strong>Paid Amount:</strong></td>
            <td><?php echo $invoiceDetails['paid_amount']; ?></td>
        </tr>
        <tr>
            <td><strong>Due Amount:</strong></td>
            <td><?php echo $invoiceDetails['due_amount']; ?></td>
        </tr>
        <tr>
            <td><strong>Invoice Status:</strong></td>
            <td><?php echo $invoiceDetails['invoice_status']; ?></td>
        </tr>
    </table>
</div>

</div>

        <!-- Print button -->
        <div class="text-center mt-4">
            <button class="btn btn-print" onclick="printInvoice()">Print</button>
        </div>
    </div>

    <script>
        function printInvoice() {
            // Trigger the browser's print functionality
            window.print();
        }
    </script>
</body>

</html>
