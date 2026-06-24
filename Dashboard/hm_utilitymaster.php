<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: 'info',
    title: 'HM Utility Master',
    text: 'You are now accessing HM Utility Master module.',
    confirmButtonText: 'Continue'
}).then(() => {
    window.location.href = 'hm_utilitymaster.php';
});
</script>

</body>
</html>