<?php
include 'master.php';

if (isset($_GET['branch_id'])) {
    $branchId = $_GET['branch_id'];
    
    // Fetch the branch name based on branch_id
    $branchName = getBranchNameById($conn, $branchId);

    $branchSubjects = getBranchSubjects($conn, $branchId);
} else {
    // Redirect to the master dashboard if branch ID is not provided
    header("Location: master_dashboard.php");
    exit();
}

function getBranchNameById($conn, $branchId) {
    $query = "SELECT branch_name FROM branches WHERE branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchId);
    $stmt->execute();
    $stmt->bind_result($branchName);
    $stmt->fetch();
    $stmt->close();

    return $branchName;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Branch Subjects</title>
  
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style/viw_branch_student.css">
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
.mt-4{
    text-align: center;
}
.row{
    padding-top: 50px;
}
.cd:hover .card:hover {
    background-color: #34dd86; /* Change this to your desired color */
    color: #e8e8e8; /* Change this to your desired text color */
}

        </style>
</head>
<body>
    <div class="container">

        <h1 class="mt-4">View Subjects for Branch <?php echo $branchName; ?></h1>

        <div class="row cd">
    <?php foreach ($branchSubjects as $subject) : ?>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo $subject['subject_name']; ?></h5>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


        <!-- Optional: Add a button to go back to the Master Dashboard -->
        <a href="master_dashboard.php" class="btn btn-primary mt-4">Back to Master Dashboard</a>
    </div>
</body>
</html>
