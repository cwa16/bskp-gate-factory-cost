<?php
    // HELPER: Format Angka Mentah untuk Excel (Tanpa titik ribuan, desimal pakai koma)
    $fmt = function ($num, $isDecimal = false) {
        if ($num == 0) {
            return '-';
        }
        if ($isDecimal) {
            return str_replace('.', ',', (string) round((float) $num, 2));
        }
        return round((float) $num, 0); // Bulatkan tanpa koma untuk Rupiah & Kg
    };

    $fmtPct = function ($num) {
        if ($num == 0) {
            return '-';
        }
        return str_replace('.', ',', (string) round((float) $num, 1)) . '%';
    };

    $isKpiMode = request('kpi_mode') == '1';
    $no = 1;

    // --- TAMBAHKAN BARIS INI UNTUK MENGATASI ERROR ---
    $ttdDay = $month == date('n') && $year == date('Y') ? (int) date('j') : $daysInMonth;
?>

<table border="1" style="font-family: Arial, sans-serif; font-size: 11px;">
    <thead>
        <tr>
            <th style="background-color: #f8fafc; font-weight: bold; text-align: center;">No.</th>
            <th style="background-color: #f8fafc; font-weight: bold; text-align: left; width: 250px;">Cost Category</th>
            <th style="background-color: #f8fafc; font-weight: bold; text-align: center;">Unit</th>
            <th style="background-color: #ccfbf1; font-weight: bold; text-align: right;">TODATE</th>
            <th style="background-color: #e2e8f0; font-weight: bold; text-align: right;">TOTAL</th>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <th style="background-color: #f8fafc; font-weight: bold; text-align: center;">
                    <?php echo e(str_pad($d, 2, '0', STR_PAD_LEFT)); ?></th>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $areaName => $prodLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $rowTotalCost = 0;
                $rowTotalProd = 0;
                $rowTotalBudgetCost = 0;
                $rowTotalBudgetProd = 0;
                $rowTtdCost = 0;
                $rowTtdProd = 0;
                $rowTtdBudgetCost = 0;
                $rowTtdBudgetProd = 0;

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $actCost = $costsPerAreaDay[$areaName][$d] ?? 0;
                    $actProd = $actualProd[$areaName][$d] ?? 0;
                    $bCost = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                    $bProd = $budgetProd[$areaName][$d] ?? 0;

                    $rowTotalCost += $actCost;
                    $rowTotalProd += $actProd;
                    $rowTotalBudgetCost += $bCost;
                    $rowTotalBudgetProd += $bProd;

                    if ($d <= $ttdDay) {
                        $rowTtdCost += $actCost;
                        $rowTtdProd += $actProd;
                        $rowTtdBudgetCost += $bCost;
                        $rowTtdBudgetProd += $bProd;
                    }
                }

                // Kalkulasi Persentase Cost
                if ($isKpiMode) {
                    $rowTotalCostPct = $rowTotalCost > 0 ? ($rowTotalBudgetCost / $rowTotalCost) * 100 : 0;
                    $rowTtdCostPct = $rowTtdCost > 0 ? ($rowTtdBudgetCost / $rowTtdCost) * 100 : 0;
                } else {
                    $rowTotalCostPct = $rowTotalBudgetCost > 0 ? ($rowTotalCost / $rowTotalBudgetCost) * 100 : 0;
                    $rowTtdCostPct = $rowTtdBudgetCost > 0 ? ($rowTtdCost / $rowTtdBudgetCost) * 100 : 0;
                }

                // Kalkulasi Persentase Prod
                $rowTotalProdPct = $rowTotalBudgetProd > 0 ? ($rowTotalProd / $rowTotalBudgetProd) * 100 : 0;
                $rowTtdProdPct = $rowTtdBudgetProd > 0 ? ($rowTtdProd / $rowTtdBudgetProd) * 100 : 0;

                // Kalkulasi CPK
                $rowTotalActualCpk = $rowTotalProd > 0 ? $rowTotalCost / $rowTotalProd : 0;
                $rowTtdActualCpk = $rowTtdProd > 0 ? $rowTtdCost / $rowTtdProd : 0;
                $rowTotalBudgetCpk = $rowTotalBudgetProd > 0 ? $rowTotalBudgetCost / $rowTotalBudgetProd : 0;
                $rowTtdBudgetCpk = $rowTtdBudgetProd > 0 ? $rowTtdBudgetCost / $rowTtdBudgetProd : 0;

                if ($isKpiMode) {
                    $rowTotalCpkPercent = $rowTotalActualCpk > 0 ? ($rowTotalBudgetCpk / $rowTotalActualCpk) * 100 : 0;
                    $rowTtdCpkPercent = $rowTtdActualCpk > 0 ? ($rowTtdBudgetCpk / $rowTtdActualCpk) * 100 : 0;
                } else {
                    $rowTotalCpkPercent = $rowTotalBudgetCpk > 0 ? ($rowTotalActualCpk / $rowTotalBudgetCpk) * 100 : 0;
                    $rowTtdCpkPercent = $rowTtdBudgetCpk > 0 ? ($rowTtdActualCpk / $rowTtdBudgetCpk) * 100 : 0;
                }
            ?>

            <tr>
                <td style="background-color: #fafafa;"><?php echo e($no++); ?></td>
                <td style="background-color: #fafafa;">Budget Cost <?php echo e($areaName); ?></td>
                <td style="background-color: #fafafa; text-align: center;">Rp</td>
                <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($rowTtdBudgetCost)); ?></td>
                <td style="background-color: #e2e8f0; font-weight: bold;"><?php echo e($fmt($rowTotalBudgetCost)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <td style="background-color: #fafafa;"><?php echo e($fmt($budgetCostsPerAreaDay[$areaName][$d] ?? 0)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td></td>
                <td style="font-weight: bold; color: #0f172a;">Actual Cost <?php echo e($areaName); ?></td>
                <td style="text-align: center;">Rp</td>
                <td style="font-weight: bold; color: #0284c7;"><?php echo e($fmt($rowTtdCost)); ?></td>
                <td style="font-weight: bold; color: #0284c7;"><?php echo e($fmt($rowTotalCost)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <td style="color: #0284c7; font-weight: bold;"><?php echo e($fmt($costsPerAreaDay[$areaName][$d] ?? 0)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td style="background-color: #ffffff;"></td>
                <td style="background-color: #ffffff; font-style: italic;">% Pencapaian Cost</td>
                <td style="background-color: #ffffff; text-align: center;">%</td>
                <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmtPct($rowTtdCostPct)); ?></td>
                <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmtPct($rowTotalCostPct)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <?php
                        $cAct = $costsPerAreaDay[$areaName][$d] ?? 0;
                        $cBud = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                        $cPct = $isKpiMode
                            ? ($cAct > 0
                                ? ($cBud / $cAct) * 100
                                : 0)
                            : ($cBud > 0
                                ? ($cAct / $cBud) * 100
                                : 0);
                    ?>
                    <td style="background-color: #ffffff; font-weight: bold;"><?php echo e($fmtPct($cPct)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td style="background-color: #fafafa;"></td>
                <td style="background-color: #fafafa;">Budget Produksi <?php echo e($prodLabel); ?></td>
                <td style="background-color: #fafafa; text-align: center;">Kg</td>
                <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($rowTtdBudgetProd)); ?></td>
                <td style="background-color: #e2e8f0; font-weight: bold;"><?php echo e($fmt($rowTotalBudgetProd)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <td style="background-color: #fafafa;"><?php echo e($fmt($budgetProd[$areaName][$d] ?? 0)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td></td>
                <td style="font-weight: bold; color: #0f172a;">Actual Produksi <?php echo e($prodLabel); ?></td>
                <td style="text-align: center;">Kg</td>
                <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmt($rowTtdProd)); ?></td>
                <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($rowTotalProd)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <td style="font-weight: bold;"><?php echo e($fmt($actualProd[$areaName][$d] ?? 0)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td style="background-color: #ffffff;"></td>
                <td style="background-color: #ffffff; font-style: italic;">% Pencapaian Prod.</td>
                <td style="background-color: #ffffff; text-align: center;">%</td>
                <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmtPct($rowTtdProdPct)); ?></td>
                <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmtPct($rowTotalProdPct)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <?php
                        $pAct = $actualProd[$areaName][$d] ?? 0;
                        $pBud = $budgetProd[$areaName][$d] ?? 0;
                        $pPct = $pBud > 0 ? ($pAct / $pBud) * 100 : 0;
                    ?>
                    <td style="background-color: #ffffff; font-weight: bold;"><?php echo e($fmtPct($pPct)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td style="background-color: #f8fafc;"></td>
                <td style="background-color: #f8fafc;">Budget Cost per kg</td>
                <td style="background-color: #f8fafc; text-align: center;">Rp</td>
                <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($rowTtdBudgetCpk, true)); ?></td>
                <td style="background-color: #e2e8f0; font-weight: bold;"><?php echo e($fmt($rowTotalBudgetCpk, true)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <?php
                        $bCost = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                        $bProd = $budgetProd[$areaName][$d] ?? 0;
                        $bCpk = $bProd > 0 ? $bCost / $bProd : 0;
                    ?>
                    <td style="background-color: #f8fafc;"><?php echo e($fmt($bCpk, true)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td style="background-color: #f0fdf4;"></td>
                <td style="background-color: #f0fdf4; font-weight: bold;">Cost per kg (Actual)</td>
                <td style="background-color: #f0fdf4; text-align: center;">Rp</td>
                <td style="background-color: #ccfbf1; font-weight: bold; color: #16a34a;">
                    <?php echo e($fmt($rowTtdActualCpk, true)); ?></td>
                <td style="background-color: #dcfce7; font-weight: bold; color: #16a34a;">
                    <?php echo e($fmt($rowTotalActualCpk, true)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <?php $cpk = ($actualProd[$areaName][$d] ?? 0) > 0 ? ($costsPerAreaDay[$areaName][$d] ?? 0) / $actualProd[$areaName][$d] : 0; ?>
                    <td style="background-color: #f0fdf4; font-weight: bold; color: #16a34a;"><?php echo e($fmt($cpk, true)); ?>

                    </td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td style="background-color: #ffffff;"></td>
                <td style="background-color: #ffffff; font-weight: bold; font-style: italic;">% Pencapaian Cost per Kg
                </td>
                <td style="background-color: #ffffff; text-align: center;">%</td>
                <td style="background-color: #f0fdfa; font-weight: bold;"><?php echo e($fmtPct($rowTtdCpkPercent)); ?></td>
                <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmtPct($rowTotalCpkPercent)); ?></td>
                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                    <?php
                        $cpk =
                            ($actualProd[$areaName][$d] ?? 0) > 0
                                ? ($costsPerAreaDay[$areaName][$d] ?? 0) / $actualProd[$areaName][$d]
                                : 0;
                        $bCost = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                        $bProd = $budgetProd[$areaName][$d] ?? 0;
                        $bCpk = $bProd > 0 ? $bCost / $bProd : 0;
                        $pct = $isKpiMode
                            ? ($cpk > 0
                                ? ($bCpk / $cpk) * 100
                                : 0)
                            : ($bCpk > 0 && $cpk > 0
                                ? ($cpk / $bCpk) * 100
                                : 0);
                    ?>
                    <td style="background-color: #ffffff; font-weight: bold;"><?php echo e($fmtPct($pct)); ?></td>
                <?php endfor; ?>
            </tr>

            <tr>
                <td colspan="<?php echo e(5 + $daysInMonth); ?>" style="height: 15px;"></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php
            $gTotalCost = 0;
            $gTotalBudgetCost = 0;
            $gTotalRss = 0;
            $gTotalBudgetRss = 0;
            $gTtdCost = 0;
            $gTtdBudgetCost = 0;
            $gTtdRss = 0;
            $gTtdBudgetRss = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dailyTotalCost = 0;
                $dailyTotalBudgetCost = 0;
                foreach (array_keys($areas) as $a) {
                    $dailyTotalCost += $costsPerAreaDay[$a][$d] ?? 0;
                    $dailyTotalBudgetCost += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                }
                $dailyRss = $actualTotalRss[$d] ?? 0;
                $dailyBudgetRss = $budgetTotalRss[$d] ?? 0;

                $gTotalCost += $dailyTotalCost;
                $gTotalBudgetCost += $dailyTotalBudgetCost;
                $gTotalRss += $dailyRss;
                $gTotalBudgetRss += $dailyBudgetRss;

                if ($d <= $ttdDay) {
                    $gTtdCost += $dailyTotalCost;
                    $gTtdBudgetCost += $dailyTotalBudgetCost;
                    $gTtdRss += $dailyRss;
                    $gTtdBudgetRss += $dailyBudgetRss;
                }
            }

            // Pct Cost
            $gTotalCostPct = $isKpiMode
                ? ($gTotalCost > 0
                    ? ($gTotalBudgetCost / $gTotalCost) * 100
                    : 0)
                : ($gTotalBudgetCost > 0
                    ? ($gTotalCost / $gTotalBudgetCost) * 100
                    : 0);
            $gTtdCostPct = $isKpiMode
                ? ($gTtdCost > 0
                    ? ($gTtdBudgetCost / $gTtdCost) * 100
                    : 0)
                : ($gTtdBudgetCost > 0
                    ? ($gTtdCost / $gTtdBudgetCost) * 100
                    : 0);

            // Pct Prod
            $gTotalProdPct = $gTotalBudgetRss > 0 ? ($gTotalRss / $gTotalBudgetRss) * 100 : 0;
            $gTtdProdPct = $gTtdBudgetRss > 0 ? ($gTtdRss / $gTtdBudgetRss) * 100 : 0;

            // CPK
            $gTotalActualCpk = $gTotalRss > 0 ? $gTotalCost / $gTotalRss : 0;
            $gTtdActualCpk = $gTtdRss > 0 ? $gTtdCost / $gTtdRss : 0;
            $gTotalBudgetCpk = $gTotalBudgetRss > 0 ? $gTotalBudgetCost / $gTotalBudgetRss : 0;
            $gTtdBudgetCpk = $gTtdBudgetRss > 0 ? $gTtdBudgetCost / $gTtdBudgetRss : 0;

            // Pct CPK
            $gTotalCpkPercent = $isKpiMode
                ? ($gTotalActualCpk > 0
                    ? ($gTotalBudgetCpk / $gTotalActualCpk) * 100
                    : 0)
                : ($gTotalBudgetCpk > 0
                    ? ($gTotalActualCpk / $gTotalBudgetCpk) * 100
                    : 0);
            $gTtdCpkPercent = $isKpiMode
                ? ($gTtdActualCpk > 0
                    ? ($gTtdBudgetCpk / $gTtdActualCpk) * 100
                    : 0)
                : ($gTtdBudgetCpk > 0
                    ? ($gTtdActualCpk / $gTtdBudgetCpk) * 100
                    : 0);
        ?>

        <tr>
            <td style="background-color: #e2e8f0;"></td>
            <td style="background-color: #e2e8f0; font-weight: bold;">Total Budget Cost</td>
            <td style="background-color: #e2e8f0; text-align: center;">Rp</td>
            <td style="background-color: #cbd5e1; font-weight: bold;"><?php echo e($fmt($gTtdBudgetCost)); ?></td>
            <td style="background-color: #94a3b8; font-weight: bold;"><?php echo e($fmt($gTotalBudgetCost)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $tBudgetCostDay = 0;
                    foreach (array_keys($areas) as $a) {
                        $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                    }
                ?>
                <td style="background-color: #e2e8f0;"><?php echo e($fmt($tBudgetCostDay)); ?></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="background-color: #ffd8d8;"></td>
            <td style="background-color: #ffd8d8; font-weight: bold; color: #000;">Total Actual Cost</td>
            <td style="background-color: #ffd8d8; text-align: center; color: #000;">Rp</td>
            <td style="background-color: #ffd8d8; font-weight: bold; color: #000;"><?php echo e($fmt($gTtdCost)); ?></td>
            <td style="background-color: #ffd8d8; font-weight: bold; color: #000;"><?php echo e($fmt($gTotalCost)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $tCostDay = 0;
                    foreach (array_keys($areas) as $a) {
                        $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                    }
                ?>
                <td style="background-color: #ffd8d8; font-weight: bold; color: #000;"><?php echo e($fmt($tCostDay)); ?></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="background-color: #f8fafc;"></td>
            <td style="background-color: #f8fafc; font-style: italic;">% Total Pencapaian Cost</td>
            <td style="background-color: #f8fafc; text-align: center;">%</td>
            <td style="background-color: #e2e8f0; font-weight: bold;"><?php echo e($fmtPct($gTtdCostPct)); ?></td>
            <td style="background-color: #cbd5e1; font-weight: bold;"><?php echo e($fmtPct($gTotalCostPct)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $tCostDay = 0;
                    $tBudgetCostDay = 0;
                    foreach (array_keys($areas) as $a) {
                        $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                        $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                    }
                    $cPct = $isKpiMode
                        ? ($tCostDay > 0
                            ? ($tBudgetCostDay / $tCostDay) * 100
                            : 0)
                        : ($tBudgetCostDay > 0
                            ? ($tCostDay / $tBudgetCostDay) * 100
                            : 0);
                ?>
                <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmtPct($cPct)); ?></td>
            <?php endfor; ?>
        </tr>

        <tr>
            <td style="background-color: #fafafa;"></td>
            <td style="background-color: #fafafa; font-weight: bold;">Total Budget Produksi (RSS)</td>
            <td style="background-color: #fafafa; text-align: center;">Kg</td>
            <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($gTtdBudgetRss)); ?></td>
            <td style="background-color: #e2e8f0; font-weight: bold;"><?php echo e($fmt($gTotalBudgetRss)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <td style="background-color: #fafafa;"><?php echo e($fmt($budgetTotalRss[$d] ?? 0)); ?></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="background-color: #ffffff;"></td>
            <td style="font-weight: bold;">Total Actual Produksi (RSS)</td>
            <td style="text-align: center;">Kg</td>
            <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmt($gTtdRss)); ?></td>
            <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($gTotalRss)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <td style="font-weight: bold;"><?php echo e($fmt($actualTotalRss[$d] ?? 0)); ?></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="background-color: #ffffff;"></td>
            <td style="font-style: italic;">% Total Pencapaian Prod.</td>
            <td style="text-align: center;">%</td>
            <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmtPct($gTtdProdPct)); ?></td>
            <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmtPct($gTotalProdPct)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $pAct = $actualTotalRss[$d] ?? 0;
                    $pBud = $budgetTotalRss[$d] ?? 0;
                    $pPct = $pBud > 0 ? ($pAct / $pBud) * 100 : 0;
                ?>
                <td style="font-weight: bold;"><?php echo e($fmtPct($pPct)); ?></td>
            <?php endfor; ?>
        </tr>

        <tr>
            <td style="background-color: #f8fafc;"></td>
            <td style="background-color: #f8fafc; font-weight: bold;">Total Budget Cost per kg</td>
            <td style="background-color: #f8fafc; text-align: center;">Rp</td>
            <td style="background-color: #f1f5f9; font-weight: bold;"><?php echo e($fmt($gTtdBudgetCpk, true)); ?></td>
            <td style="background-color: #e2e8f0; font-weight: bold;"><?php echo e($fmt($gTotalBudgetCpk, true)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $tBudgetCostDay = 0;
                    foreach (array_keys($areas) as $a) {
                        $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                    }
                    $fBudCpk = ($budgetTotalRss[$d] ?? 0) > 0 ? $tBudgetCostDay / $budgetTotalRss[$d] : 0;
                ?>
                <td style="background-color: #f8fafc;"><?php echo e($fmt($fBudCpk, true)); ?></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="background-color: #f0fdf4;"></td>
            <td style="background-color: #f0fdf4; font-weight: bold;">Total Cost per kg (Actual)</td>
            <td style="background-color: #f0fdf4; text-align: center;">Rp</td>
            <td style="background-color: #ccfbf1; font-weight: bold; color: #16a34a;"><?php echo e($fmt($gTtdActualCpk, true)); ?>

            </td>
            <td style="background-color: #dcfce7; font-weight: bold; color: #16a34a;">
                <?php echo e($fmt($gTotalActualCpk, true)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $tCostDay = 0;
                    foreach (array_keys($areas) as $a) {
                        $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                    }
                    $fCpk = ($actualTotalRss[$d] ?? 0) > 0 ? $tCostDay / $actualTotalRss[$d] : 0;
                ?>
                <td style="background-color: #f0fdf4; font-weight: bold; color: #16a34a;"><?php echo e($fmt($fCpk, true)); ?></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="background-color: #ffffff;"></td>
            <td style="font-weight: bold; font-style: italic;">% Total Pencapaian Cost per Kg</td>
            <td style="text-align: center;">%</td>
            <td style="background-color: #f0fdfa; font-weight: bold;"><?php echo e($fmtPct($gTtdCpkPercent)); ?></td>
            <td style="background-color: #f8fafc; font-weight: bold;"><?php echo e($fmtPct($gTotalCpkPercent)); ?></td>
            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                    $tCostDay = 0;
                    $tBudgetCostDay = 0;
                    foreach (array_keys($areas) as $a) {
                        $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                        $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                    }
                    $fCpk = ($actualTotalRss[$d] ?? 0) > 0 ? $tCostDay / $actualTotalRss[$d] : 0;
                    $fBudCpk = ($budgetTotalRss[$d] ?? 0) > 0 ? $tBudgetCostDay / $budgetTotalRss[$d] : 0;
                    $pct = $isKpiMode
                        ? ($fCpk > 0
                            ? ($fBudCpk / $fCpk) * 100
                            : 0)
                        : ($fBudCpk > 0 && $fCpk > 0
                            ? ($fCpk / $fBudCpk) * 100
                            : 0);
                ?>
                <td style="font-weight: bold;"><?php echo e($fmtPct($pct)); ?></td>
            <?php endfor; ?>
        </tr>
    </tbody>
</table>
<?php /**PATH D:\Anshari\App\bskp-gate-factory-cost\resources\views/cost-per-kg/export.blade.php ENDPATH**/ ?>