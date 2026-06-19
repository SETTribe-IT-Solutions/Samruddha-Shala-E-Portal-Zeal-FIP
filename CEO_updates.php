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
    <!-- Custom Style Sheet -->
    <link href="Dashboard/css/style.css" rel="stylesheet">
    <style>
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
    </style>
</head>
<body>

    <div id="wrapper">
        <!-- Sidebar Navigation -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h4 class="mb-0 text-white font-weight-bold"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Samruddha Shala</h4>
                <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 1px;">E-Portal System</small>
            </div>

            <!-- Sidebar Menu -->
            <ul class="list-unstyled components">
                <p>School reporting</p>
                <li id="nav-hm-report">
                    <a href="Dashboard/hm_dashboard.php">
                        <i class="fa-solid fa-cloud-arrow-up"></i>Upload Progress
                    </a>
                </li>
                <li id="nav-hm-history">
                    <a href="Dashboard/hm_dashboard.php">
                        <i class="fa-solid fa-clock-rotate-left"></i>Report History
                    </a>
                </li>
                <li class="active" id="nav-ceo-updates">
                    <a href="CEO_updates.php">
                        <i class="fa-solid fa-file-lines"></i>CEO Updates
                    </a>
                </li>
            </ul>

            <div class="mt-auto p-4 border-top border-secondary border-opacity-10 text-center text-muted" style="font-size: 0.75rem;">
                <p class="mb-0">Kolhapur District Board</p>
                <span style="font-size: 0.7rem;">Version 2.4 (Zeal FIP)</span>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Header Top Bar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
                        <i class="fas fa-align-left"></i>
                    </button>

                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">CEO Updates & Assigned Tasks</h5>
                    </div>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Active User Indicator -->
                        <div class="d-flex align-items-center border-start ps-4">
                            <div class="text-end me-3 d-none d-md-block">
                                <p class="mb-0 fw-bold fs-6"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'User'; ?></p>
                                <small class="text-muted"><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'User'; ?></small>
                            </div>
                            <span class="role-badge badge-hm"><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'User'; ?></span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="container-fluid p-4">

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
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        }
    </script>

    <?php include("include/footer.php"); ?>
</body>
</html>
