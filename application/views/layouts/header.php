<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= isset($title) ? $title : 'PLLA Management System'; ?>
    </title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo.png'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">  <!-- notification -->

    <!-- <link rel="stylesheet" href="assets/css/verify-otp.css"> -->
    
    <?php if(isset($page_css)): ?>
        <link rel="stylesheet" href="<?= base_url($page_css); ?>"></link>
    <?php endif; ?>

</head>
<body>
    
    <script>
        const BASE_URL = "<?= base_url();?>";
    </script>