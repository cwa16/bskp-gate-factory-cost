<?php $__env->startSection('title', 'Sub Job Budgets'); ?>
<?php $__env->startSection('page-title', 'Sub Job Budgets'); ?>

<style>
    /* ========================================================
                   COMPACT & MODERN ENTERPRISE STYLING
                   ======================================================== */

    .container-fluid {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Container Freeze Pane */
    .table-responsive-freeze {
        max-width: 100%;
        max-height: 65vh;
        /* Sedikit dilebarkan ke bawah */
        overflow: auto;
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        /* Sudut tumpul modern */
        margin-bottom: 30px;
        background-color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    /* Styling Dasar Tabel */
    .table-summary {
        font-size: 11px;
        text-align: center;
        vertical-align: middle;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        font-variant-numeric: tabular-nums;
    }

    .table-summary th,
    .table-summary td {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px 4px;
        /* Dibuat compact */
        height: 28px;
        color: #334155;
    }

    .table-summary th {
        font-weight: 600;
        color: #475569;
    }

    /* ========================================================
                   STICKY LOGIC (HEADER & LEFT)
                   ======================================================== */

    /* Baris Header Bulan (Atas) */
    .sticky-top-th {
        position: sticky;
        z-index: 20;
        background-color: #f8fafc;
        border-bottom: 2px solid #cbd5e1 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 10.5px;
    }

    /* Pertemuan Header Atas & Kiri (Pojok Kiri Atas) */
    .super-sticky {
        position: sticky;
        z-index: 30 !important;
        background-color: #f1f5f9;
        color: #1e293b !important;
        border-bottom: 2px solid #cbd5e1 !important;
        text-transform: uppercase;
        font-size: 10.5px;
    }

    /* Kolom Freeze Kiri (No, Area, SubJob) */
    .sticky-left {
        position: sticky;
        z-index: 10;
        background-color: #ffffff;
    }

    /* Bayangan halus pembatas Freeze Pane */
    .col-sub {
        box-shadow: inset -6px 0 6px -6px rgba(0, 0, 0, 0.12);
        border-right: none !important;
    }

    /* ========================================================
                   STICKY LOGIC (FOOTER TOTAL)
                   ======================================================== */
    .sticky-bottom {
        position: sticky;
        bottom: 0;
        z-index: 20;
        background-color: #f8fafc !important;
        /* Abu-abu kalem, bukan kuning mencolok */
        font-weight: 700;
        border-top: 2px solid #94a3b8 !important;
        color: #0f172a;
    }

    .super-sticky-bottom {
        position: sticky;
        bottom: 0;
        z-index: 30;
        background-color: #f1f5f9 !important;
        font-weight: 700;
        border-top: 2px solid #94a3b8 !important;
        color: #0f172a;
    }

    /* ========================================================
                   UKURAN KOLOM (Presisi agar rapi)
                   ======================================================== */
    .col-no {
        left: 0px;
        width: 35px;
        min-width: 35px;
        background-color: #f8fafc;
        color: #64748b;
    }

    .col-area {
        left: 35px;
        width: 85px;
        min-width: 85px;
        background-color: #ffffff;
        font-weight: 600;
    }

    .col-sub {
        left: 120px;
        width: 170px;
        min-width: 170px;
    }

    .sub-job-cell {
        color: #1e293b;
        font-weight: 600;
        text-align: left;
        padding-left: 10px !important;
        /* Efek pudar agar warna bawaan SubJob tidak terlalu mencolok */
        background-blend-mode: multiply;
    }

    /* Form & Filter Bar */
    .border-primary {
        border-color: #3b82f6 !important;
    }

    .text-primary {
        color: #0f172a !important;
        /* Gunakan dark navy alih-alih biru terang */
    }
</style>

<?php $__env->startSection('content'); ?>

    <div class="container-fluid py-4">

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-primary">Summary Budget Plan</h5>

                <form action="<?php echo e(route('subjob-budget.index')); ?>" method="GET" class="d-flex gap-2">
                    <select name="type" class="form-select form-select-sm w-auto fw-bold text-primary border-primary">
                        <option value="gabungan" <?php echo e($type == 'gabungan' ? 'selected' : ''); ?>>Regular + Contract FL</option>
                        <option value="regular" <?php echo e($type == 'regular' ? 'selected' : ''); ?>>Hanya Regular</option>
                        <option value="contract_fl" <?php echo e($type == 'contract_fl' ? 'selected' : ''); ?>>Hanya Contract FL
                        </option>
                    </select>

                    <select name="month" class="form-select form-select-sm w-auto">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('F')); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm w-auto">
                        <?php $crYear = date('Y'); ?>
                        <?php for($y = $crYear - 1; $y <= $crYear + 1; $y++): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary px-4">Filter</button>
                </form>
            </div>
        </div>

        <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusName => $reportData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <h5 class="fw-bold mb-2 ps-1 border-start border-4 border-primary text-dark mt-4">
                <?php echo e($statusName); ?>

            </h5>

            <div class="table-responsive-freeze">
                <table class="table table-summary mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="super-sticky col-no">No</th>
                            <th rowspan="2" class="super-sticky col-area">Area</th>
                            <th rowspan="2" class="super-sticky col-sub">Sub Job</th>
                            <th colspan="<?php echo e($daysInMonth); ?>" class="sticky-top-th" style="top: 0; height: 30px;">
                                <?php echo e(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')); ?>

                            </th>
                        </tr>
                        <tr>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <th class="sticky-top-th" style="top: 30px; min-width: 30px;"><?php echo e($d); ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php $__currentLoopData = $structure; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $areaName => $subJobs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $subJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php if($index === 0): ?>
                                        <td rowspan="<?php echo e(count($subJobs)); ?>" class="sticky-left col-no"><?php echo e($no++); ?>

                                        </td>
                                        <td rowspan="<?php echo e(count($subJobs)); ?>" class="sticky-left col-area fw-bold">
                                            <?php echo e($areaName); ?></td>
                                    <?php endif; ?>

                                    <td class="sticky-left col-sub sub-job-cell"
                                        style="background-color: <?php echo e($job['color'] ?? '#fff'); ?>40;">
                                        <?php echo e($job['name']); ?>

                                    </td>

                                    <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                        <?php
                                            $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                            // Panggil variabel $reportData langsung
                                            $val = $reportData['map'][$job['id']][$dateStr] ?? 0;
                                        ?>
                                        <td><?php echo e($val > 0 ? $val : ''); ?></td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="super-sticky-bottom text-end pe-3" style="left: 0;">
                                TOTAL <?php echo e(strtoupper($statusName)); ?>

                            </td>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    // Panggil variabel $reportData langsung
                                    $total = $reportData['totals'][$d] ?? 0;
                                ?>
                                <td class="sticky-bottom text-danger">
                                    <?php echo e($total > 0 ? $total : ''); ?>

                                </td>
                            <?php endfor; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const SUBMIT_URL = "<?php echo e(route('subjob-budget.store')); ?>";

        document.querySelectorAll('.budget-input').forEach(input => {

            input.addEventListener('focus', () => input.select());

            input.addEventListener('change', function() {
                fetch(SUBMIT_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        sub_job: this.dataset.subjob,
                        work_date: this.dataset.date,
                        status: this.dataset.status,
                        qty: this.value || 0
                    })
                });
            });
        });

        document.querySelectorAll('select[name="month"], select[name="year"]').forEach(sel => {
            sel.addEventListener('change', () => sel.closest('form').submit());
        });
    </script>

    <script>
        let isDragging = false;
        let dragValue = null;
        let sourceInput = null;

        /* ============ START DRAG ============ */
        document.querySelectorAll('.budget-input').forEach(input => {

            input.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return; // hanya klik kiri
                isDragging = true;
                sourceInput = this;
                dragValue = this.value;
                this.classList.add('drag-source');
            });

            input.addEventListener('mouseenter', function() {
                if (!isDragging || this === sourceInput) return;
                if (this.disabled) return;

                this.value = dragValue;
                this.classList.add('drag-fill');

                this.dispatchEvent(new Event('change')); // auto save
            });

        });

        /* ============ STOP DRAG ============ */
        document.addEventListener('mouseup', function() {
            isDragging = false;
            dragValue = null;

            document.querySelectorAll('.drag-source, .drag-fill').forEach(el => {
                el.classList.remove('drag-source', 'drag-fill');
            });
        });
    </script>

    <script>
        document.getElementById('btn-copy-prev').addEventListener('click', function() {

            if (!confirm('Copy budget dari bulan sebelumnya?\nData yang sudah ada tidak akan ditimpa.')) {
                return;
            }

            fetch("<?php echo e(route('subjob-budget.copy-prev')); ?>", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        month: "<?php echo e(request('month', now()->format('m'))); ?>",
                        year: "<?php echo e(request('year', now()->year)); ?>"
                    })
                })
                .then(res => res.json())
                .then(res => {
                    alert(res.message);
                    if (res.success) {
                        location.reload();
                    }
                });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/sub-job-budget/index.blade.php ENDPATH**/ ?>