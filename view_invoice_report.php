<?php
session_start();
include 'master.php';



if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    // echo '<p>Welcome, ' . $username . '!</p>';
    // ... (rest of the code)
} else {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}


if (isset($_GET['branch'])) {
    $branchName = $_GET['branch'];

    // Fetch branch ID by name
    $branchId = getBranchIdByName($conn, $branchName);

    if ($branchId) {
        // Check if search data is submitted
        $search = isset($_POST['search']) ? $_POST['search'] : '';

        // Check if start and end date data is submitted
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';


    if (!empty($startDate) && !empty($endDate)) {
        // If both start and end dates are provided, use date range search
        $invoiceDetails = getInvoiceDetailsByBranchWithSearch($conn, $branchId, $startDate, $endDate, '');
    } else {
        // If only common search term is provided, use common search
        $invoiceDetails = getInvoiceDetailsByBranchWithSearch($conn, $branchId, '', '', $search);
    }
    } else {
        // Handle the case where the branch ID is not found
        echo "Branch ID not found for the selected branch name.";
        exit();
    }
} else {
    // Redirect to the master dashboard if the branch is not specified
    header("Location: master_dashboard.php");
    exit();
}

function getInvoiceDetailsByBranchWithSearch($conn, $branchId, $startDate, $endDate, $search)
{
    // Use prepared statements to avoid SQL injection
    $query = "SELECT * FROM invoices WHERE branch_id = ?";

    // Initialize parameter types string and parameters array
    $paramTypes = 'i';
    $params = [$branchId];

    // Add search filter to the query if the search input is not empty
    if (!empty($search)) {
        $search = '%' . mysqli_real_escape_string($conn, $search) . '%';
        $query .= " AND (invoice_number LIKE ? OR student_id LIKE ? OR student_name LIKE ? OR contact_number LIKE ? OR subject_name LIKE ? OR due_amount LIKE ? OR invoice_status LIKE ?)";
        
        // Add search parameters to parameter types string and parameters array
        $paramTypes .= 'sssssss';
        $params = array_merge($params, array_fill(0, 7, $search));
    }

    // Add date range filter to the query if start and end dates are provided
    if (!empty($startDate) && !empty($endDate)) {
        $query .= " AND invoice_date BETWEEN ? AND ?";
        
        // Add date parameters to parameter types string and parameters array
        $paramTypes .= 'ss';
        $params[] = $startDate;
        $params[] = $endDate;
    }

    $query .= " ORDER BY invoice_date DESC";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        // Log the SQL error for debugging
        echo "Error preparing the query: " . mysqli_error($conn);
        exit();
    }

    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, $paramTypes, ...$params);

    // Execute the statement
    $result = mysqli_stmt_execute($stmt);

    if ($result === false) {
        // Log the SQL error for debugging
        echo "Error executing the query: " . mysqli_error($conn);
        exit();
    }

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    $invoiceDetails = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $invoiceDetails[] = $row;
    }

    // Close the statement
    mysqli_stmt_close($stmt);

    return $invoiceDetails;
}




