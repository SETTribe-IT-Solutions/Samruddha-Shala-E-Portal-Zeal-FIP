<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <script>
        Swal.fire({
            icon: 'info',
            title: 'HM Notification',
            text: 'You are now accessing HM Notification module.',
            confirmButtonText: 'Continue'
        }).then(() => {
            window.location.href = 'hm_notification.php';
        });
    </script>

</body>

</html>