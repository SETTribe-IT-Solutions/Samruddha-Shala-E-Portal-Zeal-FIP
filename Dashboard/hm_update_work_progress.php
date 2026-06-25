<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: 'info',
    title: 'HM Update Work Progress',
    text: 'You are now accessing HM Update Work Progress module.',
    confirmButtonText: 'Continue'
}).then(() => {
    window.location.href = 'hm_update_work_progress.php';
});
</script>

</body>
</html>