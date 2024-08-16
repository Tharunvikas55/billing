<!-- ... Remaining HTML code ... -->

<script>
// Parse the query parameters to extract branchId and students
var urlParams = new URLSearchParams(window.location.search);
var branchId = urlParams.get('branchId');
var students = JSON.parse(decodeURIComponent(urlParams.get('students')));

// Display branch-specific student details
if (branchId && students) {
    displayBranchSpecificStudents(branchId, students);
}

function displayBranchSpecificStudents(branchId, students) {
    // You can implement the logic to dynamically update the HTML content with branch-specific student details
    // For example, you can populate a table with the received data
    // Here, I'm updating the document title and logging the data to the console as an example
    document.title = 'Branch ' + branchId + ' Students';
    console.log('Branch ID:', branchId);
    console.log('Students:', students);
}
</script>

<!-- ... Remaining HTML code ... -->
