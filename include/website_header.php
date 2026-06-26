<?php
$local_prefix = isset($root_prefix) ? $root_prefix : ((basename(getcwd()) === 'Dashboard') ? '../' : '');
?>
<link rel="stylesheet" href="<?php echo $local_prefix; ?>css/header.css?v=<?php echo time(); ?>">

<div class="site-banner-wrapper">

    <div class="site-banner">

        <!-- Left Logo -->
        <div class="site-banner-left">
            <div class="site-logo-box">
                <img src="<?php echo $local_prefix; ?>images/THREELIONS__1_-removebg-preview.png"
                     alt="Satyamev Jayate"
                     class="site-banner-logo">
            </div>
        </div>

        <!-- Center Title -->
        <div class="site-banner-center">

            <div class="site-banner-title">
                Samruddha Shala E-Portal
            </div>

            <div class="site-banner-subtitle">
                जिल्हा परिषद, कोल्हापूर
            </div>

        </div>

        <!-- Right Logo -->
        <div class="site-banner-right">
            <div class="site-logo-box">
                <img src="<?php echo $local_prefix; ?>images/demo.jpg"
                     alt="ZP Kolhapur"
                     class="site-banner-logo">
            </div>
        </div>

    </div>

</div>
