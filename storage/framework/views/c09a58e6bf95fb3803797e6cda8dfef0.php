

<?php $__env->startSection('title', 'Wages Summary'); ?>
<?php $__env->startSection('page-title', 'Wages Summary'); ?>

<?php $__env->startSection('content'); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table.dataTable {
            font-size: 12px;
            border-collapse: collapse !important;
        }

        .table thead {
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
        }

        .table th,
        .table td {
            padding: 6px 10px !important;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
        }

        .table tbody tr:hover {
            background: #f1f5f9;
        }

        .fw-bold {
            color: #0f172a;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark m-0">Wages Summary - <?php echo e(\Carbon\Carbon::create(null, $month)->format('F Y')); ?>

                </h5>
                <form action="<?php echo e(route('subjob.summary')); ?>" method="GET" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('F')); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm">
                        <?php for($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Tampilkan</button>
                    <a href="<?php echo e(route('subjob.wages.export', ['month' => $month, 'year' => $year])); ?>"
                        class="btn btn-sm btn-success px-3">Export</a>
                </form>
            </div>

            <table class="table table-hover w-100" id="wagesTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>HK</th>
                        <th>Ori (jam)</th>
                        <th>Final (jam)</th>
                        <th>Rp (OT)</th>
                        <th>Gaji</th>
                        <th>Total Gaji + OT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $tot = $employeeTotals[$emp->nik]; ?>
                        <tr>
                            <td><?php echo e($i + 1); ?></td>
                            <td><?php echo e($emp->nik); ?></td>
                            <td class="fw-medium"><?php echo e($emp->name); ?></td>
                            <td class="text-center"><?php echo e($tot['total_hk']); ?></td>
                            <td class="text-center"><?php echo e($tot['total_ot']); ?></td>
                            <td class="text-center"><?php echo e($tot['total_ot']); ?></td>
                            <td class="text-end"><?php echo e(number_format($tot['total_ot_rp'], 0, ',', '.')); ?></td>
                            <td class="text-end"><?php echo e(number_format($tot['total_hk_rp'], 0, ',', '.')); ?></td>
                            <td class="text-end fw-bold text-primary"><?php echo e(number_format($tot['total_rp'], 0, ',', '.')); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#wagesTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [10, 25, 50, 100],
                "language": {
                    "search": "Cari Karyawan:"
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/sub-job/wages-summary.blade.php ENDPATH**/ ?>