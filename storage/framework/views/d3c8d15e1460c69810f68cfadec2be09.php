

<?php $__env->startSection('title', 'Sub Job'); ?>
<?php $__env->startSection('page-title', 'Sub Job'); ?>

<style>
    /* Efek Berhasil Disimpan */
    .save-success {
        animation: flashGreen 1s;
    }

    @keyframes flashGreen {
        0% {
            background-color: #dcfce7;
            color: #16a34a;
        }

        100% {
            background-color: transparent;
            color: #0284c7;
        }
    }

    /* --- STRUKTUR & CONTAINER --- */
    .filter-box {
        background: #fff;
        padding: 12px 15px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        margin-bottom: 15px;
    }

    .table-responsive-freeze {
        max-width: 100%;
        height: 75vh;
        overflow: auto;
        position: relative;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
    }

    /* --- DESAIN TABEL TIMESHEET --- */
    .ts-table {
        font-size: 10px;
        text-align: center;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 100%;
        color: #333;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .ts-table th,
    .ts-table td {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 4px 6px;
        vertical-align: middle;
        box-sizing: border-box;
        background-color: #fff;
    }

    .ts-table tr th:first-child,
    .ts-table tr td:first-child {
        border-left: none;
    }

    .ts-table thead tr:first-child th {
        border-top: none;
    }

    /* Header Styling */
    .bg-header {
        background-color: #f8fafc !important;
        color: #0f172a;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 9px;
    }

    /* Sortable Header Hover */
    .sortable {
        cursor: pointer;
        transition: 0.2s;
    }

    .sortable:hover {
        background-color: #e2e8f0 !important;
        color: #0284c7 !important;
    }

    /* Warna Libur */
    .bg-libur {
        background-color: #fff2f2 !important;
        color: #dc2626 !important;
        font-weight: 600;
    }

    .text-red {
        color: #dc2626 !important;
        font-weight: bold;
    }

    /* Input Revisi Modern */
    .rev-input {
        width: 100%;
        border: none;
        text-align: center;
        background: transparent;
        font-weight: 700;
        color: #0284c7;
        font-size: 10px;
        padding: 0;
        margin: 0;
        -moz-appearance: textfield;
        cursor: pointer;
    }

    .rev-input::-webkit-outer-spin-button,
    .rev-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .rev-input:focus {
        outline: none;
        border-bottom: 1px solid #0284c7;
    }

    .rev-input::placeholder {
        color: #cbd5e1;
        font-weight: normal;
    }

    /* Wages Styling */
    .bg-wages-brutto {
        background-color: #c55a5a !important;
        color: #ffffff !important;
        font-weight: bold;
        font-size: 11px;
    }

    .bg-wages-netto {
        background-color: #ffffff !important;
        font-weight: bold;
        color: #0f172a;
        font-size: 11px;
    }

    /* --- ENGINE FREEZE PANE --- */
    .sticky-left {
        position: sticky;
        z-index: 10;
        background-color: #fff;
    }

    .sticky-top-th {
        position: sticky;
        z-index: 20;
    }

    .super-sticky {
        position: sticky;
        top: 0;
        z-index: 30 !important;
        background-color: #f8fafc !important;
    }

    /* Bayangan Pembatas Kiri & Bawah */
    .col-type {
        box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.15);
        border-right: 2px solid #cbd5e1 !important;
    }

    .h-row-2 th {
        border-bottom: none !important;
        box-shadow: 0 2px 4px -3px rgba(0, 0, 0, 0.15);
    }

    .super-sticky.col-type {
        box-shadow: 2px 2px 5px -2px rgba(0, 0, 0, 0.15) !important;
    }

    /* STICKY FOOTER (GRAND TOTAL) */
    .sticky-bottom-tf td {
        position: sticky;
        bottom: 0;
        z-index: 25;
        background-color: #f1f5f9 !important;
        border-top: 2px solid #94a3b8 !important;
        font-weight: bold;
        color: #0f172a;
    }

    /* Koordinat X Axis */
    .col-chk {
        left: 0px;
        width: 30px;
        min-width: 30px;
    }

    .col-no {
        left: 30px;
        width: 35px;
        min-width: 35px;
    }

    .col-name {
        left: 65px;
        width: 175px;
        min-width: 175px;
        text-align: left !important;
    }

    .col-type {
        left: 240px;
        width: 45px;
        min-width: 45px;
    }

    /* Y Axis Header */
    .h-row-1 {
        height: 26px;
    }

    .top-1 {
        top: 0px;
    }

    .h-row-2 {
        height: 24px;
    }

    .top-2 {
        top: 26px;
    }

    /* Fix borders for merged rows (3 Baris) */
    .row-hk td {
        border-bottom: 1px dashed #e2e8f0;
    }

    .row-ot td {
        border-bottom: 1px dashed #e2e8f0;
        border-top: none;
    }

    .row-ot-rev td {
        border-top: none;
        background-color: #f8fafc;
    }

    .custom-check {
        transform: scale(1.2);
        cursor: pointer;
    }

    .row-ot-calc td {
        border-top: none;
        background-color: #f0fdfa;
        /* Warna hijau tosca tipis untuk pembeda */
    }
