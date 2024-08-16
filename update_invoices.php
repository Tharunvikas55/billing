<?php
include 'admin.php';

// Fetch all unpaid invoices that are 30 seconds or older (for demonstration purposes)
$sql = "SELECT * FROM invoices WHERE (invoice_status = 'Due' OR invoice_status = 'Paid') AND invoice_date <= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $invoiceID = $row['id'];
        $dueAmount = $row['due_amount'];
        $grandTotal = $row['grand_total'];
        $balance = $row['balance_amount'];
        if($balance==0)
        {
         // Calculate new due amount after adding the grand total
         $newDueAmount = $dueAmount + $grandTotal;
        }
        else{
             if($balance > $grandTotal)
             {
                $balance -=$grandTotal;
             }
             else{
                $newDueAmount = $grandTotal-$balance;
                $balance=0;
             }
            
         // $remaining_balance = abs($balance -$balrem);
        }
        // Update the invoice with the new due amount and set status to 'Paid' if new due amount is 0
        $updateSql = "UPDATE invoices SET due_amount = $newDueAmount, balance_amount =$balance, invoice_status = " . (($newDueAmount == 0) ? "'Paid'" : "'Due'") . " WHERE id = $invoiceID";

        if ($conn->query($updateSql) === FALSE) {
            echo "Error updating invoice with ID $invoiceID: " . $conn->error . "\n";
        }
    }
} else {
    // No invoices to update
   // echo "No invoices to update.\n";
}

// Note: Do not close the connection here, so it remains open for further use.
// $conn->close(); // Remove this line
?>
