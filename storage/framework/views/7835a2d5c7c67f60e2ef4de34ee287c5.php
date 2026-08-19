<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body>

    <div class="d-flex" id="wrapper">

        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div id="page-content-wrapper">

            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 py-3">
                <div class="d-flex align-items-center w-100 justify-content-between">

                    <button class="btn btn-outline-secondary btn-sm" id="menu-toggle">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <div class="dropdown">
                        <a href="#"
                            class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark fw-bold"
                            id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-4 me-2 text-secondary"></i>
                            <span><?php echo e(Auth::user()->name ?? 'Guest'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><i
                                        class="bi bi-person me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 py-4">
                <?php if(isset($header)): ?>
                    <div class="mb-4"><?php echo e($header); ?></div>
                <?php endif; ?>

                <?php if(isset($slot) && $slot->isNotEmpty()): ?>
                    <?php echo e($slot); ?>

                <?php else: ?>
                    <?php echo $__env->yieldContent('content'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function() {
            el.classList.toggle("sb-sidenav-toggled");
        };
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/html/bskp-gate-factory-cost/resources/views/layouts/app.blade.php ENDPATH**/ ?>