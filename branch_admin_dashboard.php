<?php
ob_start();
include 'admin.php';
// include 'update_invoices.php';
include 'session_helper.php';
// Fetch contact numbers for students in the specified branch
$branchID = $_SESSION['branch_id'];
$subjectnames = fetchSubjectNames($conn, $branchID);
// Initialize variables for student details
$studentname = $address = $contact = '';
$price = $tax = '';

// Define $invoiceNumbers variable here
$invoiceNumbers = fetchStudentInvoiceNumbers($conn, $branchID);

function fetchSubjectNames($conn, $branchID) {
    $subjectnames = array();

    $result = $conn->query("SELECT DISTINCT subject_name FROM subjects WHERE branch_id = '$branchID'");
    while ($row = $result->fetch_assoc()) {
        $subjectnames[] = $row['subject_name'];
    }

    return $subjectnames;
}


// Define $invoiceNumbers variable here
$invoiceNumbers = fetchStudentInvoiceNumbers($conn, $branchID);

function fetchStudentInvoiceNumbers($conn, $branchID) {
    $invoiceNumbers = array();

    $result = $conn->query("SELECT DISTINCT invoice_number FROM invoices WHERE branch_id = '$branchID'");
    while ($row = $result->fetch_assoc()) {
        $invoiceNumbers[] = $row['invoice_number'];
    }

    return $invoiceNumbers;
}
function isContactExists($conn, $branchID, $contact) {
    $query = "SELECT COUNT(*) FROM branch_students WHERE contact = ? OR contact2 = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        // Error handling: Output the error message
        echo "Error: " . $conn->error;
        return false; // Return false to indicate an error
    }
    
    $stmt->bind_param("ss", $contact, $contact);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}


