<?php
session_start();

include("include/dbConfig.php");

/* Check Login */
if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - CEO Updates</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
        }

        .page-title {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .page-title h2 {
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 15px;
            font-size: 28px;
        }

        .notice-box {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .task-card {
            background: white;
            border-left: 5px solid #0d6efd;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .task-card.high-priority {
            border-left-color: #dc3545;
        }

        .task-card.medium-priority {
            border-left-color: #fd7e14;
        }

        .task-card.low-priority {
            border-left-color: #28a745;
        }

        .task-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
        }

        .task-desc {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.7;
        }

        .task-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .meta-item {
            font-size: 14px;
        }

        .meta-item strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .meta-item span {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
        }

        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }

        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .no-record {
            text-align: center;
            background: white;
            padding: 60px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .no-record i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .no-record h3 {
            color: #999;
            margin-bottom: 10px;
        }

        .back-btn {
            margin-bottom: 20px;
        }

        .container-main {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .back-btn a {
            text-decoration: none;
            color: #0d6efd;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn a:hover {
            color: #0a58ca;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">

<div class="container-main">
    <!-- Back Button -->
    <div class="back-btn">
        <a href="javascript:history.back();"><i class="fas fa-arrow-left"></i> Go Back</a>
    </div>

    <div class="page-title">
        <h2><i class="fa-solid fa-bell"></i>CEO Updates & Assigned Tasks</h2>
    </div>

    <div class="notice-box">
        <strong><i class="fa-solid fa-info-circle me-2"></i>Notice:</strong>
        All instructions and tasks assigned by CEO are displayed below.
    </div>

    <?php
        /* Check if table exists before querying */
        $table_check = "SHOW TABLES LIKE 'ceo_tasks'";
        $table_result = @mysqli_query($conn, $table_check);

        if($table_result && mysqli_num_rows($table_result) > 0)
        {
            $sql = "SELECT * FROM ceo_tasks ORDER BY task_id DESC";
            $result = @mysqli_query($conn, $sql);

            if($result && mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_assoc($result))
                {
                    $priorityClass = "low";
                    $statusClass = "pending";
                    $cardClass = "low-priority";

                    if(isset($row['priority']))
                    {
                        if($row['priority'] == "High") {
                            $priorityClass = "high";
                            $cardClass = "high-priority";
                        }
                        elseif($row['priority'] == "Medium") {
                            $priorityClass = "medium";
                            $cardClass = "medium-priority";
                        }
                    }

                    if(isset($row['status']))
                    {
                        if($row['status'] == "Pending") {
                            $statusClass = "pending";
                        }
                        elseif($row['status'] == "In Progress") {
                            $statusClass = "progress";
                        }
                        else {
                            $statusClass = "completed";
                        }
                    }

                    $dueDate = isset($row['due_date']) ? date('d M, Y', strtotime($row['due_date'])) : 'N/A';
    ?>

                    <div class="task-card <?php echo $cardClass; ?>">
                        <div class="task-title">
                            <?php echo isset($row['task_title']) ? htmlspecialchars($row['task_title']) : 'Untitled Task'; ?>
                        </div>

                        <div class="task-desc">
                            <?php echo isset($row['task_description']) ? htmlspecialchars($row['task_description']) : 'No description provided'; ?>
                        </div>

                        <div class="task-meta">
                            <div class="meta-item">
                                <strong><i class="fa-solid fa-calendar me-2"></i>Due Date</strong>
                                <span><?php echo $dueDate; ?></span>
                            </div>

                            <div class="meta-item">
                                <strong><i class="fa-solid fa-flag me-2"></i>Priority</strong>
                                <span class="priority-<?php echo $priorityClass; ?>">
                                    <?php echo isset($row['priority']) ? ucfirst($row['priority']) : 'N/A'; ?>
                                </span>
                            </div>

                            <div class="meta-item">
                                <strong><i class="fa-solid fa-circle-check me-2"></i>Status</strong>
                                <span class="status-<?php echo $statusClass; ?>">
                                    <?php echo isset($row['status']) ? ucfirst($row['status']) : 'N/A'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

    <?php
                }
            }
            else
            {
                echo "<div class='no-record'>
                        <i class='fa-solid fa-inbox'></i>
                        <h3>No CEO updates available.</h3>
                        <p class='text-muted'>All tasks will appear here once assigned by the CEO.</p>
                      </div>";
            }
        }
        else
        {
            echo "<div class='alert alert-info'>
                    <i class='fa-solid fa-info-circle me-2'></i>
                    <strong>Welcome!</strong> CEO tasks database is being set up. Tasks will appear here shortly.
                  </div>";
        }
    ?>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
