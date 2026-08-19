<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0">Input Budget Cost per Kg (Harian)</h4>
                <p class="text-muted small m-0 mt-1">Isi target budget per tanggal. Kosongkan jika tidak ada target.</p>
            </div>

            <form action="<?php echo e(route('budget-cpk.index')); ?>" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select fw-medium shadow-sm">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                            <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('F')); ?>

                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-select fw-medium shadow-sm">
                    <?php $crYear = date('Y'); ?>
                    <?php for($y = $crYear - 1; $y <= $crYear + 2; $y++): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary shadow-sm px-4">Tampilkan</button>
            </form>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('budget-cpk.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary"><i class="bi bi-grid-3x3 me-2"></i>Spreadsheet Editor</span>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="fillDown()">
                            <i class="bi bi-arrow-down-square me-1"></i>Salin Tgl 1 ke Bawah
                        </button>
                        <button type="submit" class="btn btn-success btn-sm fw-bold">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-bordered table-hover mb-0 text-center align-middle"
                        style="font-variant-numeric: tabular-nums;">
                        <thead class="bg-light sticky-top" style="z-index: 10;">
                            <tr>
                                <th width="10%">Tanggal</th>
                                <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($area); ?> <br><small class="text-muted fw-normal">(Rp/Kg)</small></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                    $isWeekend = \Carbon\Carbon::parse($dateStr)->isWeekend();
                                ?>
                                <tr style="<?php echo e($isWeekend ? 'background-color: #fdf2f2;' : ''); ?>">
                                    <td class="fw-bold <?php echo e($isWeekend ? 'text-danger' : 'text-secondary'); ?>">
                                        <?php echo e($d); ?> <br>
                                        <small
                                            class="fw-normal opacity-75"><?php echo e(\Carbon\Carbon::parse($dateStr)->translatedFormat('D')); ?></small>
                                    </td>
                                    <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            // Tarik value jika ada di database
                                            $val = $budgetMap[$dateStr][$area] ?? '';
                                        ?>
                                        <td class="p-1">
                                            <input type="number" step="0.01"
                                                name="budgets[<?php echo e($dateStr); ?>][<?php echo e($area); ?>]"
                                                class="form-control form-control-sm text-center border-0 bg-transparent input-budget"
                                                data-area="<?php echo e($area); ?>" value="<?php echo e($val > 0 ? (float) $val : ''); ?>"
                                                placeholder="-">
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <script>
        function fillDown() {
            if (!confirm('Angka di Tanggal 1 akan disalin ke seluruh tanggal di bawahnya. Lanjutkan?')) return;

            let areas = <?php echo json_encode($areas, 15, 512) ?>;

            areas.forEach(function(area) {
                // Ambil input pertama (Tanggal 1) untuk area ini
                let inputs = document.querySelectorAll(`input[data-area="${area}"]`);
                if (inputs.length > 0) {
                    let valToCopy = inputs[0].value;
                    // Salin ke input 2 sampai 31
                    for (let i = 1; i < inputs.length; i++) {
                        inputs[i].value = valToCopy;
                    }
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/budget-cpk/index.blade.php ENDPATH**/ ?>