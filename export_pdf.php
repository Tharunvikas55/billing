<?php
require_once("config.php");
require_once("dompdf/autoload.inc.php");
use Dompdf\Dompdf;
extract($_POST);

if(isset($submit))
{
    $sql ="select * from invoices order by id desc";
    $query =mysqli_query($conn, $sql);   

    // Fetch the branch ID from the first row of the result set
    $branchID = '';
    if ($row = mysqli_fetch_assoc($query)) {
        $branchID = $row['branch_id'];
    }

    // Fetch the branch name based on the branch ID
    $sqlBranch = "SELECT branch_name FROM branches WHERE branch_id = '$branchID'";
    $resultBranch = mysqli_query($conn, $sqlBranch);
    $branchName = "Unknown Branch";
    if ($resultBranch && mysqli_num_rows($resultBranch) > 0) {
        $rowBranch = mysqli_fetch_assoc($resultBranch);
        $branchName = $rowBranch['branch_name'];
    }



    // Now build the HTML for the invoice report
    $html ='';
    $html.='
    
    <h1 style="text-align:center;" class="text-center mb-4">Invoice Report - Branch ID: '.$branchID.'</h1>
    <h2 class="text-center">Branch Name: '.$branchName.'</h2>

    <table style ="width:100%; border-collapse:collapse;">
    <tr>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 5%;">ID</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 5%;">Branch ID</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Invoice Number</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Student ID</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Invoice Time</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Invoice Date</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Contact</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Paid Amount</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Balance Amount</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Due Amount</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:left; width: 10%;">Status</th>
        <th style ="border:1px solid #ddd; padding:8px; text-align:center; width: 25%;">Subject Name</th>
    </tr>';

    if(mysqli_num_rows($query) > 0)
    {
        $count = 1;
        foreach($query as $data)
        {
            $subjectName = $data['subject_name'];
            $subjectArray = json_decode($subjectName);

            $subjectHtml = '';
            if (is_array($subjectArray) && count($subjectArray) > 0) {
                foreach ($subjectArray as $subjectGroup) {
                    foreach ($subjectGroup as $subject) {
                        $subjectHtml .= '<li>' . $subject . '</li>';
                    }
                }
            } else {
                $subjectHtml = $subjectName;
            }

            $html .= '
            <tr>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$count.'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["branch_id"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["invoice_number"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["student_id"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.substr($data["time"],11,19).'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.substr($data["invoice_date"], 0, 10).'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["contact_number"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["paid_amount"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["balance_amount"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["due_amount"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:left;">'.$data["invoice_status"].'</td>
                <td style ="border:1px solid #ddd; padding:8px; text-align:center;">'.$subjectHtml.'</td>
            </tr>';
            $count++;
        }
    }
    else
    {
        $html .= '
        <tr>
            <td colspan ="12" style ="border:1px solid #ddd; padding:8px; text-align:left;">No Data</td>
        </tr>';
    }

    $html .= '</table>';

    // Create a Dompdf instance
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper("A3", "portrait");
    $dompdf->render();
    $dompdf->stream("data.pdf");
}
?>
