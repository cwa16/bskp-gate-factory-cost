<style>
    /* ================= SIDEBAR INDUSTRIAL ================= */

    .sidebar {
        width: 240px;
        min-height: 100vh;
        background: #f8f9fa;
        font-size: 13px;
    }

    .sidebar-header {
        border-bottom: 1px solid #dee2e6;
    }

    .sidebar-brand {
        font-weight: 700;
        letter-spacing: 1px;
        color: #212529;
    }

    .btn-toggle {
        border: none;
        color: #6c757d;
    }

    .btn-toggle:hover {
        color: #212529;
    }

    /* MENU */
    .sidebar-menu .nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #495057;
        padding: 8px 10px;
        border-radius: 4px;
        font-weight: 500;
    }

    .sidebar-menu .nav-link i {
        font-size: 16px;
        width: 18px;
        text-align: center;
    }

    /* Hover */
    .sidebar-menu .nav-link:hover {
        background: #e9ecef;
        color: #212529;
    }

    /* Active */
    .sidebar-menu .nav-link.active {
        background: #212529;
        color: #fff;
    }

    .sidebar-menu .nav-link.active i {
        color: #fff;
    }

    /* Divider */
    .nav-divider {
        margin: 10px 8px 6px;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6c757d;
    }

    /* COLLAPSED */
    .sidebar.collapsed {
        width: 64px;
    }

    .sidebar.collapsed .sidebar-brand,
    .sidebar.collapsed .nav-link span,
    .sidebar.collapsed .nav-divider {
        display: none;
    }

    .sidebar.collapsed .nav-link {
        justify-content: center;
    }
</style>

<aside id="sidebar" class="sidebar border-end">

    
    <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-2">
        <span class="sidebar-brand">Factory Cost</span>
        <button class="btn btn-sm btn-toggle" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
    </div>

    
    <ul class="nav flex-column sidebar-menu px-2 py-2">

        <li class="nav-item">
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-divider">Master Cost</li>


        <li class="nav-item">
            <a href="<?php echo e(route('master-costs.index')); ?>"
                class="nav-link <?php echo e(request()->is('master-costs*') ? 'active' : ''); ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Master Cost</span>
            </a>
        </li>

        <li class="nav-divider">SUB JOB</li>


        <li class="nav-item">
            <a href="<?php echo e(route('subjob.index')); ?>" class="nav-link <?php echo e(request()->is('subjob') ? 'active' : ''); ?>">
                <i class="bi bi-diagram-3"></i>
                <span>OT Management</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo e(route('subjob.summary')); ?>" class="nav-link <?php echo e(request()->is('subjob.summary') ? 'active' : ''); ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Wages Summary</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo e(route('borongan.index')); ?>" class="nav-link <?php echo e(request()->is('borongan') ? 'active' : ''); ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Input Borongan</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo e(route('deduction.index')); ?>"
                class="nav-link <?php echo e(request()->is('deduction.*') ? 'active' : ''); ?>">
                <i class="bi bi-bar-chart"></i>
                <span>Input Potongan</span>
            </a>
        </li>

        

        <li class="nav-divider">BUDGET</li>
        <li class="nav-item">
            <a href="<?php echo e(route('subjob.plan.index')); ?>"
                class="nav-link <?php echo e(request()->is('subjob/plan') ? 'active' : ''); ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Input</span>
            </a>
        </li>


        <li class="nav-item">
            <a href="<?php echo e(route('subjob-budget.index')); ?>"
                class="nav-link <?php echo e(request()->is('subjob-budget') ? 'active' : ''); ?>">
                <i class="bi bi-calculator"></i>
                <span>Summary Budget</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo e(route('subjob-budget.compare')); ?>"
                class="nav-link <?php echo e(request()->is('subjob-budget-vs-actual') ? 'active' : ''); ?>">
                <i class="bi bi-clipboard-data"></i>
                <span>Budget vs Actual</span>
            </a>
        </li>

        <li class="nav-divider">Crop Per KG</li>

        <li class="nav-item">
            <a href="<?php echo e(route('cost-per-kg.index')); ?>"
                class="nav-link <?php echo e(request()->is('cost-per-kg.index') ? 'active' : ''); ?>">
                <i class="bi bi-clipboard-data"></i>
                <span>Cost Per KG Report</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo e(route('budget-cpk.index')); ?>"
                class="nav-link <?php echo e(request()->is('budget-cpk.index') ? 'active' : ''); ?>">
                <i class="bi bi-clipboard-data"></i>
                <span>Input Budget Cost Per KG</span>
            </a>
        </li>

    </ul>

</aside>
<?php /**PATH D:\Anshari\App\bskp-gate-factory-cost\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>