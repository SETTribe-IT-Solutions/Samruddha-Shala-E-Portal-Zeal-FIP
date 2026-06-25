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

<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        html: `
            <div style="text-align: center; padding: 10px 0;">
                <img src="../images/maintenance_boy.png?v=2" alt="Maintenance Boy" style="width: 250px; margin-bottom: 20px; border-radius: 15px;">
                <h2 style="color: #1e293b; font-weight: 800; margin-bottom: 15px;">
                    We're Under <span style="color: #2563eb;">Maintenance!</span>
                </h2>
                <p style="color: #475569; font-size: 1.05rem; line-height: 1.5; margin-bottom: 30px;">
                    We're currently working on making things better.<br>
                    Please check back again soon!
                </p>
                <div style="background-color: #f0f4ff; color: #1e3a8a; padding: 12px 25px; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 0.95rem;">
                    <i class="fa-regular fa-face-smile text-primary me-2"></i> 
                    Thank you for your patience and support! 
                    <i class="fa-solid fa-heart text-primary ms-1"></i>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        allowOutsideClick: false,
        width: '550px',
        padding: '2em',
        customClass: {
            popup: 'rounded-4 shadow-lg border-0'
        }
    }).then(() => {
        // Automatically redirect back when the user closes the alert
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'ceo_dashboard.php';
        }
    });
});
</script>

</body>
</html>