</style>

<?php $__env->startSection('content'); ?>

    <div class="container-fluid py-3">
        <div class="filter-box d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-secondary">
                <i class="bi bi-table me-1"></i> Rekap Absensi: <span
                    class="text-primary"><?php echo e(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')); ?></span>
            </h5>
            <div class="d-flex gap-2">
                <input type="text" id="searchInput" class="form-control form-control-sm border-secondary shadow-sm"
                    placeholder="🔍 Cari Nama..." style="width: 130px; font-size: 11px;">

                <select id="filterJabatan" class="form-select form-select-sm border-secondary shadow-sm"
                    style="width: 110px; font-size: 11px;">
                    <option value="">Semua Area</option>
                    <option value="w mill">W Mill</option>
                    <option value="w grad">W Grad</option>
                    <option value="w proc">W Proc</option>
                    <option value="w wwtp">W WWTP</option>
                    <option value="w sh">W SH</option>
                </select>

                <select id="filterStatus" class="form-select form-select-sm border-secondary shadow-sm"
                    style="width: 110px; font-size: 11px;">
                    <option value="">Semua Status</option>
                    <option value="regular">Regular</option>
                    <option value="contract fl">Contract FL</option>
                </select>

                <button type="button" class="btn btn-sm btn-success px-3 fw-bold shadow-sm" onclick="printSelectedSlips()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Slip
                </button>

                <div class="vr mx-1"></div>

                <form action="<?php echo e(route('subjob.index')); ?>" method="GET" class="d-flex gap-2 mb-0">
                    <select name="month" class="form-select form-select-sm w-auto border-light" style="font-size: 11px;">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create(null, $m)->translatedFormat('F')); ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm w-auto border-light" style="font-size: 11px;">
                        <?php $currentYear = date('Y'); ?>
                        <?php for($y = $currentYear - 1; $y <= $currentYear + 1; $y++): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" name="btn_filters" value="1" class="btn btn-sm btn-primary px-3"
                        style="font-size: 11px;">Filter</button>
                    <button type="submit" name="btn_filter" value="2" class="btn btn-sm btn-outline-secondary px-3"
                        style="font-size: 11px;">Tarik Data</button>
                </form>
            </div>
        </div>

        <div class="table-responsive-freeze show-shadow">
            <table class="table ts-table mb-0" id="mainTable">
                <thead>
                    <tr class="h-row-1">
                        <th rowspan="2" class="super-sticky col-chk bg-header" style="top: 0;">
                            <input type="checkbox" class="form-check-input custom-check shadow-none border-secondary"
                                id="checkAll">
                        </th>
                        <th rowspan="2" class="super-sticky col-no bg-header sortable" data-sort="no" style="top: 0;">No
                            <i class="bi bi-arrow-down-up ms-1 text-muted"></i>
                        </th>
                        <th rowspan="2" class="super-sticky col-name bg-header sortable" data-sort="name"
                            style="top: 0;">Name <i class="bi bi-arrow-down-up ms-1 text-muted"></i></th>
                        <th rowspan="2" class="super-sticky col-type bg-header" style="top: 0;">DATA</th>
                        <th colspan="<?php echo e($daysInMonth); ?>" class="sticky-top-th top-1 bg-header">DATE</th>
                        <th colspan="4" class="sticky-top-th top-1 bg-header">TOTAL</th>
                        <th rowspan="2" class="sticky-top-th top-1 bg-header" style="min-width: 110px;">WAGES BRUTTO</th>
                        <th rowspan="2" class="sticky-top-th top-1 bg-header" style="min-width: 110px;">WAGES NETTO</th>
                    </tr>
                    <tr class="h-row-2">
                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php
                                $dateStr = \Carbon\Carbon::create($year, $month, $d)->format('Y-m-d');
                                $isHoliday =
                                    \Carbon\Carbon::create($year, $month, $d)->isSunday() ||
                                    isset($holidayMap[$dateStr]);
                                $title = $holidayMap[$dateStr] ?? 'Minggu';
                            ?>
                            <th class="sticky-top-th top-2 <?php echo e($isHoliday ? 'bg-libur' : 'bg-header'); ?>"
                                title="<?php echo e($title); ?>" style="width: 25px;">
                                <?php echo e($d); ?>

                            </th>
                        <?php endfor; ?>
                        <th class="sticky-top-th top-2 bg-header" style="width: 30px;">HK</th>
                        <th class="sticky-top-th top-2 bg-header" style="width: 30px;">HLY</th>
                        <th class="sticky-top-th top-2 bg-header" style="width: 30px;">OT</th>
                        <th class="sticky-top-th top-2 bg-header" style="width: 80px;">RP</th>
                    </tr>
                </thead>

                <?php
                    $monthStr = \Carbon\Carbon::create($year, $month)->translatedFormat('F');
                    $printDate = \Carbon\Carbon::now()->translatedFormat('d F Y');
                ?>

                <tbody>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $totals = $employeeTotals[$emp->nik]; ?>

                <tbody class="emp-block" data-no="<?php echo e($index + 1); ?>" data-name="<?php echo e(strtolower($emp->name)); ?>"
                    data-jabatan="<?php echo e(strtolower($emp->jabatan)); ?>" data-status="<?php echo e(strtolower($emp->status)); ?>"
                    data-ot="<?php echo e($totals['total_ot']); ?>" data-rp="<?php echo e($totals['total_rp']); ?>"
                    data-brutto="<?php echo e($totals['total_rp']); ?>"
                    data-netto="<?php echo e($totals['wages_netto'] ?? $totals['total_rp']); ?>">

                    <tr class="row-hk">
                        <td rowspan="4" class="sticky-left col-chk" style="background-color: #f8fafc;">
                            <input type="checkbox" class="form-check-input custom-check slip-check border-secondary"
                                data-name="<?php echo e(addslashes($emp->name)); ?>" data-job="<?php echo e($emp->jabatan); ?>"
                                data-hkrp="<?php echo e($totals['total_hk_rp']); ?>" data-otrp="<?php echo e($totals['total_ot_rp']); ?>"
                                data-brutto="<?php echo e($totals['total_rp']); ?>" data-spsi="<?php echo e($totals['spsi'] ?? 0); ?>"
                                data-astek="<?php echo e($totals['astek'] ?? 0); ?>" data-listrik="<?php echo e($totals['listrik'] ?? 0); ?>"
                                data-kantin="<?php echo e($totals['kantin'] ?? 0); ?>"
                                data-spdmotor="<?php echo e($totals['spd_motor'] ?? 0); ?>" data-bank="<?php echo e($totals['bank'] ?? 0); ?>"
                                data-other="<?php echo e($totals['other'] ?? 0); ?>"
                                data-totalpotongan="<?php echo e($totals['total_potongan'] ?? 0); ?>"
                                data-netto="<?php echo e($totals['wages_netto'] ?? $totals['total_rp']); ?>">
                        </td>
                        <td rowspan="4" class="sticky-left col-no row-number"><?php echo e($index + 1); ?></td>
                        <td rowspan="4" class="sticky-left col-name">
                            <div class="fw-bold text-dark" style="font-size: 11px;"><?php echo e($emp->name); ?></div>
                            <div class="text-muted" style="margin-top: 1px;"><?php echo e($emp->nik); ?> - <?php echo e($emp->status); ?>

                            </div>
                            <div class="text-muted fw-bold text-primary" style="margin-top: 1px;"><?php echo e($emp->jabatan); ?>

                            </div>
                        </td>
                        <td class="sticky-left col-type fw-bold text-secondary">HK</td>

                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $cell = $matrix[$emp->nik][$d]; ?>
                            <td class="<?php echo e($cell['is_holiday'] ? 'bg-libur' : ''); ?>" title="<?php echo e($cell['holiday_nm']); ?>">
                                <?php if(in_array($cell['desc'], ['H', 'TA', 'L']) || empty($cell['desc'])): ?>
                                    <?php echo e($cell['hk_raw']); ?>

                                <?php else: ?>
                                    <span class="text-danger fw-bold"><?php echo e($cell['desc']); ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>

                        <td class="fw-bold"><?php echo e($totals['total_hk'] > 0 ? $totals['total_hk'] : ''); ?></td>
                        <td></td>
                        <td></td>
                        <td class="text-end pe-2 text-secondary" title="Total Rp Hari Kerja">Rp
                            <?php echo e(number_format($totals['total_hk_rp'], 0, ',', '.')); ?></td>
                        <td rowspan="4" class="bg-wages-brutto text-end pe-2">Rp
                            <?php echo e(number_format($totals['total_rp'], 0, ',', '.')); ?></td>
                        <td rowspan="4" class="bg-wages-netto text-end pe-2">
                            <?php echo e(number_format($totals['wages_netto'] ?? $totals['total_rp'], 0, ',', '.')); ?></td>
                    </tr>

                    <tr class="row-ot">
                        <td class="sticky-left col-type fw-bold text-secondary">OT</td>
                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $cell = $matrix[$emp->nik][$d]; ?>
                            <td class="<?php echo e($cell['is_holiday'] ? 'bg-libur' : ''); ?> text-secondary">
                                <?php echo e((float) $cell['ot'] > 0 ? $cell['ot'] : '-'); ?>

                            </td>
                        <?php endfor; ?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr class="row-ot-rev">
                        <td class="sticky-left col-type fw-bold text-primary">OT Rev</td>
                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $cell = $matrix[$emp->nik][$d]; ?>
                            <td class="<?php echo e($cell['is_holiday'] ? 'bg-libur' : ''); ?> p-0" style="vertical-align: middle;">
                                <input type="number" step="0.5"
                                    class="rev-input <?php echo e($cell['is_holiday'] && $cell['ot_rev'] !== '' ? 'text-red' : ''); ?>"
                                    value="<?php echo e($cell['ot_rev']); ?>" placeholder="-"
                                    onchange="saveRev(this, '<?php echo e($emp->nik); ?>', '<?php echo e($cell['date']); ?>', this.value)">
                            </td>
                        <?php endfor; ?>
                        <td></td>
                        <td></td>
                        <td class="fw-bold text-primary">
                            <?php echo e($totals['total_ot'] > 0 ? number_format($totals['total_ot'], 2, '.', '') : ''); ?></td>
                        <td class="text-end pe-2 fw-bold text-primary" title="Total Rp Lembur">
                            <?php echo e($totals['total_ot_rp'] > 0 ? 'Rp ' . number_format($totals['total_ot_rp'], 0, ',', '.') : ''); ?>

                        </td>
                    </tr>

                    <!-- BARIS BARU: OT CALC -->
                    <tr class="row-ot-calc">
                        <td class="sticky-left col-type fw-bold text-success">OT Calc</td>
                        <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $cell = $matrix[$emp->nik][$d]; ?>
                            <td class="<?php echo e($cell['is_holiday'] ? 'bg-libur' : ''); ?> text-success fw-bold">
                                <?php echo e((float) $cell['ot_calc'] > 0 ? $cell['ot_calc'] : '-'); ?>

                            </td>
                        <?php endfor; ?>
                        <td></td>
                        <td></td>
                        <!-- Total Jam OT Setelah Dikali -->
                        <td class="fw-bold text-success">
                            <?php echo e($totals['total_ot_final'] > 0 ? number_format($totals['total_ot_final'], 2, '.', '') : ''); ?>

                        </td>
                        <!-- Total Rupiah Lembur ditaruh sejajar dengan OT Calc -->
                        <td class="text-end pe-2 fw-bold text-success" title="Total Rp Lembur">
                            <?php echo e($totals['total_ot_rp'] > 0 ? 'Rp ' . number_format($totals['total_ot_rp'], 0, ',', '.') : ''); ?>

                        </td>
                    </tr>
                </tbody>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>

                <tfoot>
                    <tr class="sticky-bottom-tf">
                        <td colspan="4" class="text-start fw-bold text-uppercase px-3"
                            style="font-size: 11px; letter-spacing: 0.5px;">Grand Total</td>
                        <td colspan="<?php echo e($daysInMonth); ?>"></td>
                        <td></td>
                        <td></td>
                        <td id="footerTotalOt" class="text-primary text-center fw-bold">0.00</td>
                        <td id="footerTotalRp" class="text-end pe-2 fw-bold">Rp 0</td>
                        <td id="footerTotalBrutto" class="text-end pe-2 fw-bold"
                            style="background-color: #ffd8d8 !important; color: #000;">Rp 0</td>
                        <td id="footerTotalNetto" class="text-end pe-2 fw-bold"
                            style="background-color: #e2e8f0 !important; color: #000;">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('searchInput');
            const filterStatus = document.getElementById('filterStatus');
            const filterJabatan = document.getElementById('filterJabatan');
            const tbodies = Array.from(document.querySelectorAll('.emp-block'));

            // === FUNGSI FILTER & HITUNG TOTAL LIVE ===
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusTerm = filterStatus.value.toLowerCase();
                const jabatanTerm = filterJabatan.value.toLowerCase();

                let visibleCount = 1;

                // Variabel akumulasi Grand Total harian
                let grandTotalOt = 0;
                let grandTotalRp = 0;
                let grandTotalBrutto = 0;
                let grandTotalNetto = 0;

                tbodies.forEach(tbody => {
                    const name = tbody.dataset.name;
                    const jabatan = tbody.dataset.jabatan;
                    const status = tbody.dataset.status;

                    const matchSearch = name.includes(searchTerm) || jabatan.includes(searchTerm);
                    const matchStatus = statusTerm === "" || status.includes(statusTerm);
                    const matchJabatan = jabatanTerm === "" || jabatan.includes(jabatanTerm);

                    if (matchSearch && matchStatus && matchJabatan) {
                        tbody.style.display = "";
                        tbody.querySelector('.row-number').innerText = visibleCount++;

                        // Tambahkan nilai jika karyawan lolos filter
                        grandTotalOt += parseFloat(tbody.dataset.ot || 0);
                        grandTotalRp += parseFloat(tbody.dataset.rp || 0);
                        grandTotalBrutto += parseFloat(tbody.dataset.brutto || 0);
                        grandTotalNetto += parseFloat(tbody.dataset.netto || 0);
                    } else {
                        tbody.style.display = "none";
                    }
                });

                // Update tampilan angka di TFOOT secara Realtime
                const formatCurrency = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

                document.getElementById('footerTotalOt').innerText = grandTotalOt > 0 ? grandTotalOt.toFixed(2) :
                    '0.00';
                document.getElementById('footerTotalRp').innerText = grandTotalRp > 0 ? formatCurrency(
                    grandTotalRp) : 'Rp 0';
                document.getElementById('footerTotalBrutto').innerText = grandTotalBrutto > 0 ? formatCurrency(
                    grandTotalBrutto) : 'Rp 0';
                document.getElementById('footerTotalNetto').innerText = grandTotalNetto > 0 ? formatCurrency(
                    grandTotalNetto) : 'Rp 0';

                document.getElementById('checkAll').checked = false;
            }

            if (searchInput && filterStatus && filterJabatan) {
                searchInput.addEventListener('input', filterTable);
                filterStatus.addEventListener('change', filterTable);
                filterJabatan.addEventListener('change', filterTable);
            }

            // Jalankan filter pertama kali saat halaman dibuka untuk menghitung total awal
            filterTable();

            // === FUNGSI SORTING ===
            const sortableHeaders = document.querySelectorAll('.sortable');
            const table = document.getElementById('mainTable');
            let currentSort = {
                col: 'no',
                dir: 'asc'
            };

            sortableHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const sortBy = header.dataset.sort;
                    if (currentSort.col === sortBy) {
                        currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.col = sortBy;
                        currentSort.dir = 'asc';
                    }

                    sortableHeaders.forEach(h => {
                        h.querySelector('i').className =
                            'bi bi-arrow-down-up ms-1 text-muted';
                    });
                    header.querySelector('i').className = currentSort.dir === 'asc' ?
                        'bi bi-sort-alpha-down ms-1 text-primary' :
                        'bi bi-sort-alpha-up ms-1 text-primary';

                    tbodies.sort((a, b) => {
                        let valA = a.dataset[sortBy];
                        let valB = b.dataset[sortBy];
                        if (sortBy === 'no') {
                            valA = parseInt(valA);
                            valB = parseInt(valB);
                        }
                        if (valA < valB) return currentSort.dir === 'asc' ? -1 : 1;
                        if (valA > valB) return currentSort.dir === 'asc' ? 1 : -1;
                        return 0;
                    });

                    // Susun ulang baris, pastikan TBODY ditaruh sebelum TFOOT
                    const tfoot = table.querySelector('tfoot');
                    tbodies.forEach(tbody => table.insertBefore(tbody, tfoot));
                    filterTable();
                });
            });

            // === CHECKALL SMART ===
            document.getElementById('checkAll').addEventListener('change', function() {
                let visibleCheckboxes = document.querySelectorAll(
                    '.emp-block:not([style*="display: none"]) .slip-check');
                visibleCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });

        // === SIMPAN REVISI OT (TANPA RELOAD) ===
        function saveRev(inputElement, nik, date, val) {
            fetch("<?php echo e(route('subjob.revise')); ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
                    },
                    body: JSON.stringify({
                        nik: nik,
                        date: date,
                        ot_value: val
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Beri efek kedip hijau tanpa me-reload halaman
                        inputElement.classList.remove('save-success');
                        void inputElement.offsetWidth; // Trigger reflow
                        inputElement.classList.add('save-success');
                        window.location.reload();
                    } else {
                        alert('Gagal menyimpan data!');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan koneksi jaringan!');
                });
        }

        // === CETAK MASSAL SLIP GAJI ===
        function printSelectedSlips() {
            const checkboxes = document.querySelectorAll('.slip-check:checked');
            if (checkboxes.length === 0) {
                alert('Pilih minimal satu karyawan (centang kotak paling kiri) untuk dicetak.');
                return;
            }

            const monthStr = '<?php echo e($monthStr); ?>';
            const printDate = '<?php echo e($printDate); ?>';
            // PERBAIKAN: Gunakan maximumFractionDigits: 0 agar tidak ada koma desimal
            const formatRp = (num) => {
                const val = parseFloat(num);
                if (isNaN(val) || val <= 0) return '-';
                return new Intl.NumberFormat('id-ID', {
                    maximumFractionDigits: 0
                }).format(val);
            };

            let slipsHtml = '';
            checkboxes.forEach((cb, index) => {
                const s = cb.dataset;
                const slip = `
                    <div class="slip-container">
                        <div class="bold slip-title">Slip Gaji</div>
                        <table class="w-100">
                            <tr><td width="30%">Bulan</td><td width="5%">:</td><td>${monthStr}</td></tr>
                            <tr><td>Nama</td><td>:</td><td class="bold text-red">${s.name}</td></tr>
                            <tr><td>Divisi</td><td>:</td><td>Factory</td></tr>
                            <tr><td>Pekerjaan</td><td>:</td><td>${s.job}</td></tr>
                        </table>

                        <div class="bold label-section">Komponen Gaji :</div>
                        <table class="w-100">
                            <tr><td>Bagi Hasil Upah</td><td class="val">${formatRp(s.hkrp)}</td></tr>
                            <tr><td>Libur</td><td class="val">-</td></tr>
                            <tr><td>Bagi Hasil Lembur</td><td class="val">${formatRp(s.otrp)}</td></tr>
                            <tr><td>&nbsp;</td><td class="val">-</td></tr>
                        </table>

                        <table class="w-100 mt-10">
                            <tr>
                                <td class="bold">Total :</td>
                                <td class="val"><div class="box bold">${formatRp(s.brutto)}</div></td>
                            </tr>
                        </table>

                        <div class="bold label-section">Potongan :</div>
                        <table class="w-100">
                            <tr><td>SPSI</td><td class="val">${formatRp(s.spsi)}</td></tr>
                            <tr><td>Astek</td><td class="val">${formatRp(s.astek)}</td></tr>
                            <tr><td>Listrik</td><td class="val">${formatRp(s.listrik)}</td></tr>
                            <tr><td>Kantin</td><td class="val">${formatRp(s.kantin)}</td></tr>
                            <tr><td>Spd. Motor</td><td class="val">${formatRp(s.spdmotor)}</td></tr>
                            <tr><td>BRI / Bank</td><td class="val">${formatRp(s.bank)}</td></tr>
                            <tr><td>Lain-Lain</td><td class="val">${formatRp(s.other)}</td></tr>
                        </table>

                        <table class="w-100 mt-5">
                            <tr>
                                <td class="bold">Total Potongan :</td>
                                <td class="val" style="padding-right: 8px;">${formatRp(s.totalpotongan)}</td>
                            </tr>
                        </table>

                        <table class="w-100 mt-10">
                            <tr>
                                <td class="bold">Total Upah Bagi Hasil</td>
                                <td class="val"><div class="box bold">${formatRp(s.netto)}</div></td>
                            </tr>
                        </table>

                        <div class="text-center signature-area">Gn. Batu, ${printDate}</div>
                    </div>
                `;
                slipsHtml += slip;
                if ((index + 1) % 4 === 0 && index !== checkboxes.length - 1) {
                    slipsHtml += '<div class="page-break"></div>';
                }
            });

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak Slip Gaji</title>
                    <style>
                        @page { size: A4 portrait; margin: 10mm; }
                        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; color: #000; display: flex; flex-wrap: wrap; align-content: flex-start; }
                        .slip-container { border: 3px double #000; width: 46%; height: 48vh; box-sizing: border-box; padding: 12px 15px; margin: 1vh 2%; page-break-inside: avoid; }
                        .w-100 { width: 100%; border-collapse: collapse; }
                        td { padding: 1px 0; vertical-align: top; }
                        .val { text-align: right; }
                        .bold { font-weight: bold; }
                        .text-red { color: #dc2626 !important; }
                        .box { border: 1px solid #000; padding: 2px 6px; display: inline-block; min-width: 80px; text-align: right; }
                        .text-center { text-align: center; }
                        .mt-5 { margin-top: 5px; } .mt-10 { margin-top: 10px; }
                        .label-section { margin-top: 10px; margin-bottom: 3px; }
                        .slip-title { font-size: 13px; margin-bottom: 8px; }
                        .signature-area { margin-top: 15px; }
                        .page-break { page-break-after: always; width: 100%; height: 0; margin: 0; border: 0; }
                    </style>
                </head>
                <body>
                    ${slipsHtml}
                    <script>window.onload = function() { window.print(); };<\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Anshari\App\bskp-gate-factory-cost\resources\views/sub-job/index.blade.php ENDPATH**/ ?>