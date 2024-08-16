<?php
include 'master.php';
include 'update_invoices.php';
// Function to check if an admin already exists with the given username
function isUsernameExists($conn, $adminUsername) {
    $query = "SELECT COUNT(*) FROM branch_admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $adminUsername);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

function isPasswordExists($conn, $adminPassword) {
    // Note: Storing passwords as MD5 hashes is not recommended for security reasons.
    // Use a stronger and more secure hashing algorithm like bcrypt.

    $hashedPassword = md5($adminPassword);

    $query = "SELECT COUNT(*) FROM branch_admins WHERE password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $hashedPassword);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}


$warningMessage = ""; // Initialize an empty warning message

if (isset($_POST['addAdmin'])) {
    $adminUsername = $_POST['adminUsername'];
    $adminPassword = $_POST['adminPassword'];
    $adminBranch = $_POST['adminBranch'];

    // Check if the username already exists
    if (isUsernameExists($conn, $adminUsername)) {
        $usernameExistsError = "Admin with this username already exists!";
        $showAdminForm = true; // Set flag to true to show add admin form
    } else {
        // If the username doesn't exist, proceed with checking the password
        if (isPasswordExists($conn, $adminPassword)) {
            $passwordExistsError = "Admin with this password already exists!";
            $showAdminForm = true; // Set flag to true to show add admin form
        } else {
            // If neither the username nor the password exists, proceed with adding the admin
            $adminPassword = md5($adminPassword);
            addAdmin($conn, $adminUsername, $adminPassword, $adminBranch);

            // Display success message and close it after 5 seconds
            echo '<p id="successMessage" style="color: white; background-color: green;">Admin added successfully!</p>';

            echo '<script>
                    setTimeout(function() {
                        var successMessage = document.getElementById("successMessage");
                        if (successMessage) {
                            successMessage.style.display = "none";
                        }
                    }, 5000);
                  </script>';
        }
    }
}




// Function to check if a branch is already assigned to an admin
function isBranchAssignedToAdmin($conn, $branchId) {
    $query = "SELECT COUNT(*) FROM branch_admins WHERE branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

// Function to add an admin to the branch_admins table
function addAdmin($conn, $adminUsername, $adminPassword, $adminBranch) {
    $query = "INSERT INTO branch_admins (username, password, branch_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $adminUsername, $adminPassword, $adminBranch);

    if ($stmt->execute()) {
        // Admin added successfully
    } else {
        // Error adding admin
    }

    $stmt->close();
}




// if (isset($_POST['addSubject'])) {
//     $subjectName = $_POST['subjectName'];
//     $fees = $_POST['fees'];
//     $tax = $_POST['tax'];
//     $selectedBranches = $_POST['branches'];


//     // Check if at least one branch is selected
//     if (empty($selectedBranches)) {
//         // Handle the case where no branches are selected
//         echo "Please select at least one branch for the subject.";
//         exit();
//     }

//     $subjectExists = false;
// foreach ($selectedBranches as $branchId) {
//     if (isSubjectExists($conn, $subjectName, $branchId)) {
//         // Subject already exists in this branch
//         $subjectExists = true;
//         break; // Exit the loop if subject already exists
//     }
// }

// // If validation passed, call addSubject function
// if (!$subjectExists) {
//     addSubject($conn, $subjectName, $fees, $tax, $selectedBranches);
// } else {

//     $already = "subject already exists!!";
//     // echo '<script>showSubjectExistsError();</script>';
// }
    
//    // addSubject($conn, $subjectName, $fees, $tax, $selectedBranches);

// }
// // Function to check if a subject already exists in a specific branch
// function isSubjectExists($conn, $subjectName, $branchId) {
//     $query = "SELECT COUNT(*) FROM subjects WHERE subject_name = ? AND branch_id = ?";
//     $stmt = $conn->prepare($query);
//     $stmt->bind_param("ss", $subjectName, $branchId);
//     $stmt->execute();
//     $stmt->bind_result($count);
//     $stmt->fetch();
//     $stmt->close();

//     return $count > 0;
// }


// Function to get the student count for a specific branch
function getBranchStudentCount($conn, $branchId) {
    // Use prepared statements to avoid SQL injection
    $query = "SELECT COUNT(*) FROM branch_students WHERE branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count;
}

if (isset($_POST['logout'])) {
    // Perform any additional logout actions if needed
    // For example, destroying the session
    session_destroy();

    // Redirect to the login page after logging out
    header("Location: master_login.php");
    exit();
}

// Fetch branches for dropdown
$branches = getMasterBranches($conn);

// // Fetch Master dashboard data
// $dashboardData = getMasterDashboardData($conn);

// Get the count of total subjects
$totalSubjectsCount = $conn->query("SELECT COUNT(*) FROM subjects")->fetch_row()[0];

//Get the count of total subjects
$totalStudentCount = $conn->query("SELECT COUNT(*) FROM branch_students")->fetch_row()[0];

// Create Branch
function createBranch($conn, $branchName) {
    // Check if the branch name already exists
    $checkExistingBranch = "SELECT * FROM branches WHERE branch_name = '$branchName'";
    $result = $conn->query($checkExistingBranch);

    if ($result->num_rows > 0) {
        // Branch already exists
        return false;
    } else {
        // Branch name doesn't exist, proceed with inserting
        $sql = "INSERT INTO branches (branch_name) VALUES ('$branchName')";
        $conn->query($sql);
        return true;
    }
}

// Call createBranch() when attempting to create a branch
if (isset($_POST['createBranch'])) {
    $branchName = $_POST['branchName'];
    $branchCreated = createBranch($conn, $branchName);
    if (!$branchCreated) {
        // Branch creation failed, echo the error message directly into the paragraph tag
        echo '<script>
                document.getElementById("branchErrorMessage").innerHTML = "Branch Name already exists!!";
                document.getElementById("branchErrorMessage").style.display = "block";
            </script>';
    } else {
        // Branch created successfully
        header("Location: master_dashboard.php");
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="masterstyles.css"> <!-- Add your custom styles if needed -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./style/master_dashboard.css">
    <style>

body {
    background-image: url('./assests/white.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: top center;
    width: auto;
    overflow-y: scroll; /* Add vertical scrollbar */
    margin: 0; /* Remove default body margin */
    padding: 0; /* Remove default body padding */
}


.modal-content {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Add hover effect to top buttons */
.top-buttons button:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease-in-out;
}

/* Add transition to badge */
.card-title .badge {
    transition: background-color 0.3s ease-in-out;
}

/* Add transition to modal close button */
.close {
    transition: color 0.3s ease-in-out;
}

/* Add hover effect to cards */
.card:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease-in-out;
}

/* Add transition to card title */
.card-title {
    transition: color 0.3s ease-in-out;
}

/* Add hover effect to links inside cards */
.card a:hover {
    color: #17a2b8; /* Change the color to your preference */
}

/* Add animation to the logout button */
.btn-outline-danger.btn-sm:hover {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    50% { transform: translateX(5px); }
    75% { transform: translateX(-5px); }
    100% { transform: translateX(0); }
}

.custom-btn {
    width: 10%;
    margin-bottom: 10px;
}

.top-buttons {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}

.top-buttons button {
    margin: 0 10px; /* Add space between buttons */
    padding: 15px 30px; /* Adjust these values to control height and width */
    font-size: 16px; /* You can adjust font size based on your preference */
}

.logout-button {
    margin-left: 10px; /* Adjust the margin as needed */
}

.logout-button button {
    padding: 15px 30px; /* Adjust the padding for the desired button size */
}

.top-buttons .btn {
    padding: 15px 30px; /* You can adjust padding based on your preference */
    font-size: 16px; /* You can adjust font size based on your preference */
}
.top-buttons .btn {
    padding: 15px 30px; /* Adjust padding for height and width */
    font-size: 16px; /* Adjust font size */
}
.btn-lg {
    position: relative;
    overflow: hidden;
    background-color: transparent;
    color: #000000;
    font-size: 20px;
    font-weight: bold;
    transition: color 0.3s ease;
}

.btn-lg:before {
    content: "";
    position: absolute;
    bottom: 0;
    right: 0;
    width: 0;
    height: 0;
    background-color: #3498db;
    transition: width 0.3s ease, height 0.3s ease;
    z-index: -1; /* Move the pseudo-element behind the content */
}

#warningMessagebranch {
    color: darkred; /* Dark red text color */
    font-weight: bold;
    padding-left: 300px; /* Padding */
    border-radius: 5px; /* Rounded corners */
}

.btn-lg:hover:before {
    width: 100%;
    height: 100%;
}

.btn-lg span {
    position: relative;
    z-index: 2; /* Ensure the text appears on top */
    display: block;
}

.btn-lg:hover span {
    color: #f9f9f9;
    font-weight: bold;
}
.btn-blue {
    border: 3px solid #3498db; /* Increase border width */
}

.btn-green {
    border: 3px solid #2ecc71;
}

.btn-yellow {
    border: 3px solid #f39c12;
}

.btn-red {
    border: 3px solid #e74c3c;
}

.btn-purple {
    border: 3px solid #9b59b6;
}
        </style>
</head>

<body>
<!-- Warning Message -->

<div class="alert alert-danger mt-3" id="branchErrorMessage" style="display: none;"></div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-6 text-right">
                <form action="" method="post">
                    <button type="submit" class="btn btn-outline-danger btn-sm" name="logout">Logout</button>
                </form>
            </div>
        </div>

        <h1 class="text-center mb-4">Welcome, Master!</h1>

        <p id= "successMessage" class="text-danger"></p>
        <p id= "successMessagebranch" class="text-danger"></p>

  


<!-- Top Buttons Row -->
<!-- Top Buttons Row -->

   


<div class="top-buttons text-center">
    <!-- Button to trigger modal -->
<button class="btn btn-info my-1 btn-text btn-lg btn-blue" onclick="openModal()">View Subjects</button>

<!-- Modal -->
<div id="myModal" class="modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">View branch subjects</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <form id="invoiceForm" action="view_branch_subjects.php" method="get">
          <div class="form-group">
            <label for="branch">Select Branch:</label>
            <select class="form-control" name="branch" required style="width: 100%;">
              <?php foreach ($branches as $branch) : ?>
                <option value="<?php echo $branch['branch_name']; ?>"><?php echo $branch['branch_name']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group text-center"> <!-- Center align the button -->
            <button type="submit" class="btn btn-info my-1 btn-text" onclick="viewBranchSubjects()">View subjects</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>




    <button class="btn btn-primary my-1 btn-text btn-lg btn-green"  onclick="toggleFormVisibility()">Add Subject</button>
    <button class="btn btn-success my-1 btn-text btn-lg btn-yellow" onclick="toggleBranchFormVisibility()">Add Branch</button>
    <button class="btn btn-warning  my-1 btn-text btn-lg btn-red" onclick="toggleAdminFormVisibility()">Add Admin</button>
    <div class="row">
        <div class="col-md-8">
            <!-- Change the form action in master_dashboard.php -->
            <form action="view_invoice_report.php" method="get">
                <div class="form-group">
                    <label for="branch">Select Branch:</label>
                    <select class="form-control" name="branch" required style="width: 100%;">
                        <?php foreach ($branches as $branch) : ?>
                            <option value="<?php echo $branch['branch_name']; ?>"><?php echo $branch['branch_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-info my-1 btn-text" style="width: 200%;">View Invoice Report</button>
            </div>
        </form>
    </div>
</div>


<div class="row mb-4">
<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Total Students</h5>
            <a href="view_student_details.php" class="btn btn-link stretched-link">
                <?php echo $totalStudentCount; ?>
            </a>
            <?php foreach ($branches as $branch) : ?>
                <div onclick="showBranchStudents('<?php echo $branch['branch_id']; ?>')">
                    <h6><?php echo $branch['branch_name'] . ' :' . getBranchStudentCount($conn, $branch['branch_id']); ?></h6>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Total Subjects and Tax</h5>
            <a href="view_subject.php" class="btn btn-link stretched-link">
                <?php echo $totalSubjectsCount; ?>
            </a>
        </div>
    </div>
</div>
  
<!-- Inside the "Add Subject Form Modal" -->
<div id="addSubjectModal" class="modal">
    <div class="modal-content">
        <!-- Warning Message Placeholder -->
        <p id="warningMessage" class="text-danger"></p>
        <span class="close" onclick="toggleFormVisibility()">&times;</span>
        <form id= "addsubjectForm" action="master_dashboard.php" method="post">
            <div class="form-group">
                <label for="subjectName">Subject Name:</label>
                <input type="text" class="form-control" name="subjectName" id="subjectName" required>
                <p class="text-danger" id="subjectError"></p>
            </div>

            <div class="form-group">
                <label for="fees">Fees:</label>
                <input type="number" class="form-control" name="fees" id="fees" required>
                <p class="text-danger" id="feesError"></p>
            </div>

            <div class="form-group">
                <label for="tax">Tax:</label>
                <input type="number" class="form-control" name="tax" id="tax" required>
                <p class="text-danger" id="taxError"></p>
            </div>

            <div class="form-group">
                <label>Select Branch(es):</label><br>
                <!-- Add the necessary code for branches selection -->

                <!-- For demonstration purposes, assuming $branches is an array containing branch information -->
                <?php foreach ($branches as $branch) : ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="branches[]"
                            value="<?php echo $branch['branch_id']; ?>"
                            id="branch_<?php echo $branch['branch_id']; ?>">
                        <label class="form-check-label"
                            for="branch_<?php echo $branch['branch_id']; ?>"><?php echo $branch['branch_name']; ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block" name="addSubject" id="addSubjectBtn" disabled>Add Subject</button>
        </form>
    </div>
</div>

        <div class="container mt-4">
            <div class="row">
                <!-- Create a card for each branch -->
                <?php foreach ($branches as $branch) : ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo $branch['branch_name']; ?>
                                    <span
                                        class="badge badge-secondary"><?php echo count(getBranchSubjects($conn, $branch['branch_id'])); ?></span>
                                </h5>
                                <!-- List subjects under the current branch -->
                                <?php $branchSubjects = getBranchSubjects($conn, $branch['branch_id']); ?>
                                <ul class="list-group">
                                    <?php foreach ($branchSubjects as $subject) : ?>
                                        <li class="list-group-item"><?php echo $subject['subject_name']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
<!-- Inside the "Add Branch Form Modal" -->
<div id="createBranchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="toggleBranchFormVisibility()">&times;</span>
        <div id="warningMessagebranch" class="modal-warning"></div> <!-- Warning message div -->
        <form id="addbranchForm" action="master_dashboard.php" method="post">
            <div class="form-group">
                <label for="branchName">Branch Name:</label>
                <input type="text" class="form-control" name="branchName" id="branchName" required oninput="validateBranchName()">
                <p class="text-danger" id="branchError"></p>
            </div>
            <button type="submit" class="btn btn-success btn-block" name="createBranch" id="addbranchBtn" disabled>Create Branch</button>
        </form>
    </div>
</div>

       
 <!-- Inside the "Add Admin Form Modal" -->
 <div id="addAdminForm">
    <div class="modal" id="addAdminModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add Admin</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form action="master_dashboard.php" method="post">
                        <!-- Your form fields go here -->
                        <div class="form-group">
                            <label for="adminUsername">Username:</label>
                            <input type="text" class="form-control" name="adminUsername" required>
                            <?php if (isset($usernameExistsError)) : ?>
                                <p class="text-danger"><?php echo $usernameExistsError; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="adminPassword">Password:</label>
                            <input type="password" class="form-control" name="adminPassword" required>
                            <?php if (isset($passwordExistsError)) : ?>
                                <p class="text-danger"><?php echo $passwordExistsError; ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="adminBranch">Select Branch:</label>
                            <select class="form-control" name="adminBranch" required>
                                <?php foreach ($branches as $branch) : ?>
                                    <?php if (!isBranchAssignedToAdmin($conn, $branch['branch_id'])) : ?>
                                        <option value="<?php echo $branch['branch_id']; ?>"><?php echo $branch['branch_name']; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- ... (other form fields) -->
                        <button type="submit" class="btn btn-warning btn-block" name="addAdmin">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

        <?php if (isset($loginError)) : ?>
            <p class="mt-4 text-danger"><?php echo $loginError; ?></p>
        <?php endif; ?>
    </div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
 // Function to open modal
 function openModal() {
    $('#myModal').modal('show');
  }

function closeWarning() {
            document.getElementById("warningMessage").style.display = "none";
        }
        function toggleFormVisibility() {
    var modal = document.getElementById("addSubjectModal");

    if (modal.style.visibility === "hidden" || modal.style.visibility === "") {
        modal.style.visibility = "visible";
        modal.style.opacity = 1;

        // Enable entire page
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    } else {
        modal.style.visibility = "hidden";
        modal.style.opacity = 0;
    }
}


function toggleBranchFormVisibility() {
    var modal = document.getElementById("createBranchModal");
    if (modal.style.visibility === "hidden" || modal.style.visibility === "") {
        modal.style.visibility = "visible";
        modal.style.opacity = 1;

        // Enable entire page
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    } else {
        modal.style.visibility = "hidden";
        modal.style.opacity = 0;
    }
}

function viewBranchSubjects() {
    // Get the selected branch name from the dropdown
    var branchName = $('select[name="branch"]').val();

    if (branchName) {
        // Use AJAX to fetch the branch ID corresponding to the selected branch name
        $.ajax({
            url: 'get_branch_id.php',
            type: 'POST',
            data: { branchName: branchName },
            success: function(data) {
                console.log('Branch ID:', data);
                window.location.href = "http://localhost/tuitionmanage/view_branch_subjects.php?branch_id=" + data;
            },
            error: function(xhr, status, error) {
                console.error("Error fetching branch ID: " + error);
            }
        });
    }
}


    function validateBranchName() {
    var branchNameInput = document.getElementById('branchName');
    var branchError = document.getElementById('branchError');
    var addbranchBtn = document.getElementById('addbranchBtn');
    var regex = /^[a-zA-Z\s]+$/; // Regular expression to match only letters and spaces

    if (!branchNameInput.value.match(regex)) {
        branchError.textContent = "Branch name can only contain letters and spaces.";
        addbranchBtn.disabled = true;
        return false;
    } else {
        branchError.textContent = "";
        addbranchBtn.disabled = false;
        return true;
    }
}




$('#addbranchForm').submit(function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Perform form validation
    if (validateBranchName()) {
        // Submit the form via AJAX
        $.ajax({
            type: 'POST',
            url: 'check_branch_exists.php',
            data: $('#addbranchForm').serialize(),
            success: function(response) {
                if (response.trim() === 'branch_exists') {
    // Show warning if the branch already exists
    showModalAndDisplayWarningsbranch("Branch already exists.");
} else if (response.trim() === 'insert_success') {
    // Store success message in local storage
    localStorage.setItem('successMessagebranch', "Branch added successfully.");

    // Reload the page
    window.location.reload();
} else {
    // Submit the form if no warnings or success message
    $('#addbranchForm')[0].submit(); // Unbind the submit event handler and submit the form
}

            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                console.error(xhr.responseText);
                showModalAndDisplayWarningsbranch("An error occurred while processing the request. Please try again later.");
            }
        });
    } else {
        // Show modal with validation error message
        showModalAndDisplayWarningsbranch("Please fix the validation errors before submitting.");
    }
});

// Function to show the modal and display warning messages
function showModalAndDisplayWarningsbranch(warningMessage) {
    $('#warningMessagebranch').text(warningMessage);
    $('#createBranchModal').modal('show');

     // Remove the modal backdrop to enable the entire page
    $('.modal-backdrop').remove();
}

// Check if success message is stored in local storage
var successMessagebranch = localStorage.getItem('successMessagebranch');
if (successMessagebranch) {
    // Display the success message
    showSuccessMessagebranch(successMessagebranch);

    // Clear the success message from local storage
    localStorage.removeItem('successMessagebranch');
}

// Function to display success message with light green background and auto-hide after 5 seconds
function showSuccessMessagebranch(message) {
    var successMessagebranch = $('<p>').text(message).addClass('text-success').css({
        'background-color': '#d4edda', // Light green background color
        'padding': '10px', // Padding
        'border-radius': '5px' // Rounded corners
    }).appendTo('#successMessagebranch').fadeIn(); // Append to container and fade in

    // Automatically hide the message after 5 seconds
    setTimeout(function() {
        successMessagebranch.fadeOut(function() {
            // Close the modal after success message disappears
            $('#createBranchModal').modal('hide');
            $('#addbranchForm')[0].reset();
        }); // Fade out after 5 seconds
    }, 5000);
}


    // Document ready function
    $(document).ready(function () {
        // Initialize Bootstrap's modal
        $('#addAdminModal').modal({ show: false });
    });

    function toggleAdminFormVisibility() {
        // Show/hide the modal using Bootstrap's modal function
        $('#addAdminModal').modal('toggle');
    }

    function validateForm() {
    var subjectName = document.getElementById('subjectName').value;
    var fees = document.getElementById('fees').value;
    var tax = document.getElementById('tax').value;

    // Regex for checking if subjectName contains only letters
    var lettersRegex = /^[a-zA-Z\s]+$/; // Updated to allow whitespace

    // Regex for checking if fees and tax contain only numbers
    var numbersRegex = /^\d+$/;

    // Error messages
    var subjectError = "";
    var feesError = "";
    var taxError = "";

    // Validate Subject Name
    if (!lettersRegex.test(subjectName.trim())) { // Trim subjectName before validation
        subjectError = "Subject Name should contain only letters.";
    }

    // Validate Fees
    if (!numbersRegex.test(fees.trim())) { // Trim fees before validation
        feesError = "Fees should contain only numbers.";
    }

    // Validate Tax
    if (!numbersRegex.test(tax.trim())) { // Trim tax before validation
        taxError = "Tax should contain only numbers.";
    }

    // Display error messages
    document.getElementById('subjectError').textContent = subjectError;
    document.getElementById('feesError').textContent = feesError;
    document.getElementById('taxError').textContent = taxError;

    // Check if all fields are valid to enable/disable the Add Subject button
    var branchesChecked = document.querySelectorAll('input[name="branches[]"]:checked').length > 0;

    var addSubjectBtn = document.getElementById('addSubjectBtn');
    if (subjectError === "" && feesError === "" && taxError === "" && branchesChecked) {
        addSubjectBtn.disabled = false;
        return true;
    } else {
        addSubjectBtn.disabled = true;
        return false;
    }
}


    // Add event listeners to the form fields to trigger validation on input change
    document.getElementById('subjectName').addEventListener('input', validateForm);
    document.getElementById('fees').addEventListener('input', validateForm);
    document.getElementById('tax').addEventListener('input', validateForm);

    // Add event listener for branch checkboxes
    var branchCheckboxes = document.querySelectorAll('input[name="branches[]"]');
    branchCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', validateForm);
    });



<?php if (isset($showAdminForm) && $showAdminForm) : ?>
    $(document).ready(function () {
        $('#addAdminModal').modal('show'); // Show the add admin modal
    });
<?php endif; ?>

$('#addsubjectForm').submit(function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Perform form validation
    if (validateForm()) {
        // Submit the form via AJAX
        $.ajax({
            type: 'POST',
            url: 'check_subject_exists.php',
            data: $('#addsubjectForm').serialize(),
            success: function(response) {
                if (response.trim() === 'subject_exists') { // Trim the response to remove any extra spaces
                    // Show warning if the subject already exists
                    showModalAndDisplayWarnings("Subject already exists in branch.");
                } else if (response.trim() === 'insert_success') {
                    // Store success message in local storage
                    localStorage.setItem('successMessage', "Subject added successfully.");

                    // Reload the page
                    window.location.reload();
                } else {
                    // Submit the form if no warnings or success message
                    $('#addsubjectForm')[0].submit(); // Unbind the submit event handler and submit the form
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                console.error(xhr.responseText);
                showModalAndDisplayWarnings("An error occurred while processing the request. Please try again later.");
            }
        });
    } else {
        // Show modal with validation error message
        showModalAndDisplayWarnings("Please fix the validation errors before submitting.");
    }
});





function showModalAndDisplayWarnings(warningMessage) {
    // Show the modal and set warning message
    $('#warningMessage').text(warningMessage);
    $('#addSubjectModal').modal('show');

    // Enable entire page
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
}

// Function to display success message with light green background and auto-hide after 5 seconds
function showSuccessMessage(message) {
    var successMessage = $('<p>').text(message).addClass('text-success').css({
        'background-color': '#d4edda', // Light green background color
        'padding': '10px', // Padding
        'border-radius': '5px' // Rounded corners
    }).appendTo('#successMessage').fadeIn(); // Append to container and fade in

    // Automatically hide the message after 5 seconds
    setTimeout(function() {
        successMessage.fadeOut(function() {
            // Close the modal after success message disappears
            $('#addsubjectForm')[0].reset();
        }); // Fade out after 5 seconds
    }, 5000);
}





    </script>
</body>

</html>