// Function to check if a user is a branch admin
function isBranchAdmin($conn, $branchId, $username)
{
    $query = "SELECT * FROM branch_admins WHERE branch_id = ? AND username = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        // Log the SQL error for debugging
        echo "Error preparing the query: " . mysqli_error($conn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $branchId, $username);

    $result = mysqli_stmt_execute($stmt);

    if ($result === false) {
        // Log the SQL error for debugging
        echo "Error executing the query: " . mysqli_error($conn);
        exit();
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Close the statement
    mysqli_stmt_close($stmt);

    return $row !== null;
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Report - <?php echo $branchName; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./style/view_invoice_report.css">
    <style>
        body{
    background-image: url('./assests/white.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    height: auto;
    width: auto;
    overflow: hidden;
}
  /* Add glass morphism effect to the table container */
  .table-container {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
}
.button-container {
    position: fixed;
    top: 100px;
    right: 15rem;
}
/* Set the table background color and style */
.table {
    background: white;
    opacity :0.8;
    /* background: rgba(255, 255, 255, 0.1); */
    border-radius: 10px;
}

/* Set the text color for the table headers */
.table thead th {
    color: white;
}

/* Set the text color for the table rows */
.table tbody tr {
    color: black;
}
body {
    overflow: auto;
}

/* Set the background color for the table rows */
.table tbody tr:nth-child(even) {
    background-color: rgba(255, 255, 255, 0.05);
}
/* Add bold and color styles for table text */
.bold-color {
    font-weight: bold;
    color: black; /* Replace 'your_color_code' with the desired color code */
}

        </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Invoice Report - <?php echo $branchName; ?></h1>

      <!-- Add search bar -->
      <form method="post" class="form-inline mb-3">
    <!-- Common search -->
    <div class="form-group mr-3">
        <input type="text" class="form-control" id="search" name="search" placeholder="Search">
    </div>
    <!-- Start and end date search -->
    <div class="form-group mr-3">
        <input type="date" class="form-control" id="startDate" name="startDate" placeholder="Start Date">
    </div>
    <div class="form-group mr-3">
        <input type="date" class="form-control" id="endDate" name="endDate" placeholder="End Date">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <button type="button" class="btn btn-secondary ml-2" onclick="resetSearch()">Reset</button>
</form>

     <form action ="export_pdf.php" method ="post" class ="mb-2">  

     <input style="position: absolute; top: 100px; right: 120px;" type="submit" name="submit" class="btn btn-outline-danger mr-2" value="Export to PDF" />
     </form>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Branch ID</th>
                    <th>Invoice Number</th>
                    <th>Student ID</th>
                    <th>Invoice Time</th>
                    <th>Invoice Date</th>
                    <th>Subject Name</th>
                    <th>Student Name</th>
                    <th>Contact</th>
                    <th>Paid Amount</th>
                    <th>Balance Amount</th>
                    <th>Due Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoiceDetails as $invoice) : ?>
                    <tr>
                        <td><?php echo $invoice['branch_id']; ?></td>
                        <td><?php echo $invoice['invoice_number']; ?></td>
                        <td><?php echo $invoice['student_id']; ?></td>
                        <td><?php echo substr($invoice['time'],11,19); ?></td>
                        <td><?php echo substr($invoice['invoice_date'], 0, 10); ?></td>
                        <td>
                            <?php
                            $subjectName = $invoice['subject_name'];
                            $subjectArray = json_decode($subjectName);

                            if (is_array($subjectArray) && count($subjectArray) > 0) {
                                echo '<ul>';
                                foreach ($subjectArray as $subjectGroup) {
                                    foreach ($subjectGroup as $subject) {
                                        echo '<li>' . $subject . '</li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo $subjectName;
                            }
                            ?>
                        </td>
                        <td><?php echo $invoice['student_name']; ?></td>
                        <td><?php echo $invoice['contact_number']; ?></td>
                        <td><?php echo $invoice['paid_amount']; ?></td>
                        <td><?php echo $invoice['balance_amount']; ?></td>
                        <td><?php echo $invoice['due_amount']; ?></td>
                        <td><?php echo $invoice['invoice_status']; ?></td>
                        <td>
                            <button class="btn btn-success" onclick="printInvoice('<?php echo $invoice['invoice_number']; ?>')">Print</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


        <div class="button-container" style="position: absolute; top: 20px; right: 120px;">
        <?php
        // Display the appropriate button based on the username
        if (isBranchAdmin($conn, $branchId, $username)) {
            echo '<a href="branch_admin_dashboard.php" class="btn btn-warning">Back to admin Dashboard</a>';
        } else {
            echo '<a href="master_dashboard.php" class="btn btn-warning">Back to Master Dashboard</a>';
        }
        ?>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function printInvoice(invoiceNumber) {
            // Redirect to the print_invoice.php with the invoice number
            window.open('print_invoice.php?invoice_number=' + invoiceNumber, '_blank');
        }
        function resetSearch() {
            // Reset the search input value to empty
            document.getElementById('search').value = '';

            // Submit the form to show all details
            document.querySelector('form').submit();
        }
    </script>
</body>

</html>


