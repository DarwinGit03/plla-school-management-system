<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>

        <?= isset($title) ? $title : 'PLLA School Management System'; ?>

    </title>

    <link rel="icon"
          href="<?= base_url('assets/images/logo.png'); ?>">

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet">

    <!-- Notyf -->

    <link
        href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css"
        rel="stylesheet">

    <!-- Dashboard -->

    <link
        href="<?= base_url('assets/css/dashboard.css'); ?>"
        rel="stylesheet">

</head>

<body>

<div id="app">

    <?php
        $this->load->view('dashboard/layouts/sidebar');
    ?>

    <div id="content">

        <?php
            $this->load->view('dashboard/layouts/topbar');
        ?>

        <main class="container-fluid py-4">

            <?php
                $this->load->view($content);
            ?>

        </main>

        <?php
            $this->load->view('dashboard/layouts/footer');
        ?>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<script src="<?= base_url('assets/js/dashboard.js'); ?>"></script>
<script>
    const CSRF = {
        name: "<?= $this->security->get_csrf_token_name(); ?>",
        hash: "<?= $this->security->get_csrf_hash(); ?>"
    };
</script>


</body>

</html>