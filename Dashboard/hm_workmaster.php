<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: 'info',
    title: 'HM Work Master',
    text: 'You are now accessing HM Work Master module.',
    confirmButtonText: 'Continue'
}).then(() => {
    window.location.href = 'hm_workmaster.php';
});
</script>

</body>
</html>