if (!function_exists('addStudent')) {
    function addStudent($conn, $branchID, $studentName, $contact, $contact2, $address, $parentName) {
        // Check if the student already exists
        $existingQuery = "SELECT * FROM branch_students WHERE branch_id = '$branchID' AND student_name = '$studentName' AND parent_name = '$parentName' AND (contact = '$contact' OR contact2 = '$contact')";
        $existingResult = $conn->query($existingQuery);

        if ($existingResult->num_rows > 0) {
 echo '<script>';
            echo 'showModalAndDisplayWarnings("A student with these details already exists.");';
            echo '</script>';
        } else {
            // Insert the new student record
            $sql = "INSERT INTO branch_students (branch_id, student_name, contact, contact2, address, parent_name) VALUES ('$branchID', '$studentName', '$contact', '$contact2', '$address', '$parentName')";
            if ($conn->query($sql) === TRUE) {
                // Redirect to branch_admin_dashboard.php after successful insertion
                header("Location: branch_admin_dashboard.php");
                exit(); // Ensure that the script stops executing after the header redirect
            } else {
                // Handle the case where the insertion fails
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

function isStudentExists($conn, $branchID, $studentName, $contact, $contact2, $parentName, $address) {
    // Check if either contact number matches, and also check parent name and address
    $query = "SELECT COUNT(*) FROM branch_students WHERE branch_id = ? AND ((contact = ? OR contact = ?) OR (contact2 = ? OR contact2 = ?)) AND parent_name = ? AND address = ? AND  student_name =?";
    $stmt = $conn->prepare($query);
    // Bind parameters
    $stmt->bind_param("ssssssss", $branchID, $studentName, $contact, $contact2, $contact, $contact2, $parentName, $address);
    // Execute query
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

function isContactUnique($conn, $contact, $contact2, $parentName, $address) {
    // Check if there are no students with the same contact numbers but different address or parent name
    $query = "SELECT COUNT(*) FROM branch_students WHERE (contact = ? OR contact = ? OR contact2 = ? OR contact2 = ?) 
                AND (parent_name != ? OR address != ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $contact, $contact2, $contact, $contact2, $parentName, $address);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count === 0;
}


if (isset($_POST['addStudent'])) {
    $branchID = $_SESSION['branch_id'];
    $studentName = $_POST['studentName'];
    $contact = $_POST['contact'];
    $contact2 = $_POST['contact2'];
    $parentName = $_POST['parentName'];
    $address = $_POST['address'];

    if (!isContactUnique($conn, $contact, $contact2, $parentName, $address)) {
        echo '<script>document.getElementById("warningMessage").textContent = "Another student with the same contact numbers, but different parent name or address, already exists."; showModal();</script>';
    } elseif (isStudentExists($conn, $branchID, $studentName, $contact, $contact2, $parentName, $address)) {
        echo '<script>document.getElementById("warningMessage").textContent = "A student with these details already exists in branch ' . getBranchNameById($conn, $branchID) . '"; showModal();</script>';
    } else {
        // No warnings, proceed with adding the student
        addStudent($conn, $branchID, $studentName, $contact, $contact2, $address, $parentName);
    }
    
    
}










if (isset($_POST['logout'])) {
    // Perform any additional logout actions if needed
    // For example, destroying the session
    session_destroy();

    // Redirect to the login page after logging out
    header("Location: master_login.php");
    exit();
}


// $invoiceDate = date("Y-m-d");
// echo "Invoice Date: " . $invoiceDate;
if (isset($_SESSION['branch_id'])) {
    $branchID = $_SESSION['branch_id'];
    // Print the branch name for the given branch ID
$branchName = getBranchNameById($conn, $branchID);

} else {
    // Handle the case where "branch_id" is not set
    echo "Error: Branch ID is not set in the session.";
    // Redirect or handle accordingly
    // header("Location: master_login.php");
    // exit();
}
// Function to get the branch name by ID
function getBranchNameById($conn, $branchID) {
    $query = "SELECT branch_name FROM branches WHERE branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchID);
    $stmt->execute();
    $stmt->bind_result($branchName);
    $stmt->fetch();
    $stmt->close();

    return $branchName;
}


// Function to get the total number of students for a particular branch
function getTotalStudentsCount($conn, $branchID) {
    $result = $conn->query("SELECT COUNT(*) FROM branch_students WHERE branch_id = '$branchID'");
    return $result->fetch_row()[0];
}



// Display the total number of students for the current branch
$totalStudentsCount = getTotalStudentsCount($conn, $branchID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./style/branch_admin_dash.css"> <!-- Add your custom styles if needed -->
    <style>    
    body {
    background-image: url('./assests/white.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: top center;
    height: auto;
    width: auto;
    overflow: hidden;
    margin: 0; /* Remove default body margin */
    padding: 0; /* Remove default body padding */
}
    </style>
<!-- Include jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>
        /* Custom styles for bigger square buttons */
        .btn-square {
            width: 150px; /* Adjust the width as needed */
            height: 100px; /* Adjust the height as needed */
            font-size: 18px; /* Adjust the font size as needed */
        }

        #subjectDetailsPage form {
        max-width: 800px; /* Adjust the value as needed */
        margin: auto; /* Center the form horizontally */
    }

    #modalreverse{
        max-width: 100%;
    }
    </style>
</head>
<body style="height: 100%; overflow: hidden;">

<div class="container mt-6">

        
        <div class="header text-center">
        <div class="d-flex align-items-center justify-content-between">
        <h1 class="h1">Welcome, <?php echo $branchName; ?> Branch Admin</h1>
        <div class="logout-button">
            <form action="" method="post">
               <button type="submit" class="btn btn-outline-danger btn-sm ml-2" name="logout">Logout</button>
            </form>
       </div>
     </div>
  </div>
  <p id= "successMessage" class="text-danger"></p>
  <div class="fields">
       <div class="row mt-3 justify-content-center">
    <div class="col-md-2 mb-3">
        <button id="addStudentBtn" class="btn btn-primary btn-block btn-square" data-toggle="modal"
            data-target="#addStudentModal">Add Student</button>
    </div>

    <div class="col-md-2 mb-3">
        <form id="generateInvoiceForm" action="invoice_operations.php" method="post">
            <!-- ... -->
            <button type="submit" name="generateInvoice"
                class="btn btn-success btn-block btn-square">Generate Invoice</button>
        </form>
    </div>

    <div class="col-md-2 mb-3">
        <button id="payDueBtn" class="btn btn-warning btn-block btn-square" data-toggle="modal"
            data-target="#payDueModal">Pay Due</button>
    </div>

    <div class="col-md-2 mb-3">
        <button id="reverseInvoiceBtn" class="btn btn-danger btn-block btn-square"
            data-toggle="modal" data-target="#reverseInvoiceModal">Reverse Invoice</button>
    </div>

    <div class="col-md-2 mb-3">
        <a href="view_invoice_report.php?branch=<?php echo urlencode($branchName); ?>"
            class="btn btn-success btn-block btn-square pt-2">View Invoices</a>
     </div>
  </div>

</div>


        </div>
    <?php if (isset($_POST['generateInvoice'])) :?>
        <div class="alert alert-success mt-2" role="alert">
            Invoice generated successfully!
        </div>
    <?php endif; ?>

   

    <?php if (isset($loginError)) : ?>
        <p class="mt-4 text-danger"><?php echo $loginError; ?></p>
    <?php endif; ?>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Warning Message Placeholder -->
                <p id="warningMessage" class="text-danger"></p>

                <!-- Add Student Form -->
                <form id="addStudentForm" action="branch_admin_dashboard.php" method="post" onsubmit="return validateForm()">
                    <input type="hidden" name="branch_id" value="<?php echo isset($_SESSION['branch_id']) ? $_SESSION['branch_id'] : ''; ?>">

                    <div class="form-group">
                        <label for="studentName">Student Name:</label>
                        <input type="text" name="studentName" class="form-control" required oninput="validateStudentName(); validateForm();">
                        <span id="studentNameError" class="error"></span>
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact1:</label>
                        <input type="text" name="contact" class="form-control" required oninput="validateContact(); validateForm();">
                        <span id="contactError" class="error"></span>
                    </div>

                    <div class="form-group">
                        <label for="contact2">Contact2:</label>
                        <input type="text" name="contact2" class="form-control" required oninput="validateContact2(); validateForm();">
                        <span id="contact2Error" class="error"></span>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" name="address" class="form-control" required oninput="validateAddress(); validateForm();">
                        <span id="addressError" class="error"></span>
                    </div>

                    <div class="form-group">
                        <label for="parentName">Parent Name:</label>
                        <input type="text" name="parentName" class="form-control" required oninput="validateParentName(); validateForm();">
                        <span id="parentNameError" class="error"></span>
                    </div>

                    <button type="submit" name="addStudent" class="btn btn-primary" id="addStudentButton">Add Student</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pay Due Modal -->
<div class="modal fade" id="payDueModal" tabindex="-1" role="dialog" aria-labelledby="payDueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payDueModalLabel">Pay Due</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Pay Due Form -->
                <form id="payDueForm" action="pay_due.php" method="post">
                    <div class="form-group">
                        <label for="contactNumber">Contact Number:</label>
                        <input type="text" name="contactNumber" id="contactNumber" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="studentName">Student Name:</label>
                        <input type="text" name="studentName" id="studentName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="grandtotal">Grand Total:</label>
                        <input type="text" name="grandtotal" id="grandtotal" class="form-control" readonly>
                    </div> 

                    <div class="form-group">
                        <label for="dueAmount">Due Amount:</label>
                        <input type="text" name="dueAmount" id="dueAmount" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="actualAmount">Actual Amount:</label>
                        <input type="text" name="actualAmount" id="actualAmount" class="form-control" required>
                        <div id= "duewarning"></div>
                    </div>

                    <button type="submit" name="payDue" class="btn btn-success" id="payDueButton">Pay Due</button>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Reverse Invoice Modal -->
<!-- Reverse Invoice Modal -->
<div class="modal fade" id="reverseInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="reverseInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reverseInvoiceModalLabel">Reverse Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
             <div class="modal-body" id="modalreverse">
                <!-- General Information Page -->
                
                <form id="generalInfoForm" action="reverse_invoice.php" method="post">
                    <div id="generalInfoPage">
                        <!-- Add your fields for general information -->
                        <div class="form-group col-md-4">
                            <label for="invoiceno">Invoice Number:</label>
                            <select class="form-control" name="invoiceno" id="invoiceno" required>
                            <?php foreach ($invoiceNumbers as $invoiceNumber) : ?>
                            <option value="<?php echo $invoiceNumber; ?>"><?php echo $invoiceNumber; ?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="studentName">Student Name:</label>
                            <input type="text" class="form-control" name="studentname" id="studentname" required readonly value="<?php echo $studentname; ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" class="form-control" name="address" id="address" required readonly value="<?php echo $address; ?>">    
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact:</label>
                            <input type="text" class="form-control" name="contact" id="contact" required readonly value="<?php echo $contact; ?>">
                        </div>

                        <!-- Next button to go to the next page -->
                        <button type="button" id="nextToSubjectDetails" class="btn btn-primary">Next</button>
                    </div>

                    <!-- Subject Details Page -->
            <div id="subjectDetailsPage" style="display: none;">

               
                     <!-- Warning Message -->
                  <div class="alert alert-warning mt-3" id="warningMessage" style="display: none;"></div>
                <div class="container-fluid mt-5">
                        <h2>Subject Details</h2>
                            <!-- Invoice Date -->
                           <div class="form-group col-md-4">
                             <label for="invoiceDate">Invoice Date:</label>
                             <input type="date" class="form-control" name="invoiceDate" id="invoiceDate" required>
                          </div>
                          <!-- Subject details rows -->
                          <div id="subjectRowsContainer"> </div>
                           <div class="form-group col-md-12">
                            <button type="button" id="addRow" class="btn btn-secondary">Add Row</button>
                           </div>

                    <div class="container">
                     <div class="row">

                     <div class="form-group">
                        <label for="balanceAmount">Balance Amount:</label>
                        <input type="text" class="form-control" name="balanceAmount" id="balanceAmount" readonly>
                     </div>
                          <!-- Due Amount -->
                         <div class="form-group col-md-4">
                         <label for="due">Due Amount:</label>
                         <input type="text" class="form-control" name="due" id="due" readonly>
                         </div>

                         <!-- Grand Total -->
                         <div class="form-group col-md-4">
                         <label for="grandTotal">Grand Total:</label>
                        <input type="text" class="form-control" name="grandTotal" id="grandTotal" readonly>
                        </div>

                         <!-- Paid Amount -->
                           <div class="form-group col-md-4">
                           <label for="paidAmount">Paid Amount:</label>
                           <input type="text" class="form-control" name="paidAmount" id="paidAmount">
                           </div>
                      </div>
                   </div>

                            <!-- Back button to go back to the General Information page -->
                            <button type="button" id="backToGeneralInfo" class="btn btn-primary mt-2">Back to General Information</button>


                            <div class="col-md-2 mb-3">
                             <button id="paybtn" class="btn btn-warning btn-block btn-square" data-toggle="modal"
                              data-target="#payDueModal" style="height: 35px; width: 120px;">Pay Due</button>
                            </div>
                            <!-- Submit button for subject details page -->
                            <button type="submit" name="reverseInvoice" id="reverseInvoicebtn"  class="btn btn-danger mt-2">Reverse Invoice</button>


             </div>
                </div>
          </form>
               
            </div>
        </div>
    </div>


   <!-- Subject Row Template -->
   <template id="subjectRowTemplate">
    <div class="row">
        <!-- Subject Name -->
        <div class="form-group col-md-3">
            <label for="subjectName">Subject :</label>
            <select class="form-control subject-name" name="subjectName[]" required>
                <?php foreach ($subjectnames as $subjectname) : ?>
                    <option value="<?php echo $subjectname; ?>"><?php echo $subjectname; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Price -->
        <div class="form-group col-md-3">
            <label for="price">Price:</label>
            <input type="text" class="form-control price" name="price[]" required readonly>
        </div>

        <!-- Tax -->
        <div class="form-group col-md-3">
            <label for="tax">Tax:</label>
            <input type="text" class="form-control tax" name="tax[]" required readonly>
        </div>

        <!-- Total -->
        <div class="form-group col-md-3">
            <label for="total">Total:</label>
            <input type="text" class="form-control total" name="total[]" required readonly>
        </div>
    </div>
  </template>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {



    
                document.getElementById('addRow').addEventListener('click', addNewSubjectRow);
                document.getElementById('nextToSubjectDetails').addEventListener('click', showSubjectDetailsPage);
                document.getElementById('backToGeneralInfo').addEventListener('click', showGeneralInfoPage);
                document.getElementById('paidAmount').addEventListener('input', function () {
    
                updateDueAmount();
                });
                
                // Fetch students by invoice on page load
                fetchStudentsByInvoice();
            });
  
           document.getElementById('invoiceno').addEventListener('change', function () {
           // Fetch students by invoice on invoice number change
           fetchStudentsByInvoice();

    
  
});

document.getElementById('paidAmount').addEventListener('input', function () {
    updateDueAmount();
    printBalanceAmount(); // Call the function to print balance amount in the console
});





function showSubjectDetailsPage() {

     // Fetch and update the due amount and button state first
     fetchDueAmountAndUpdateButtons(document.getElementById('invoiceno').value);

    // Hide general info page and show subject details page
    document.getElementById('generalInfoPage').style.display = 'none';
    document.getElementById('subjectDetailsPage').style.display = 'block';

    // Fetch and display subject details based on the selected invoice number
    fetchSubjectDetails();
   
}

function showGeneralInfoPage() {
    // Hide subject details page and show general info page
    document.getElementById('subjectDetailsPage').style.display = 'none';
    document.getElementById('generalInfoPage').style.display = 'block';
}

function fetchStudentsByInvoice() {
    var invoiceno = document.getElementById('invoiceno').value;

    // Make an AJAX request to fetch students based on invoice number
    $.ajax({
        type: 'POST',
        url: 'fetch_student_details_invoice.php',
        data: { invoiceno: invoiceno },
        success: function (response) {
            var studentDetails = JSON.parse(response);

            // Populate the form fields with the retrieved information
            document.getElementById('studentname').value = studentDetails.studentname;
            document.getElementById('address').value = studentDetails.address;
            document.getElementById('contact').value = studentDetails.contact;
            var due = studentDetails.due_amount;
            fetchSubjectDetails(invoiceno);
            fetchDueAmountAndUpdateButtons(invoiceno);
            updateDueAmount();
        },
        error: function (error) {
            console.error('Error fetching students by invoice:', error);
        }
    });

}

function fetchDueAmountAndUpdateButtons(invoiceno) {
    // Make an AJAX request to fetch due amount based on invoice number
    $.ajax({
        type: 'POST',
        url: 'get_due_amount.php',
        data: { invoiceno: invoiceno },
        dataType: 'json',
        success: function (response) {
            if ('dueAmount' in response) {
                var dueAmount = parseFloat(response.dueAmount) || 0;

                if(dueAmount > 0)
                {
                // Update the "Reverse Invoice" button state
                 var reverseInvoiceButton = document.getElementById('reverseInvoicebtn');
                
                 reverseInvoiceButton.disabled = true;
                }
                else{
                    // Update the "Reverse Invoice" button state
                 var payButton = document.getElementById('paybtn');
                
                payButton.disabled = true;
                }
                // Display the due amount in the console
                console.log('Due Amount:', dueAmount.toFixed(2));
            } else {
                console.error('Error: Due Amount not found in the response.');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching due amount:', error);
        }
    });
}



function updateDueAndBalanceAmount(balanceAmount) {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
    var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;

    var balPaid = paidAmount + balanceAmount; // Add balance amount to the paid amount
    var remainBal = balPaid - grandTotal; // Calculate remaining balance
    console.log('balpaid:', balPaid.toFixed(2));
    console.log('remainBal', remainBal.toFixed(2));
    var dueAmountField = document.getElementById('due');
    var balanceAmountField = document.getElementById('balanceAmount');

    if (remainBal < 0) {

        // If remaining balance is negative, update due amount and set balance amount to 0
        dueAmountField.value =Math.abs(remainBal.toFixed(2));
        balanceAmountField.value = '0.00';
    } else {
        // If remaining balance is non-negative, update due amount to 0 and set balance amount
        dueAmountField.value = '0.00';
        balanceAmountField.value = remainBal.toFixed(2);
    }
}





function addNewSubjectRow() {
    // Clone the existing subject row template
    var clonedRow = document.getElementById('subjectRowTemplate').content.cloneNode(true);

    // Modify the IDs and names of the cloned row elements to avoid conflicts
    var rowIdx = document.getElementById('subjectRowsContainer').childElementCount;
    clonedRow.querySelectorAll('[id]').forEach(function (element) {
        element.id += '_' + rowIdx;
    });
    clonedRow.querySelectorAll('[name]').forEach(function (element) {
        element.name += '[]';
    });
    
    // Set default placeholder for the "Select Subject" option in the cloned row
    var subjectNameSelect = clonedRow.querySelector('.subject-name');
    var defaultOption = document.createElement('option');
    defaultOption.text = 'Select Subject';
    defaultOption.value = '';
    subjectNameSelect.add(defaultOption);
    subjectNameSelect.selectedIndex = subjectNameSelect.options.length - 1;

    // Add a delete button to the cloned row
    var deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'btn btn-danger  mt-2 ml-4 align-self-center';
    deleteButton.style.height = '35px'; // Set a custom height
    deleteButton.style.width = '63px';  // Set a custom width
    deleteButton.textContent = 'Delete';
    deleteButton.addEventListener('click', function () {
        // Remove the corresponding row when the delete button is clicked
        document.getElementById('subjectRowsContainer').removeChild(clonedRowContainer);
        updateGrandTotal();
    });
    
    // Append the delete button to the cloned row
    clonedRow.appendChild(deleteButton);

    // Append the cloned row to the form
    var clonedRowContainer = document.createElement('div');
    clonedRowContainer.className = 'row';
    clonedRowContainer.appendChild(clonedRow);
    document.getElementById('subjectRowsContainer').appendChild(clonedRowContainer);

    // Fetch subject details for the new row
    fetchSubjectByName(rowIdx);
  }

 
  // Modify the function to accept a parameter for row index
  function fetchSubjectByName(rowIdx) {
    var subjectNameSelect = document.getElementsByClassName('subject-name')[rowIdx];
    var priceInput = document.getElementsByClassName('price')[rowIdx];
    var taxInput = document.getElementsByClassName('tax')[rowIdx];
    var totalInput = document.getElementsByClassName('total')[rowIdx];

    // Attach an event listener to the subjectNameSelect dropdown to handle changes
    subjectNameSelect.addEventListener('change', function () {
        // Fetch subject details for the new row when subject name changes
        var subjectName = subjectNameSelect.value;

        $.ajax({
            type: 'POST',
            url: 'fetch_subject_details.php',
            data: { subjectName: subjectName },
            success: function (response) {
                var subjectDetails = JSON.parse(response);

                // Populate the form fields with the retrieved information for the specific row
                priceInput.value = subjectDetails.price;
                taxInput.value = subjectDetails.tax;
                calculateTotal(rowIdx);
            },
            error: function (error) {
                console.error('Error fetching details by subject name:', error);
            }
        });
    });
 }




 function calculateTotal(rowIdx) {
    // Get the price and tax values for the specific row
    var price = parseFloat(document.getElementsByClassName('price')[rowIdx].value) || 0;
    var tax = parseFloat(document.getElementsByClassName('tax')[rowIdx].value) || 0;

    // Calculate the total
    var total = price + ((tax/100)*price);

    // Display the total in the corresponding field for the specific row
    document.getElementsByClassName('total')[rowIdx].value = total;

    updateGrandTotal();
    updateDueAmount(); 
    
 }

// Function to calculate and update the Grand Total
function updateGrandTotal() {
    var grandTotal = 0;

    // Iterate through all rows and sum up the totals
    var totalFields = document.getElementsByClassName('total');
    for (var i = 0; i < totalFields.length; i++) {
        grandTotal += parseFloat(totalFields[i].value) || 0;
    }

    // Display the calculated grand total
    document.getElementById('grandTotal').value = grandTotal.toFixed(2);
 }
 function updateDueAmount() {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
    var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;

    var dueAmount = grandTotal - paidAmount;

    // Display the calculated due amount in the form field
    document.getElementById('due').value = dueAmount.toFixed(2);
    // Display the due amount in the console
    console.log('Due Amount:', dueAmount.toFixed(2));
}


document.getElementById('paidAmount').addEventListener('input', function () {
    var invoiceNumber = document.getElementById('invoiceno').value;
    console.log('Invoice Number:', invoiceNumber);
    $.ajax({
        type: 'POST',
        url: 'get_balance_amount.php', // Replace with the correct path
        data: { invoiceNumber: invoiceNumber },
        dataType: 'json',
        success: function (response) {
            console.log(response); // Log the entire response

            if ('balanceAmount' in response) {
                var balanceAmount = parseFloat(response.balanceAmount) || 0;
                updateDueAndBalanceAmount(balanceAmount);
            } else {
                console.error('Error: Balance Amount not found in the response.');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching balance amount:', error);
        }
    });

    checkPaidAmountValidity(); 
});

// Function to check the validity of the paid amount
function checkPaidAmountValidity() {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
    var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;

    var minValidPaidAmount = grandTotal * 0.5; // 50% of the grand total

    // Check if paidAmount is less than 50% of grandTotal
    if (paidAmount < minValidPaidAmount) {
        // Display the warning message
        document.getElementById('warningMessage').innerText = 'Your paid amount is less than 50% of the grand total. Please pay at least 50% of the grand total.';
        document.getElementById('warningMessage').style.display = 'block';
        // Disable the submit button
        document.getElementById('reverseInvoicebtn').disabled = true;
    } else if (paidAmount > grandTotal && paidAmount % grandTotal !== 0) {
        // Display the warning message for invalid multiple of grand total
        document.getElementById('warningMessage').innerText = 'Your paid amount is greater than the grand total, but it must be a multiple of the grand total.';
        document.getElementById('warningMessage').style.display = 'block';
        // Disable the submit button
        document.getElementById('reverseInvoicebtn').disabled = true;
    } else {
        // Hide the warning message
        document.getElementById('warningMessage').style.display = 'none';

        // Enable the submit button
        document.getElementById('reverseInvoicebtn').disabled = false;
    }
}


// Attach an event listener to each row's total field for real-time updates
document.getElementById('subjectRowsContainer').addEventListener('input', function (event) {
    if (event.target.classList.contains('total')) {
        updateGrandTotal();
    }
});


$(document).ready(function() {
        $("#contactNumber, #studentName").on("input", function() {
            var contactNumber = $("#contactNumber").val();
            var studentName = $("#studentName").val();
            $.ajax({
                url: "get_invoice_details.php",
                type: "POST",
                data: { contactNumber: contactNumber, studentName: studentName },
                success: function(response) {
                    var data = JSON.parse(response);
                    $("#dueAmount").val(data.dueAmount);
                    $("#grandtotal").val(data.grandtotal);
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                }
            });
        });
    });











function validateStudentName() {
        const studentNameInput = document.getElementsByName("studentName")[0];
        const studentNameError = document.getElementById("studentNameError");

        const regex = /^[a-zA-Z]+$/;

        if (!regex.test(studentNameInput.value)) {
            studentNameError.textContent = "Student Name should contain only letters.";
            return false;
        } else {
            studentNameError.textContent = "";
            return true;
        }
    }

    function validateContact() {
        const contactInput = document.getElementsByName("contact")[0];
        const contactError = document.getElementById("contactError");

        const regex = /^\d{10}$/;

        if (!regex.test(contactInput.value)) {
            contactError.textContent = "Contact number should contain only 10 digits.";
            return false;
        } else {
            contactError.textContent = "";
            return true;
        }
    }
    function validateContact2() {
        const contactInput = document.getElementsByName("contact2")[0];
        const contactError = document.getElementById("contact2Error");

        const regex = /^\d{10}$/;

        if (!regex.test(contactInput.value)) {
            contactError.textContent = "Contact number should contain only 10 digits.";
            return false;
        } else {
            contactError.textContent = "";
            return true;
        }
    }

    function validateAddress() {
        const addressInput = document.getElementsByName("address")[0];
        const addressError = document.getElementById("addressError");

        const regex = /^[a-zA-Z0-9]+$/;

        if (!regex.test(addressInput.value)) {
            addressError.textContent = "Address should contain letters and a combination of numbers and letters.";
            return false;
        } else {
            addressError.textContent = "";
            return true;
        }
    }

    function validateParentName() {
        const parentNameInput = document.getElementsByName("parentName")[0];
        const parentNameError = document.getElementById("parentNameError");

        const regex = /^[a-zA-Z]+$/;

        if (!regex.test(parentNameInput.value)) {
            parentNameError.textContent = "Parent Name should contain only letters.";
            return false;
        } else {
            parentNameError.textContent = "";
            return true;
        }
    }
    // Function to show the modal and display warning messages
    function showModalAndDisplayWarnings(warningMessage) {
        $('#warningMessage').text(warningMessage);
        $('#addStudentModal').modal('show');
    }

    // Function to validate the form
    function validateForm() {
        const isStudentNameValid = validateStudentName();
        const isContactValid = validateContact();
        const isContact2Valid = validateContact2();
        const isAddressValid = validateAddress();
        const isParentNameValid = validateParentName();

        const submitButton = document.getElementById("addStudentButton");

        if (isStudentNameValid && isContactValid && isAddressValid && isParentNameValid && isContact2Valid) {
            submitButton.disabled = false;
            return true;
        } else {
            submitButton.disabled = true;
            return false;
        }
    }

  // Function to handle form submission and check for warnings
$('#addStudentForm').submit(function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Perform form validation
    if (validateForm()) {
        // Submit the form via AJAX
        $.ajax({
            type: 'POST',
            url: 'check_student_exists.php', // Path to the PHP script for checking student existence
            data: $('#addStudentForm').serialize(), // Serialize form data
            success: function(response) {
                if (response === 'contact_not_unique') {
                    // Show warning if the student already exists
                    showModalAndDisplayWarnings("Another student with the same contact numbers, but different parent name or address, already exists.");
                } else if (response === 'student_exists') {
                    showModalAndDisplayWarnings("A student with these details already exists in branch.");
                } else if (response === 'insert_success') {
                    // Display success message
                    showSuccessMessage("Student added successfully.");
                } else {
                    // Submit the form if no warnings or success message
                    $('#addStudentForm')[0].submit();
                }
            }
        });
    } else {
        // Show modal with validation error message
        showModalAndDisplayWarnings("Please fix the validation errors before submitting.");
    }
});

// Function to display success message with light green background and auto-hide after 5 seconds
function showSuccessMessage(message) {
    $('#addStudentModal').modal('hide');
    var successMessage = $('<p>').text(message).addClass('text-success').css({
        'background-color': '#d4edda', // Light green background color
        'padding': '10px', // Padding
        'border-radius': '5px' // Rounded corners
    }).appendTo('#successMessage').fadeIn(); // Append to container and fade in

    // Automatically hide the message after 5 seconds
    setTimeout(function() {
        successMessage.fadeOut(function() {
            // Close the modal after success message disappears
            $('#addStudentForm')[0].reset();
        }); // Fade out after 5 seconds
    }, 2000);
}


    document.addEventListener('DOMContentLoaded', function () {
    // Get form elements outside the event listener to avoid repetitive querying
    const payDueForm = document.getElementById('payDueForm');
    const dueAmountInput = document.getElementById('dueAmount');
    const grandTotalInput = document.getElementById('grandtotal');
    const actualAmountInput = document.getElementById('actualAmount');
    const payDueButton = document.getElementById('payDueButton');

    // Disable payDue button by default
    payDueButton.disabled = true;

    // Add input event listeners to trigger calculation when values change
    actualAmountInput.addEventListener('input', calculateAndCheck);

    function calculateAndCheck() {
        const dueAmount = parseFloat(dueAmountInput.value);
        const grandTotal = parseFloat(grandTotalInput.value);
        const actualAmount = parseFloat(actualAmountInput.value);

        // Display actualAmount, grandTotal, and dueAmount in the console
        console.log('actualAmount:', actualAmount);
        console.log('grandTotal:', grandTotal);
        console.log('dueAmount:', dueAmount);

        if (actualAmount >= dueAmount) {
            // Calculate remaining due
            var remainDue = actualAmount - dueAmount;

            // Check if remainDue is a multiple of grandTotal
            if ((remainDue % grandTotal === 0 && remainDue >= grandTotal) || remainDue === 0) {
                // Enable payDue button
                payDueButton.disabled = false;
                document.getElementById('duewarning').innerText = '';
            } else {
                document.getElementById('duewarning').innerText = 'if you pay more than dueamount please add due amount along with total.';
                ///document.getElementById('duewarning').style.display = 'block';

                // Disable payDue button
                payDueButton.disabled = true;
            }

            // Print remainDue in the console
            console.log('remainDue:', remainDue);
        } else {
            // Disable payDue button if actualAmount is not greater than dueAmount
            payDueButton.disabled = true;
        }
    }
});


    function  showModalAndDisplayWarnings(warningMessage) {
        $('#warningMessage').text(warningMessage);
        $('#addStudentModal').modal('show');
    }

   

</script>
</body>
</html>
<?php ob_end_flush();?>
