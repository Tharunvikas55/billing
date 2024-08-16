<?php
// Include your master.php file for database connection and functions
include 'master.php';

// Fetch subjects with or without search condition
if (isset($_POST['search'])) {
    $searchValue = $_POST['search'];
    $subjects = searchSubjects($conn, $searchValue);
} else {
    $subjects = searchSubjects($conn);
}

function searchSubjects($conn, $search = null) {
    $query = "SELECT * FROM subjects";

    // Add a search condition if a search value is provided
    if ($search !== null) {
        $search = $conn->real_escape_string($search);
        $query .= " WHERE subject_name LIKE '%$search%' OR fees LIKE '%$search%' OR tax LIKE '%$search%' OR branch_id LIKE '%$search%'";
    }

    $result = $conn->query($query);

    // Fetch subjects into an array
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    return $subjects;
}






// Assuming you have functions to get subjects, delete subject, and update subject
// Replace these with your actual functions
// function getSubjects($conn) {
//     $query = "SELECT * FROM subjects";
//     $result = $conn->query($query);

//     // Fetch subjects into an array
//     $subjects = [];
//     while ($row = $result->fetch_assoc()) {
//         $subjects[] = $row;
//     }

//     return $subjects;
// }

function deleteSubject($conn, $subjectId) {
    $subjectId = $conn->real_escape_string($subjectId); // Sanitize input
    $conn->query("DELETE FROM subjects WHERE id = $subjectId");
}

function updateSubject($conn, $subjectId, $newSubjectData) {
    $subjectId = $conn->real_escape_string($subjectId); // Sanitize input
    $subjectName = $conn->real_escape_string($newSubjectData['subject_name']);
    $fees = $conn->real_escape_string($newSubjectData['fees']);
    $tax = $conn->real_escape_string($newSubjectData['tax']);

    // Update the subject with the given ID
    $conn->query("UPDATE subjects SET subject_name = '$subjectName', fees = $fees, tax = $tax WHERE id = $subjectId");
}

// Fetch subjects
// $subjects = getSubjects($conn);



// Handle delete request
if (isset($_GET['delete']) && isset($_GET['subject_id'])) {
    $subjectIdToDelete = $_GET['subject_id'];
    deleteSubject($conn, $subjectIdToDelete);
    header("Location: view_subject.php"); // Redirect after deleting to refresh the page
    exit();
}

// Handle edit request
if (isset($_GET['edit']) && isset($_GET['subject_id'])) {
    $subjectIdToEdit = $_GET['subject_id'];
    // Implement the logic to load subject data for editing
    // Example: $subjectToEdit = getSubjectById($conn, $subjectIdToEdit);
    // Assume $subjectToEdit is an array containing subject data
    // You can then populate a form with this data for editing
}

// Handle update request
if (isset($_POST['update_subject'])) {
    $subjectIdToUpdate = $_POST['subject_id'];
    $newSubjectData = [
        'subject_name' => $_POST['updated_subject_name'],
        'fees' => $_POST['updated_fees'],
        'tax' => $_POST['updated_tax']
    ];
    updateSubject($conn, $subjectIdToUpdate, $newSubjectData);
    header("Location: view_subject.php"); // Redirect after updating to refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Subjects</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./style/view_subject.css"> <!-- Add your custom styles if needed -->
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

        .custom-padding {
            padding-left: 28%; /* Adjust the value according to your preference */
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: rgba(254, 254, 254, 0.64);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            overflow: hidden;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15);
        }

        .table tbody + tbody {
            border-top: 2px solid rgba(255, 255, 255, 0.15);
        }

        .btn-glass {
            background-color: transparent;
            color: #fff;
            border: 1px solid #fff;
            border-radius: 5px;
            padding: 5px 10px;
            margin-right: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-glass:hover {
            background-color: #fff;
            color: #000;
        }


#editSubjectModal {
    height: 100vh;
    margin-right:180rem;
    margin-left:-35rem;
    
}

.modal-dialog {
    max-width: 30%;
}

#view{
    margin-bottom: 3rem;
    color: red;
}
#dash{
    position: fixed;
    top:2rem;
    right: 3rem;
}
        </style>
</head>
<body>
    <div class="container">
    <h1 class="mt-4 custom-padding" id="view">Subject Details</h1>
      <!-- Add search bar -->
      <form method="post" class="form-inline mb-3">
            <div class="form-group">
                <input type="text" class="form-control" id="search" name="search" placeholder="Search">
            </div>
            <button type="submit" class="btn btn-primary ml-2">Search</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="resetSearch()">Reset</button>
        </form>
        <a href="master_dashboard.php" class="btn btn-primary mt-4" id ="dash">Back to Master Dashboard</a>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch_Id</th>
                    <th>Subject Name</th>
                    <th>Fees</th>
                    <th>Tax</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <tr>
                        <td><?php echo $subject['id']; ?></td>
                        <td><?php echo $subject['branch_id']; ?></td>
                        <td><?php echo $subject['subject_name']; ?></td>
                        <td><?php echo $subject['fees']; ?></td>
                        <td><?php echo $subject['tax']; ?></td>
                        <td>
    <button onclick="toggleEditFormVisibility(<?php echo $subject['id']; ?>, '<?php echo $subject['subject_name']; ?>', <?php echo $subject['fees']; ?>, <?php echo $subject['tax']; ?>)" class="btn btn-warning btn-sm">Edit</button>
    <a href="view_subject.php?delete=true&subject_id=<?php echo $subject['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add any additional content or features as needed -->

        <!-- Optional: Add a button to go back to the Master Dashboard -->
      

        <!-- Edit Subject Modal -->
       <!-- Edit Subject Modal -->
<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content edit-modal small-modal">
            <div class="modal-header text-center">
                <span class="close" onclick="toggleEditFormVisibility()">&times;</span>
            </div>
            <div class="modal-body">
                <form action="view_subject.php" method="post">
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label for="updated_subject_name">Subject Name:</label>
                            <input type="text" class="form-control" name="updated_subject_name" required>
                        </div>
                        <div class="form-group col-12">
                            <label for="updated_fees">Fees:</label>
                            <input type="number" class="form-control" name="updated_fees" required>
                        </div>
                        <div class="form-group col-12">
                            <label for="updated_tax">Tax:</label>
                            <input type="number" class="form-control" name="updated_tax" required>
                        </div>
                    </div>

                    <input type="hidden" name="subject_id" value="">
                    <button type="submit" class="btn btn-primary" name="update_subject">Update Subject</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    function toggleEditFormVisibility(subjectId, subjectName, fees, tax) {
        var modal = document.getElementById("editSubjectModal");

        if (modal.style.display === "none" || modal.style.display === "") {
            // Show the modal
            modal.style.display = "block";

            // Center the modal on the screen
            var modalContent = modal.querySelector(".modal-content");
            modalContent.style.marginTop = (window.innerHeight - modalContent.clientHeight) / 2 + "px";
            modalContent.style.marginLeft = (window.innerWidth - modalContent.clientWidth) / 2 + "px";

            // Populate the form with subject data for editing
            document.getElementsByName("subject_id")[0].value = subjectId;
            document.getElementsByName("updated_subject_name")[0].value = subjectName;
            document.getElementsByName("updated_fees")[0].value = fees;
            document.getElementsByName("updated_tax")[0].value = tax;
        } else {
            // Hide the modal
            modal.style.display = "none";
        }
    }

    function resetSearch() {
            // Reset the search input value to empty
            document.getElementById('search').value = '';

            // Submit the form to show all details
            document.querySelector('form').submit();
        }
</script>

