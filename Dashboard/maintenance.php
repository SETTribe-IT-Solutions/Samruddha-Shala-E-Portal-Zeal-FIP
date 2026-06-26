<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - Samruddha Shala</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="card border-0 shadow-lg p-5 text-center" style="max-width: 550px; border-radius: 20px;">
        <div class="mb-4">
            <img src="../images/maintenance_boy.png?v=2" alt="Maintenance Boy" style="width: 250px; border-radius: 15px;">
        </div>
        <h2 style="color: #1e293b; font-weight: 800; margin-bottom: 15px;">
            We're Under <span class="text-primary">Maintenance!</span>
        </h2>
        <p style="color: #475569; font-size: 1.05rem; line-height: 1.5; margin-bottom: 30px;">
            We're currently working on making things better.<br>
            Please check back again soon!
        </p>
        <div class="d-inline-block bg-light text-primary px-4 py-2 mb-4" style="border-radius: 50px; font-weight: 600; font-size: 0.95rem;">
            <i class="fa-regular fa-face-smile me-2"></i> 
            Thank you for your patience and support! 
            <i class="fa-solid fa-heart ms-1"></i>
        </div>
        <div>
            <button onclick="window.history.back()" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-arrow-left me-2"></i> Go Back
            </button>
        </div>
    </div>
</body>
</html>
