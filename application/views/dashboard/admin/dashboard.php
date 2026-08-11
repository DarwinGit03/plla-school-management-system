<div class="mb-4">

    <h2 class="fw-bold">

        Administrator Dashboard

    </h2>

    <p class="text-muted">

        Welcome back,
        <?= $current_user->first_name ?? 'Administrator'; ?>

    </p>

</div>

<?php

$this->load->view(
    'dashboard/widgets/statistics_chart'
);

?>

<div class="row mt-4">

    <div class="col-lg-8">

        <?php

        $this->load->view(
            'dashboard/widgets/enrollment_chart'
        );

        ?>

    </div>

    <div class="col-lg-4">

        <?php

        $this->load->view(
            'dashboard/widgets/attendance_chart'
        );

        ?>

    </div>

</div>