<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">

    <title>Fee Configuration Test</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .card {
            background: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;
        }

        h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .amount {
            text-align: right;
        }

        .empty {
            color: #888;
            font-style: italic;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Fee Configuration Test</h1>

        <?php if (!$fees): ?>

            <p class="empty">
                No fee configuration found.
            </p>

        <?php else: ?>

            <p>
                <strong>School Year:</strong>
                <?= html_escape($fees['configuration']->school_year); ?>
            </p>

            <p>
                <strong>Grade:</strong>
                <?= html_escape($fees['configuration']->grade_code); ?>
            </p>

            <p>
                <strong>Payment Mode:</strong>
                <?= html_escape($fees['configuration']->payment_mode); ?>
            </p>

        <?php endif; ?>

    </div>


    <?php if ($fees): ?>


        <!-- ==============================
             TUITION
        =============================== -->

        <div class="card">

            <h2>Tuition</h2>

            <table>

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="amount">
                            Amount
                        </th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($fees['tuition'])): ?>

                    <?php foreach ($fees['tuition'] as $row): ?>

                        <tr>

                            <td>
                                <?= html_escape($row->payment_sequence); ?>
                            </td>

                            <td>
                                <?= html_escape($row->payment_label); ?>
                            </td>

                            <td>
                                <?= !empty($row->payment_date)
                                    ? html_escape($row->payment_date)
                                    : '-'; ?>
                            </td>

                            <td class="amount">

                                ₱<?= number_format(
                                    (float) $row->tuition_fee,
                                    2
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="empty">
                            No tuition records.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- ==============================
             WORKTEXT
        =============================== -->

        <div class="card">

            <h2>Worktext</h2>

            <table>

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="amount">
                            Amount
                        </th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($fees['worktext'])): ?>

                    <?php foreach ($fees['worktext'] as $row): ?>

                        <tr>

                            <td>
                                <?= html_escape($row->payment_sequence); ?>
                            </td>

                            <td>
                                <?= html_escape($row->payment_label); ?>
                            </td>

                            <td>
                                <?= !empty($row->payment_date)
                                    ? html_escape($row->payment_date)
                                    : '-'; ?>
                            </td>

                            <td class="amount">

                                ₱<?= number_format(
                                    (float) $row->amount,
                                    2
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="empty">
                            No worktext records.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- ==============================
             EXTRA CURRICULAR
        =============================== -->

        <div class="card">

            <h2>Extra-Curricular</h2>

            <table>

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="amount">
                            Amount
                        </th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($fees['extra_curricular'])): ?>

                    <?php foreach ($fees['extra_curricular'] as $row): ?>

                        <tr>

                            <td>
                                <?= html_escape($row->payment_sequence); ?>
                            </td>

                            <td>
                                <?= html_escape($row->payment_label); ?>
                            </td>

                            <td>
                                <?= !empty($row->payment_date)
                                    ? html_escape($row->payment_date)
                                    : '-'; ?>
                            </td>

                            <td class="amount">

                                ₱<?= number_format(
                                    (float) $row->amount,
                                    2
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="empty">
                            No extra-curricular records.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- ==============================
             BOYS UNIFORM
        =============================== -->

        <div class="card">

            <h2>Uniform — Boys</h2>

            <table>

                <thead>

                    <tr>
                        <th>Size</th>
                        <th class="amount">Polo</th>
                        <th class="amount">Pants</th>
                        <th class="amount">Total</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($fees['uniform_boys'] as $row): ?>

                    <tr>

                        <td>
                            <?= html_escape($row->uniform_size); ?>
                        </td>

                        <td class="amount">
                            ₱<?= number_format(
                                (float) $row->top_price,
                                2
                            ); ?>
                        </td>

                        <td class="amount">
                            ₱<?= number_format(
                                (float) $row->bottom_price,
                                2
                            ); ?>
                        </td>

                        <td class="amount">

                            ₱<?= number_format(
                                (float) $row->top_price +
                                (float) $row->bottom_price,
                                2
                            ); ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- ==============================
             GIRLS UNIFORM
        =============================== -->

        <div class="card">

            <h2>Uniform — Girls</h2>

            <table>

                <thead>

                    <tr>
                        <th>Size</th>
                        <th class="amount">Blouse</th>
                        <th class="amount">Skirt</th>
                        <th class="amount">Total</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($fees['uniform_girls'] as $row): ?>

                    <tr>

                        <td>
                            <?= html_escape($row->uniform_size); ?>
                        </td>

                        <td class="amount">
                            ₱<?= number_format(
                                (float) $row->top_price,
                                2
                            ); ?>
                        </td>

                        <td class="amount">
                            ₱<?= number_format(
                                (float) $row->bottom_price,
                                2
                            ); ?>
                        </td>

                        <td class="amount">

                            ₱<?= number_format(
                                (float) $row->top_price +
                                (float) $row->bottom_price,
                                2
                            ); ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


    <?php endif; ?>

</div>

</body>
</html>