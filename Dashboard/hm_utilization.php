<?php
session_start();
if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
    header("Location: ../login.php");
    exit();
}

$rows = [];
$error = '';
$fund = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calc'])) {
    $fund = isset($_POST['fund']) ? (float) $_POST['fund'] : 0;
    $names = $_POST['stage_name'] ?? [];
    $percents = $_POST['stage_percent'] ?? [];
    $total = 0;

    if ($fund <= 0) {
        $error = 'Enter valid fund amount.';
    } else {
        for ($i = 0; $i < count($names); $i++) {
            $name = trim($names[$i] ?? '');
            $percent = (float) ($percents[$i] ?? 0);

            if ($name !== '' && $percent > 0) {
                $total += $percent;
                $rows[] = [
                    'name' => $name,
                    'percent' => $percent,
                    'amount' => ($fund * $percent) / 100
                ];
            }
        }

        if (empty($rows)) {
            $error = 'Add stage and percentage.';
        } elseif (abs($total - 100) > 0.01) {
            $error = 'Total percentage must be 100%.';
            $rows = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amount Utilization Report - HM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .report-title {
            text-align: center;
            color: #800000;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 0.4px;
            margin-bottom: 0.25rem;
        }

        .report-subtitle {
            text-align: center;
            color: #1f3b73;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .important-label {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0d3b66;
        }

        .hm-note {
            background: #f8f5ef;
            border-left: 4px solid #800000;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
        }

        .hm-note-title {
            color: #800000;
            font-weight: 700;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0 text-white font-weight-bold"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Samruddha Shala</h4>
            <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 1px;">E-Portal System</small>
        </div>

        <ul class="list-unstyled components">
            <p>School reporting</p>
            <li>
                <a href="hm_dashboard.php"><i class="fa-solid fa-cloud-arrow-up"></i>Upload Progress</a>
            </li>
            <li>
                <a href="hm_dashboard.php"><i class="fa-solid fa-clock-rotate-left"></i>Report History</a>
            </li>
            <li class="active">
                <a href="hm_utilization.php"><i class="fa-solid fa-chart-pie"></i>Amount Utilization Report</a>
            </li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a href="hm_dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <label for="langSelector" class="mb-0 fw-semibold" data-en="Language" data-mr="भाषा">Language</label>
                    <select id="langSelector" class="form-select form-select-sm" style="width: 130px;">
                        <option value="en">English</option>
                        <option value="mr">मराठी</option>
                    </select>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="card p-4">
                <h2 class="report-title" data-en="Amount Utilization Report (HM)" data-mr="रक्कम वापर अहवाल (HM)">Amount Utilization Report (HM)</h2>
                <h5 class="report-subtitle" data-en="Define Stages and Weightage" data-mr="प्रकल्प टप्पे व टक्केवारी निश्चित करा">Define Stages and Weightage</h5>

                <div class="hm-note">
                    <div class="hm-note-title" data-en="Note for HM" data-mr="HM साठी सूचना">Note for HM</div>
                    <div data-en="Enter total received fund first. Then write stage names (like Foundation, Structure, Finishing) and percentage for each stage. Total percentage must be exactly 100." data-mr="प्रथम एकूण मिळालेला निधी भरा. नंतर टप्प्यांची नावे (उदा. पाया, स्ट्रक्चर, फिनिशिंग) आणि प्रत्येक टप्प्याची टक्केवारी भरा. एकूण टक्केवारी नक्की 100 असावी.">
                        Enter total received fund first. Then write stage names (like Foundation, Structure, Finishing) and percentage for each stage. Total percentage must be exactly 100.
                    </div>
                </div>

                <form method="post" class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label important-label" data-en="Total Fund Received (Lakhs)" data-mr="एकूण प्राप्त निधी (लाख)">Total Fund Received (Lakhs)</label>
                        <input type="number" name="fund" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars((string) $fund); ?>" required>
                    </div>

                    <div class="col-md-8">
                        <input type="text" name="stage_name[]" class="form-control" placeholder="Stage 1 (Foundation)" data-en-placeholder="Stage 1 (Foundation)" data-mr-placeholder="टप्पा 1 (पाया)">
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="stage_percent[]" class="form-control" placeholder="%" min="0" max="100" step="0.01">
                    </div>

                    <div class="col-md-8">
                        <input type="text" name="stage_name[]" class="form-control" placeholder="Stage 2 (Structure)" data-en-placeholder="Stage 2 (Structure)" data-mr-placeholder="टप्पा 2 (स्ट्रक्चर)">
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="stage_percent[]" class="form-control" placeholder="%" min="0" max="100" step="0.01">
                    </div>

                    <div class="col-md-8">
                        <input type="text" name="stage_name[]" class="form-control" placeholder="Stage 3 (Finishing)" data-en-placeholder="Stage 3 (Finishing)" data-mr-placeholder="टप्पा 3 (फिनिशिंग)">
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="stage_percent[]" class="form-control" placeholder="%" min="0" max="100" step="0.01">
                    </div>

                    <div class="col-12">
                        <button type="submit" name="calc" value="1" class="btn btn-success" data-en="Calculate" data-mr="हिशोब करा">Calculate</button>
                    </div>
                </form>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger mt-3 mb-0 py-2"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($rows)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th data-en="Stage" data-mr="टप्पा">Stage</th>
                                    <th data-en="Weightage %" data-mr="टक्केवारी %">Weightage %</th>
                                    <th data-en="Amount (Lakhs)" data-mr="रक्कम (लाख)">Amount (Lakhs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float) $row['percent'], 2)); ?>%</td>
                                        <td><?php echo htmlspecialchars(number_format((float) $row['amount'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function setLanguage(lang) {
        const texts = document.querySelectorAll('[data-en][data-mr]');
        texts.forEach(function (el) {
            el.textContent = lang === 'mr' ? el.getAttribute('data-mr') : el.getAttribute('data-en');
        });

        const placeholders = document.querySelectorAll('[data-en-placeholder][data-mr-placeholder]');
        placeholders.forEach(function (el) {
            el.placeholder = lang === 'mr' ? el.getAttribute('data-mr-placeholder') : el.getAttribute('data-en-placeholder');
        });

        localStorage.setItem('hmLang', lang);
    }

    const langSelector = document.getElementById('langSelector');
    const savedLang = localStorage.getItem('hmLang') || 'en';
    langSelector.value = savedLang;
    setLanguage(savedLang);

    langSelector.addEventListener('change', function () {
        setLanguage(this.value);
    });
</script>
</body>
</html>
