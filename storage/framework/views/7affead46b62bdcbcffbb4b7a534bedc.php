<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

</head>



<?php $__env->startSection('title', 'Sub Job'); ?>
<?php $__env->startSection('page-title', 'Sub Job'); ?>

<style>
    /* Styling Container */
    .table-responsive-budget {
        max-height: 60vh;
        overflow: auto;
        position: relative;
        border: 1px solid #e0e0e0;
        background: #fff;
        border-radius: 6px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    }

    /* Legend Bar Sticky */
    .legend-bar {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        border-bottom: 2px solid #ddd;
        padding: 8px 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .legend-item {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #000000;
        cursor: pointer;
        transition: transform 0.1s;
    }

    .legend-item:hover {
        transform: scale(1.05);
    }

    .legend-id {
        background: rgba(255, 255, 255, 0.8);
        padding: 0 5px;
        border-radius: 3px;
        margin-right: 5px;
        font-weight: bold;
        border: 1px solid #ccc;
    }

    /* Tabel Slim Modern */
    .table-slim {
        font-size: 10px;
        text-align: center;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 100%;
        color: #444;
        font-family: 'Segoe UI', sans-serif;
    }

    .table-slim th,
    .table-slim td {
        border-right: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
        padding: 0;
        height: 26px;
        vertical-align: middle;
    }

    /* Input ID Field */
    .id-input {
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        font-size: 11px;
        color: inherit;
        /* Ikut warna parent */
        -moz-appearance: textfield;
        padding: 0;
        margin: 0;
    }

    .id-input::-webkit-outer-spin-button,
    .id-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Fokus: Border biru tebal agar tahu posisi kursor */
    .id-input:focus {
        outline: 2px solid #0d6efd;
        background-color: #fff !important;
        z-index: 5;
        position: relative;
    }

    /* Sticky Columns */
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background: #fff;
        border-right: 1px solid #ddd !important;
    }

    .sticky-top-th {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .super-sticky {
        z-index: 30 !important;
        top: 0;
    }

    /* Header */
    .bg-head-modern {
        background-color: #f8f9fa !important;
        color: #555;
        font-weight: 600;
        font-size: 9.5px;
        border-bottom: 1px solid #ddd !important;
        padding: 4px;
    }

    /* Dimensions */
    .col-no {
        left: 0px;
        width: 30px;
        min-width: 30px;
    }

    .col-nik {
        left: 30px;
        width: 70px;
        min-width: 70px;
    }

    .col-nama {
        left: 100px;
        width: 130px;
        min-width: 130px;
        text-align: left !important;
        padding-left: 5px !important;
    }

    .col-pos {
        left: 230px;
        width: 80px;
        min-width: 80px;
        border-right: 2px solid #bbb !important;
    }

    /* Indikator saat sel sedang dipilih/di-drag */
    .drag-active {
        outline: 2px dashed #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.1) !important;
        z-index: 20;
    }

    /* Ubah kursor menjadi tanda plus (crosshair) agar terasa seperti Excel */
    .id-input {
        cursor: crosshair;
    }

    /* Mencegah teks ter-blok/ter-select saat nge-drag */
    .table-slim {
        user-select: none;
        -webkit-user-select: none;
    }
</style>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-3">

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-2 d-flex align-items-center justify-content-between bg-light">
                <form action="<?php echo e(route('subjob.plan.index')); ?>" method="GET" class="d-flex gap-2 align-items-center">
                    <span class="fw-bold text-muted small"><i class="bi bi-calendar-event me-1"></i>Periode:</span>
                    <select name="month" class="form-select form-select-sm border-0 bg-white fw-bold shadow-sm"
                        style="width: 100px;">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('F')); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm border-0 bg-white fw-bold shadow-sm"
                        style="width: 70px;">
                        <?php for($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">Load</button>
                </form>
                <button type="button" class="btn btn-sm btn-warning text-dark me-1" onclick="openCopyModal()">
                    <i class="bi bi-files"></i> Copy Budget
                </button>
                <div class="text-end small text-muted">
                    Ketik <strong>ID Job</strong> atau <strong>Jam OT</strong> pada tabel. Kosongkan untuk menghapus.
                </div>
            </div>
            <div class="legend-bar">
                <span class="small fw-bold text-muted me-2">LEGEND:</span>
                <?php $__currentLoopData = $subJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="legend-item" style="background-color: <?php echo e($job->color); ?>;" title="<?php echo e($job->name); ?>">
                        <span class="legend-id"><?php echo e($job->id); ?></span> <?php echo e($job->name); ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="d-flex align-items-center mb-1"><span class="badge bg-primary me-2">Regular</span></div>
        <div class="table-responsive-budget">
            <table class="table-slim">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col super-sticky col-no bg-head-modern">No</th>
                        <th rowspan="2" class="sticky-col super-sticky col-nik bg-head-modern">NIK</th>
                        <th rowspan="2" class="sticky-col super-sticky col-nama bg-head-modern">Nama</th>
                        <th rowspan="2" class="sticky-col super-sticky col-pos bg-head-modern">Posisi</th>
                        <th rowspan="2" class="sticky-col super-sticky col-tipe bg-head-modern">Tipe</th>
                        <th colspan="<?php echo e($daysInMonth); ?>" class="sticky-top-th bg-head-modern" style="top: 0;">
                            <?php echo e(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')); ?>

                        </th>
                    </tr>
                    <tr>
                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $isSun = \Carbon\Carbon::create($year, $month, $d)->isSunday(); ?>
                            <th class="sticky-top-th bg-head-modern <?php echo e($isSun ? 'text-danger bg-light' : ''); ?>"
                                style="top: 25px; min-width: 35px;">
                                <?php echo e($d); ?>

                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $regularEmps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td rowspan="2" class="sticky-col col-no"><?php echo e($index + 1); ?></td>
                            <td rowspan="2" class="sticky-col col-nik fw-bold text-secondary"><?php echo e($emp->nik); ?></td>
                            <td rowspan="2" class="sticky-col col-nama text-dark fw-bold"><?php echo e($emp->name); ?></td>
                            <td rowspan="2" class="sticky-col col-pos text-muted"><?php echo e($emp->jabatan); ?></td>
                            <td class="sticky-col col-tipe fw-bold text-primary bg-light" style="font-size: 9px;">SUB JOB
                            </td>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                    $jobId = $existingBudgets[$emp->nik][$dateStr]['sub_job'] ?? '';
                                    $color = $jobId && isset($subJobs[$jobId]) ? $subJobs[$jobId]->color : '#fff';
                                ?>
                                <td style="background-color: <?php echo e($color); ?>; transition: background 0.2s;">
                                    <input type="number" class="id-input" value="<?php echo e($jobId); ?>"
                                        data-nik="<?php echo e($emp->nik); ?>" data-date="<?php echo e($dateStr); ?>"
                                        data-status="Regular" data-field="sub_job" placeholder="-">
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <tr>
                            <td class="sticky-col col-tipe fw-bold text-danger bg-light" style="font-size: 9px;">OT (Jam)
                            </td>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                    $otVal = $existingBudgets[$emp->nik][$dateStr]['ot'] ?? '';
                                ?>
                                <td class="<?php echo e($otVal > 0 ? 'bg-danger bg-opacity-10' : ''); ?>"
                                    style="transition: background 0.2s;">
                                    <input type="number" class="id-input text-danger" value="<?php echo e($otVal); ?>"
                                        data-nik="<?php echo e($emp->nik); ?>" data-date="<?php echo e($dateStr); ?>" step="0.5"
                                        data-status="Regular" data-field="ot" placeholder="-">
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center mb-1 mt-3"><span class="badge bg-success me-2">Contract FL</span></div>
        <div class="table-responsive-budget">
            <table class="table-slim">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col super-sticky col-no bg-head-modern">No</th>
                        <th rowspan="2" class="sticky-col super-sticky col-nik bg-head-modern">NIK</th>
                        <th rowspan="2" class="sticky-col super-sticky col-nama bg-head-modern">Nama</th>
                        <th rowspan="2" class="sticky-col super-sticky col-pos bg-head-modern">Posisi</th>
                        <th rowspan="2" class="sticky-col super-sticky col-tipe bg-head-modern">Tipe</th>
                        <th colspan="<?php echo e($daysInMonth); ?>" class="sticky-top-th bg-head-modern" style="top: 0;">
                            <?php echo e(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')); ?>

                        </th>
                    </tr>
                    <tr>
                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $isSun = \Carbon\Carbon::create($year, $month, $d)->isSunday(); ?>
                            <th class="sticky-top-th bg-head-modern <?php echo e($isSun ? 'text-danger bg-light' : ''); ?>"
                                style="top: 25px; min-width: 35px;">
                                <?php echo e($d); ?>

                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $contractEmps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td rowspan="2" class="sticky-col col-no"><?php echo e($index + 1); ?></td>
                            <td rowspan="2" class="sticky-col col-nik fw-bold text-secondary"><?php echo e($emp->nik); ?></td>
                            <td rowspan="2" class="sticky-col col-nama text-dark fw-bold"><?php echo e($emp->name); ?></td>
                            <td rowspan="2" class="sticky-col col-pos text-muted"><?php echo e($emp->jabatan); ?></td>
                            <td class="sticky-col col-tipe fw-bold text-primary bg-light" style="font-size: 9px;">SUB JOB
                            </td>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                    $jobId = $existingBudgets[$emp->nik][$dateStr]['sub_job'] ?? '';
                                    $color = $jobId && isset($subJobs[$jobId]) ? $subJobs[$jobId]->color : '#fff';
                                ?>
                                <td style="background-color: <?php echo e($color); ?>; transition: background 0.2s;">
                                    <input type="number" class="id-input" value="<?php echo e($jobId); ?>"
                                        data-nik="<?php echo e($emp->nik); ?>" data-date="<?php echo e($dateStr); ?>"
                                        data-status="Contract FL" data-field="sub_job" placeholder="-">
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <tr>
                            <td class="sticky-col col-tipe fw-bold text-danger bg-light" style="font-size: 9px;">OT (Jam)
                            </td>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                    $otVal = $existingBudgets[$emp->nik][$dateStr]['ot'] ?? '';
                                ?>
                                <td class="<?php echo e($otVal > 0 ? 'bg-danger bg-opacity-10' : ''); ?>"
                                    style="transition: background 0.2s;">
                                    <input type="number" class="id-input text-danger" value="<?php echo e($otVal); ?>"
                                        data-nik="<?php echo e($emp->nik); ?>" data-date="<?php echo e($dateStr); ?>" step="0.5"
                                        data-status="Contract FL" data-field="ot" placeholder="-">
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="modalCopy" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning-subtle">
                        <h6 class="modal-title fw-bold">Copy Budget Plan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCopyBudget">
                            <div class="mb-2">
                                <label class="small fw-bold">Status Karyawan</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="Regular">Regular</option>
                                    <option value="Contract FL">Contract FL</option>
                                </select>
                            </div>
                            <hr class="my-2">
                            <div class="mb-2">
                                <label class="small fw-bold text-muted">Dari (Sumber)</label>
                                <div class="d-flex gap-1">
                                    <select name="from_month" class="form-select form-select-sm">
                                        <?php for($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo e($m); ?>" <?php echo e($month - 1 == $m ? 'selected' : ''); ?>>
                                                <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('M')); ?>

                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="from_year" class="form-select form-select-sm">
                                        <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                                        <option value="<?php echo e($year - 1); ?>"><?php echo e($year - 1); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-primary">Ke (Target)</label>
                                <div class="d-flex gap-1">
                                    <select name="to_month" class="form-select form-select-sm fw-bold">
                                        <?php for($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                                                <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('F')); ?>

                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <input type="hidden" name="to_year" value="<?php echo e($year); ?>">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        value="<?php echo e($year); ?>" disabled>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold">
                                <i class="bi bi-check-circle"></i> Proses Copy
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const jobColors = {
            <?php $__currentLoopData = $subJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($job->id); ?>: "<?php echo e($job->color); ?>",
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        };

        // Pastikan nama route ini sesuai dengan yang ada di web.php
        const BATCH_URL = "<?php echo e(route('budget-input.save-batch')); ?>";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        // --- LOGIC DRAG TO COPY (BATCH MODE) ---
        let isDragging = false;
        let dragValue = null;
        let dragField = null;
        let modifiedCells = []; // Array penampung data

        document.addEventListener('dragstart', (e) => e.preventDefault());

        document.querySelectorAll('.id-input').forEach(input => {

            // 1. MOUSE DOWN (Mulai Drag)
            input.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return;
                isDragging = true;
                dragValue = this.value;
                dragField = this.getAttribute('data-field');
                modifiedCells = []; // Reset antrian

                // Masukkan sel pertama ke antrian juga
                queueChange(this, dragValue);
            });

            // 2. MOUSE ENTER (Saat mouse digeser ke sel sebelah)
            input.addEventListener('mouseenter', function() {
                if (!isDragging) return;
                // Cegah copy lintas baris
                if (this.getAttribute('data-field') !== dragField) return;
                if (this.value === dragValue) return;

                // Masukkan sel yang dilewati ke antrian
                queueChange(this, dragValue);
            });

            // 3. EVENT CHANGE (Input manual ketik keyboard)
            input.addEventListener('change', function() {
                if (isDragging) return; // Abaikan jika sedang proses drag

                queueChange(this, this.value);
                // Langsung kirim 1 data jika ketik manual
                sendBatchRequest();
            });

            // Update warna visual langsung saat angka diketik
            input.addEventListener('input', function() {
                updateVisualColor(this, this.value);
            });

            input.addEventListener('focus', function() {
                this.select();
            });
        });

        // 4. MOUSE UP (Selesai drag, kirim semua antrian ke server)
        document.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                sendBatchRequest();
            }
        });

        // --- FUNGSI HELPER ---

        function queueChange(el, val) {
            el.value = val;
            updateVisualColor(el, val);

            // Efek kedip biru
            el.parentElement.classList.add('drag-active');
            setTimeout(() => el.parentElement.classList.remove('drag-active'), 300);

            // Pastikan tidak duplikat di dalam array
            const existsIndex = modifiedCells.findIndex(item =>
                item.nik === el.getAttribute('data-nik') &&
                item.work_date === el.getAttribute('data-date') &&
                item.field === el.getAttribute('data-field')
            );

            if (existsIndex === -1) {
                modifiedCells.push({
                    nik: el.getAttribute('data-nik'),
                    work_date: el.getAttribute('data-date'),
                    status: el.getAttribute('data-status'),
                    field: el.getAttribute('data-field'),
                    value: val
                });
            } else {
                modifiedCells[existsIndex].value = val;
            }
        }

        function sendBatchRequest() {
            if (modifiedCells.length === 0) return;

            // Copy data agar array utama bisa di-reset untuk drag selanjutnya
            const dataToSend = [...modifiedCells];
            modifiedCells = []; // Reset

            fetch(BATCH_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        changes: dataToSend
                    })
                })
                .then(async (res) => {
                    // Tangkap error validasi dari Laravel (HTTP 422 atau 500)
                    if (!res.ok) {
                        const errData = await res.json();
                        throw new Error(errData.message || "Server error: " + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        alert("Gagal menyimpan data: " + data.message);
                    }
                })
                .catch(err => {
                    console.error("Gagal Request Batch:", err);
                    alert("Terjadi kesalahan jaringan atau Server. Lihat Console (F12) untuk detail.");
                });
        }

        function updateVisualColor(el, val) {
            const parentTd = el.parentElement;
            const field = el.getAttribute('data-field');

            if (field === 'sub_job') {
                if (val && jobColors[val]) {
                    parentTd.style.backgroundColor = jobColors[val];
                } else {
                    parentTd.style.backgroundColor = '#fff';
                }
            } else if (field === 'ot') {
                if (val > 0) {
                    parentTd.classList.add('bg-danger', 'bg-opacity-10');
                } else {
                    parentTd.classList.remove('bg-danger', 'bg-opacity-10');
                }
            }
        }
    </script>

    <script>
        // SCRIPT MODAL COPY BUDGET
        function openCopyModal() {
            new bootstrap.Modal(document.getElementById('modalCopy')).show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const formCopy = document.getElementById('formCopyBudget');
            if (formCopy) {
                formCopy.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!confirm('Yakin menyalin budget? Data target akan dihapus dan diganti.')) return;

                    let formData = new FormData(this);

                    fetch("<?php echo e(route('budget.copy')); ?>", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": CSRF_TOKEN,
                                "Accept": "application/json"
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert('Gagal: ' + data.message);
                            }
                        })
                        .catch(err => alert('Terjadi kesalahan sistem saat Copy.'));
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/sub-job-plan/index.blade.php ENDPATH**/ ?>