<?php $__env->startSection('content'); ?>
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0 text-secondary">Master Costs</h4>
            <button class="btn btn-primary" onclick="openModal('create')">
                <i class="bi bi-plus-lg me-1"></i> Tambah Data
            </button>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="15%">Tahun</th>
                                <th width="30%">Status Karyawan</th>
                                <th width="30%">Cost Per Day (Rp)</th>
                                <th width="10%">Last Update</th>
                                <th class="text-end pe-4" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $costs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-4"><?php echo e($index + 1); ?></td>
                                    <td class="fw-bold"><?php echo e($item->year); ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?php echo e($item->status); ?></span>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?php echo e(number_format($item->cost_per_day, 0, ',', '.')); ?>

                                    </td>
                                    <td class="small text-muted">
                                        <?php echo e($item->updated_at->format('d M Y')); ?>

                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-warning me-1"
                                            onclick="openModal('edit', <?php echo e($item); ?>)" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <form action="<?php echo e(route('master-costs.destroy', $item->id)); ?>" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Belum ada data Master Cost
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="costModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Master Cost</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="costForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div id="methodField"></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Tahun</label>
                            <select name="year" id="input_year" class="form-select" required>
                                <?php $currYear = date('Y'); ?>
                                <?php for($y = $currYear - 2; $y <= $currYear + 2; $y++): ?>
                                    <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Status Karyawan</label>
                            <select name="status" id="input_status" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Regular">Regular</option>
                                <option value="Contract FL">Contract FL</option>
                                <option value="DW">Daily Worker (DW)</option>
                                <option value="Magang">Magang / Intern</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Cost Per Day (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="cost_per_day" id="input_cost" class="form-control"
                                    placeholder="Contoh: 150000" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(mode, data = null) {
            // 1. Ambil elemen modal
            var modalElement = document.getElementById('costModal');

            // 2. Gunakan 'getOrCreateInstance' (Fitur Bootstrap 5)
            // Ini memastikan modal di-init hanya saat tombol diklik, bukan saat page load
            var myModal = bootstrap.Modal.getOrCreateInstance(modalElement);

            // 3. Reset Form & Setup Logic
            let form = document.getElementById('costForm');
            let title = document.getElementById('modalTitle');
            let methodField = document.getElementById('methodField');

            // Reset Nilai Input
            document.getElementById('input_year').value = new Date().getFullYear();
            document.getElementById('input_status').value = "";
            document.getElementById('input_cost').value = "";

            // Hapus method field (hidden input _method) jika ada sisa sebelumnya
            methodField.innerHTML = "";

            if (mode === 'create') {
                title.innerText = "Tambah Master Cost";
                form.action = "<?php echo e(route('master-costs.store')); ?>";
            } else if (mode === 'edit') {
                title.innerText = "Edit Master Cost";

                // Set URL Update
                let updateUrl = "<?php echo e(route('master-costs.update', ':id')); ?>";
                updateUrl = updateUrl.replace(':id', data.id);
                form.action = updateUrl;

                // Tambahkan Method PUT
                methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                // Isi Data Lama
                document.getElementById('input_year').value = data.year;
                document.getElementById('input_status').value = data.status;
                document.getElementById('input_cost').value = data.cost_per_day;
            }

            // 4. Tampilkan Modal
            myModal.show();
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/master_costs/index.blade.php ENDPATH**/ ?>