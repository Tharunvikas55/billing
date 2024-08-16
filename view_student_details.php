<?php
include 'master.php';

// Fetch branches for dropdown
$branches = getMasterBranches($conn);

// Fetch all students from all branches
$allStudents = getAllStudents($conn);


// Handle Form Submission for Editing Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    // Get the submitted data
    $studentID = $_POST['student_id'];
    $editStudentName = $_POST['editStudentName'];
    $editContact = $_POST['editContact'];
    $editAddress = $_POST['editAddress'];
    $editParentName = $_POST['editParentName'];

    // Update the student in the database
    $updateResult = updateStudent($conn, $studentID, $editStudentName, $editContact, $editAddress, $editParentName);

    // Check if the update was successful
    if (strpos($updateResult, 'Error') === false) {
        // Fetch the updated data if needed
        $allStudents = getAllStudents($conn);
    } else {
        // Handle update error (replace this with appropriate error handling)
        echo $updateResult;
    }
}

// Handle delete request
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $studentIdToDelete = $_GET['id'];
    deleteStudent($conn, $studentIdToDelete);
    header("Location: view_student_details.php"); // Redirect after deleting to refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./style/view_student_details.css"> <!-- Add your custom styles if needed -->
    <style>
         #but{
            position: fixed;
            top: 3rem;
            right:4rem;
         }
         #tbl{
            margin-bottom:4rem;
         }
         .scrollable {
            height: 80vh; /* Adjust the height as needed */
            overflow-y: auto;
         }

        </style>
</head>
<body style="height: 100%; overflow: hidden;">

<div class="container mt-5">


<div class="row">
        
        <div class="col-md-6 mx-auto text-center">
            <h1 class="mb-6">View All Students</h1>
        </div>
    </div>
    <div class="scrollable"> <!-- Add scrollable class here -->
            <a href="master_dashboard.php" class="btn btn-primary" id="but">Master Dashboard</a>
    <!-- All Students Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Parent Name</th>
                <th>Branch ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allStudents as $student) : ?>
                <tr>
                    <td><?php echo $student['id']; ?></td>
                    <td><?php echo $student['student_name']; ?></td>
                    <td><?php echo $student['contact']; ?></td>
                    <td><?php echo $student['address']; ?></td>
                    <td><?php echo $student['parent_name']; ?></td>
                    <td><?php echo $student['branch_id']; ?></td>
                    <td>
                        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?php echo $student['id']; ?>">Edit</a>
                        <a href="view_student_details.php?delete=true&id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
            </div>
</div>

<!-- Edit Student Modal -->
<?php foreach ($allStudents as $student) : ?>
    <div class="modal fade" id="editModal<?php echo $student['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Edit Student Form -->
                    <form action="view_student_details.php" method="post">
                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                        <div class="form-group">
                            <label for="editStudentName">Student Name:</label>
                            <input type="text" class="form-control" name="editStudentName" value="<?php echo $student['student_name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="editContact">Contact:</label>
                            <input type="text" class="form-control" name="editContact" value="<?php echo $student['contact']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="editAddress">Address:</label>
                            <textarea class="form-control" name="editAddress" required><?php echo $student['address']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editParentName">Parent Name:</label>
                            <input type="text" class="form-control" name="editParentName" value="<?php echo $student['parent_name']; ?>" required>
                        </div>
                        <!-- Add other form fields as needed -->

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php endforeach; ?>



<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
