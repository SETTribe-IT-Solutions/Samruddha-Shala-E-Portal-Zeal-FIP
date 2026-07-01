<?php

session_start();

require_once '../include/dbConfig.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'CEO') {
    header("Location: ceo_dashboard.php");
    exit();
}

if(isset($_POST['save']))
{
    $hm_id = $_SESSION['user_id'];

    $work_type         = $_POST['work_type'];
    $fund_source       = $_POST['fund_source'];
    $sanctioned_amount = $_POST['sanctioned_amount'];
    $work_name         = $_POST['work_name'];
    $remarks           = $_POST['share'];

    $expense_amounts = $_POST['expense_amount'];
    $expense_dates   = $_POST['expense_date'];
    $expense_remarks = $_POST['expense_remark'];

    $total_spent = 0;

    foreach($expense_amounts as $amount)
    {
        $total_spent += (float)$amount;
    }

    if($total_spent > $sanctioned_amount)
    {
        echo "<script>
                alert('Amount spent cannot exceed sanctioned amount');
                window.history.back();
              </script>";
        exit();
    }

    for($i=0; $i<count($expense_amounts); $i++)
    {
        $amount         = $expense_amounts[$i];
        $expense_date   = $expense_dates[$i];
        $expense_remark = $expense_remarks[$i];

        $sql = "INSERT INTO amount_utilization
                (
                    hm_id,
                    work_type,
                    fund_source,
                    sanctioned_amount,
                    work_name,
                    remarks,
                    amount_spent,
                    expense_date,
                    expense_remark
                )
                VALUES
                (
                    ?,?,?,?,?,?,?,?,?
                )";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "issdssdss",
            $hm_id,
            $work_type,
            $fund_source,
            $sanctioned_amount,
            $work_name,
            $remarks,
            $amount,
            $expense_date,
            $expense_remark
        );

        $stmt->execute();
    }

    echo "
    <script>
        alert('Data Saved Successfully');
        window.location='amount_utilization_ceo.php';
    </script>";
}
?>
