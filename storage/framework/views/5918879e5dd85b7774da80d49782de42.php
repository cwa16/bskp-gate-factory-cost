<table border="1">
    <thead>
        <tr>
            <th colspan="9" style="font-weight:bold; font-size: 14px;">WAGES SUMMARY
                <?php echo e(\Carbon\Carbon::create(null, $month)->format('F Y')); ?></th>
        </tr>
        <tr style="background-color: #d1ecf1;">
            <th>No</th>
            <th>NIK</th>
            <th>Nama</th>
            <th>Total HK</th>
            <th>Original (jam)</th>
            <th>Final (jam)</th>
            <th>Rp (OT)</th>
            <th>Gaji</th>
            <th>Total Gaji + Overtime</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $tot = $employeeTotals[$emp->nik]; ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($emp->nik); ?></td>
                <td><?php echo e($emp->name); ?></td>
                <td style="mso-number-format:'0';"><?php echo e($tot['total_hk']); ?></td>
                <td style="mso-number-format:'0.0';"><?php echo e($tot['total_ot']); ?></td>
                <td style="mso-number-format:'0.0';"><?php echo e($tot['total_ot']); ?></td>
                <td style="mso-number-format:'#,##0';"><?php echo e($tot['total_ot_rp']); ?></td>
                <td style="mso-number-format:'#,##0';"><?php echo e($tot['total_hk_rp']); ?></td>
                <td style="mso-number-format:'#,##0';"><?php echo e($tot['total_rp']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/sub-job/export-summary.blade.php ENDPATH**/ ?>