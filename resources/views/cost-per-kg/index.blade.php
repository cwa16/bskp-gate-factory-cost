@extends('layouts.app')

@section('content')
    <style>
        .enterprise-container {
            font-family: 'Inter', -apple-system, sans-serif;
        }

        .table-freeze-container {
            max-width: 100%;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .table-cost {
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .table-cost th,
        .table-cost td {
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 14px;
            vertical-align: middle;
        }

        /* STICKY COLUMNS */
        .sticky-col-1 {
            position: sticky;
            left: 0;
            z-index: 10;
            background: #ffffff;
            width: 40px;
            text-align: center;
            color: #64748b;
        }

        .sticky-col-2 {
            position: sticky;
            left: 40px;
            z-index: 10;
            background: #ffffff;
            min-width: 250px;
            font-weight: 500;
            color: #334155;
        }

        .sticky-col-3 {
            position: sticky;
            left: 290px;
            z-index: 10;
            background: #ffffff;
            width: 60px;
            text-align: center;
            border-right: 1px solid #e2e8f0 !important;
        }

        .sticky-col-4 {
            position: sticky;
            left: 350px;
            z-index: 10;
            background: #f0fdfa;
            min-width: 100px;
            text-align: right;
            border-right: 1px solid #cbd5e1 !important;
            font-weight: 700;
        }

        .sticky-col-5 {
            position: sticky;
            left: 450px;
            z-index: 10;
            background: #f8fafc;
            min-width: 100px;
            text-align: right;
            box-shadow: inset -6px 0 6px -6px rgba(0, 0, 0, 0.15);
            border-right: none !important;
            font-weight: 700;
        }

        /* HEADERS */
        .table-cost thead th {
            position: sticky;
            top: 0;
            background-color: #f8fafc;
            z-index: 20;
            font-weight: 600;
            text-align: center;
            color: #475569;
            text-transform: uppercase;
            font-size: 11px;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-cost thead th.sticky-col-1,
        .table-cost thead th.sticky-col-2,
        .table-cost thead th.sticky-col-3 {
            z-index: 30;
            background-color: #f8fafc;
        }

        .table-cost thead th.sticky-col-4 {
            z-index: 30;
            background-color: #ccfbf1;
            color: #0f172a;
        }

        .table-cost thead th.sticky-col-5 {
            z-index: 30;
            background-color: #e2e8f0;
            color: #0f172a;
        }

        /* ROWS AREA */
        .row-cost-budget td,
        .row-cost-budget .sticky-col-1,
        .row-cost-budget .sticky-col-2,
        .row-cost-budget .sticky-col-3 {
            background-color: #fafafa !important;
            color: #475569;
            font-weight: 500;
        }

        .row-cost-budget .sticky-col-4 {
            background-color: #f1f5f9 !important;
        }

        .row-cost-budget .sticky-col-5 {
            background-color: #e2e8f0 !important;
        }

        .row-cost-actual td {
            font-weight: 600;
            color: #0f172a;
        }

        .row-cost-actual .val-cell {
            color: #0284c7;
        }

        .row-budget-production td,
        .row-budget-production .sticky-col-1,
        .row-budget-production .sticky-col-2,
        .row-budget-production .sticky-col-3 {
            background-color: #fafafa !important;
            color: #94a3b8;
        }

        .row-budget-production .sticky-col-4 {
            background-color: #f1f5f9 !important;
            color: #64748b;
        }

        .row-budget-production .sticky-col-5 {
            background-color: #e2e8f0 !important;
            color: #64748b;
        }

        .row-production td,
        .row-production .sticky-col-1,
        .row-production .sticky-col-2,
        .row-production .sticky-col-3 {
            background-color: #ffffff !important;
            color: #64748b;
            font-weight: 600;
        }

        .row-production .sticky-col-4 {
            background-color: #f8fafc !important;
        }

        .row-production .sticky-col-5 {
            background-color: #f1f5f9 !important;
        }

        .row-budget td,
        .row-budget .sticky-col-1,
        .row-budget .sticky-col-2,
        .row-budget .sticky-col-3 {
            background-color: #f8fafc !important;
            color: #64748b;
        }

        .row-budget .sticky-col-4 {
            background-color: #f1f5f9 !important;
        }

        .row-budget .sticky-col-5 {
            background-color: #e2e8f0 !important;
        }

        .row-cost-kg td,
        .row-cost-kg .sticky-col-1,
        .row-cost-kg .sticky-col-2,
        .row-cost-kg .sticky-col-3 {
            background-color: #f0fdf4 !important;
            font-weight: 600;
        }

        .row-cost-kg .sticky-col-4 {
            background-color: #ccfbf1 !important;
        }

        .row-cost-kg .sticky-col-5 {
            background-color: #dcfce7 !important;
        }

        .row-cost-kg .val-cell {
            color: #16a34a;
        }

        /* PERCENTAGE ROWS */
        .row-percent td,
        .row-percent .sticky-col-1,
        .row-percent .sticky-col-2,
        .row-percent .sticky-col-3 {
            background-color: #ffffff !important;
        }

        .row-percent .sticky-col-4 {
            background-color: #f0fdfa !important;
            font-size: 13px;
        }

        .row-percent .sticky-col-5 {
            background-color: #f8fafc !important;
            font-size: 13px;
        }

        .row-sub-percent td,
        .row-sub-percent .sticky-col-1,
        .row-sub-percent .sticky-col-2,
        .row-sub-percent .sticky-col-3 {
            background-color: #ffffff !important;
            font-style: italic;
        }

        .row-sub-percent .sticky-col-4 {
            background-color: #f8fafc !important;
            font-size: 12px;
        }

        .row-sub-percent .sticky-col-5 {
            background-color: #f1f5f9 !important;
            font-size: 12px;
        }

        /* TOTALS FOOTER */
        .row-total-budget td {
            background-color: #e2e8f0 !important;
            color: #0f172a;
            font-weight: 600;
            border-top: none;
        }

        .row-total-budget .sticky-col-4 {
            background-color: #cbd5e1 !important;
            color: #0f172a;
            font-size: 14px;
        }

        .row-total-budget .sticky-col-5 {
            background-color: #94a3b8 !important;
            color: #0f172a;
            font-size: 14px;
        }

        .row-total-cost td {
            background-color: #ffd8d8 !important;
            color: #000000;
            font-weight: 600;
            border-top: none;
        }

        .row-total-cost .sticky-col-4 {
            background-color: #ffd8d8 !important;
            color: #000000;
            font-size: 14px;
        }

        .row-total-cost .sticky-col-5 {
            background-color: #ffd8d8 !important;
            color: #000000;
            font-size: 14px;
        }

        .row-footer-percent td {
            background-color: #f8fafc !important;
            color: #0f172a;
            font-style: italic;
            border-top: none;
        }

        .row-footer-percent .sticky-col-4 {
            background-color: #e2e8f0 !important;
            color: #0f172a;
            font-size: 13px;
        }

        .row-footer-percent .sticky-col-5 {
            background-color: #cbd5e1 !important;
            color: #0f172a;
            font-size: 13px;
        }

        .enterprise-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
        }
    </style>

    <div class="container-fluid py-4 enterprise-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0">Laporan Cost per Kg</h4>
                <p class="text-secondary small m-0 mt-1">Evaluasi efisiensi biaya per area operasional pabrik</p>
            </div>

            <form action="{{ route('cost-per-kg.index') }}" method="GET"
                class="d-flex gap-2 enterprise-toolbar shadow-sm align-items-center">
                <!-- TOGGLE KPI MODE -->
                <div class="form-check form-switch ms-2 me-2 mb-0 d-flex align-items-center">
                    <input class="form-check-input mt-0 me-2" type="checkbox" role="switch" id="kpiModeSwitch"
                        name="kpi_mode" value="1" {{ request('kpi_mode') == '1' ? 'checked' : '' }}
                        onchange="this.form.submit()" style="cursor: pointer; transform: scale(1.2);">
                    <label class="form-check-label fw-bold" for="kpiModeSwitch"
                        style="color: #0284c7; cursor: pointer; padding-top:2px;">KPI Mode</label>
                </div>
                <div class="vr mx-1"></div>

                <select name="type" class="form-select form-select-sm fw-bold border-0 bg-light text-primary">
                    <option value="gabungan" {{ request('type') == 'gabungan' ? 'selected' : '' }}>Regular + Contract FL
                    </option>
                    <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Hanya Regular</option>
                    <option value="contract_fl" {{ request('type') == 'contract_fl' ? 'selected' : '' }}>Hanya Contract FL
                    </option>
                </select>
                <select name="month" class="form-select form-select-sm fw-medium border-0 bg-light">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm fw-medium border-0 bg-light">
                    @php $crYear = date('Y'); @endphp
                    @for ($y = $crYear - 1; $y <= $crYear + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-medium">Filter</button>
                <a href="{{ route('cost-per-kg.export', ['month' => $month, 'year' => $year, 'type' => $type, 'kpi_mode' => request('kpi_mode')]) }}"
                    class="btn btn-sm btn-success px-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </form>

        </div>

        <div class="table-freeze-container mt-4">
            <table class="table table-cost mb-0">
                <thead>
                    <tr>
                        <th class="sticky-col-1">No.</th>
                        <th class="sticky-col-2 text-start">Cost Category</th>
                        <th class="sticky-col-3">Unit</th>
                        <th class="sticky-col-4 text-end pe-3">TODATE</th>
                        <th class="sticky-col-5 text-end pe-3">TOTAL</th>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            <th class="{{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                        $isKpiMode = request('kpi_mode') == '1'; // Deteksi KPI Mode
                    @endphp

                    @foreach ($areas as $areaName => $prodLabel)
                        @php
                            // ==========================================================
                            // RUMUS PERHITUNGAN
                            // ==========================================================
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

                            // 1. Persentase COST
                            if ($isKpiMode) {
                                // KPI: Budget / Actual (>= 100 Hijau)
                                $rowTotalCostPct = $rowTotalCost > 0 ? ($rowTotalBudgetCost / $rowTotalCost) * 100 : 0;
                                $rowTtdCostPct = $rowTtdCost > 0 ? ($rowTtdBudgetCost / $rowTtdCost) * 100 : 0;
                                $pctClassTotalCost =
                                    $rowTotalCostPct > 0
                                        ? ($rowTotalCostPct >= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                                $pctClassTtdCost =
                                    $rowTtdCostPct > 0
                                        ? ($rowTtdCostPct >= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                            } else {
                                // Reguler: Actual / Budget (<= 100 Hijau)
                                $rowTotalCostPct =
                                    $rowTotalBudgetCost > 0 ? ($rowTotalCost / $rowTotalBudgetCost) * 100 : 0;
                                $rowTtdCostPct = $rowTtdBudgetCost > 0 ? ($rowTtdCost / $rowTtdBudgetCost) * 100 : 0;
                                $pctClassTotalCost =
                                    $rowTotalCostPct > 0
                                        ? ($rowTotalCostPct <= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                                $pctClassTtdCost =
                                    $rowTtdCostPct > 0
                                        ? ($rowTtdCostPct <= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                            }

                            // 2. Persentase PRODUKSI (Tetap Actual / Budget -> >= 100 Hijau)
                            $rowTotalProdPct =
                                $rowTotalBudgetProd > 0 ? ($rowTotalProd / $rowTotalBudgetProd) * 100 : 0;
                            $rowTtdProdPct = $rowTtdBudgetProd > 0 ? ($rowTtdProd / $rowTtdBudgetProd) * 100 : 0;
                            $pctClassTotalProd =
                                $rowTotalProdPct > 0
                                    ? ($rowTotalProdPct >= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                            $pctClassTtdProd =
                                $rowTtdProdPct > 0
                                    ? ($rowTtdProdPct >= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';

                            // 3. Persentase CPK
                            $rowTotalActualCpk = $rowTotalProd > 0 ? $rowTotalCost / $rowTotalProd : 0;
                            $rowTtdActualCpk = $rowTtdProd > 0 ? $rowTtdCost / $rowTtdProd : 0;
                            $rowTotalBudgetCpk =
                                $rowTotalBudgetProd > 0 ? $rowTotalBudgetCost / $rowTotalBudgetProd : 0;
                            $rowTtdBudgetCpk = $rowTtdBudgetProd > 0 ? $rowTtdBudgetCost / $rowTtdBudgetProd : 0;

                            if ($isKpiMode) {
                                // KPI: Budget / Actual (>= 100 Hijau)
                                $rowTotalCpkPercent =
                                    $rowTotalActualCpk > 0 ? ($rowTotalBudgetCpk / $rowTotalActualCpk) * 100 : 0;
                                $rowTtdCpkPercent =
                                    $rowTtdActualCpk > 0 ? ($rowTtdBudgetCpk / $rowTtdActualCpk) * 100 : 0;
                                $pctClassTotalCpk =
                                    $rowTotalCpkPercent > 0
                                        ? ($rowTotalCpkPercent >= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                                $pctClassTtdCpk =
                                    $rowTtdCpkPercent > 0
                                        ? ($rowTtdCpkPercent >= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                            } else {
                                // Reguler: Actual / Budget (<= 100 Hijau)
                                $rowTotalCpkPercent =
                                    $rowTotalBudgetCpk > 0 ? ($rowTotalActualCpk / $rowTotalBudgetCpk) * 100 : 0;
                                $rowTtdCpkPercent =
                                    $rowTtdBudgetCpk > 0 ? ($rowTtdActualCpk / $rowTtdBudgetCpk) * 100 : 0;
                                $pctClassTotalCpk =
                                    $rowTotalCpkPercent > 0
                                        ? ($rowTotalCpkPercent <= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                                $pctClassTtdCpk =
                                    $rowTtdCpkPercent > 0
                                        ? ($rowTtdCpkPercent <= 100
                                            ? 'text-success'
                                            : 'text-danger')
                                        : 'text-muted';
                            }
                        @endphp

                        <!-- 1. BUDGET COST -->
                        <tr class="row-cost-budget">
                            <td class="sticky-col-1">{{ $no++ }}</td>
                            <td class="sticky-col-2"><i class="bi bi-wallet2 me-1 opacity-50"></i>Budget Cost
                                {{ $areaName }}</td>
                            <td class="sticky-col-3 text-secondary">Rp</td>
                            <td class="sticky-col-4 pe-3">
                                {{ $rowTtdBudgetCost > 0 ? number_format($rowTtdBudgetCost, 0, ',', '.') : '-' }}</td>
                            <td class="sticky-col-5 pe-3">
                                {{ $rowTotalBudgetCost > 0 ? number_format($rowTotalBudgetCost, 0, ',', '.') : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <td class="text-end val-cell {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ ($budgetCostsPerAreaDay[$areaName][$d] ?? 0) > 0 ? number_format($budgetCostsPerAreaDay[$areaName][$d], 0, ',', '.') : '-' }}
                                </td>
                            @endfor
                        </tr>

                        <!-- 2. ACTUAL COST -->
                        <tr class="row-cost-actual">
                            <td class="sticky-col-1 border-top-0"></td>
                            <td class="sticky-col-2 ps-4 border-top-0">Actual Cost {{ $areaName }}</td>
                            <td class="sticky-col-3 border-top-0">Rp</td>
                            <td class="sticky-col-4 text-primary pe-3">
                                {{ $rowTtdCost > 0 ? number_format($rowTtdCost, 0, ',', '.') : '-' }}</td>
                            <td class="sticky-col-5 text-primary pe-3">
                                {{ $rowTotalCost > 0 ? number_format($rowTotalCost, 0, ',', '.') : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <td class="text-end val-cell {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ ($costsPerAreaDay[$areaName][$d] ?? 0) > 0 ? number_format($costsPerAreaDay[$areaName][$d], 0, ',', '.') : '-' }}
                                </td>
                            @endfor
                        </tr>

                        <!-- 3. PENCAPAIAN COST (%) -->
                        <tr class="row-sub-percent">
                            <td class="sticky-col-1 border-bottom-0"></td>
                            <td class="sticky-col-2 ps-4 border-bottom-0"><i
                                    class="bi bi-arrow-return-right me-1 opacity-50"></i>% Pencapaian Cost</td>
                            <td class="sticky-col-3 border-bottom-0">%</td>
                            <td class="sticky-col-4 pe-3 fw-bold {{ $pctClassTtdCost }}">
                                {{ $rowTtdCostPct > 0 ? number_format($rowTtdCostPct, 1, ',', '.') . '%' : '-' }}</td>
                            <td class="sticky-col-5 pe-3 fw-bold {{ $pctClassTotalCost }}">
                                {{ $rowTotalCostPct > 0 ? number_format($rowTotalCostPct, 1, ',', '.') . '%' : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $cAct = $costsPerAreaDay[$areaName][$d] ?? 0;
                                    $cBud = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                                    if ($isKpiMode) {
                                        $cPct = $cAct > 0 ? ($cBud / $cAct) * 100 : 0;
                                        $cClass =
                                            $cPct > 0 ? ($cPct >= 100 ? 'text-success' : 'text-danger') : 'text-muted';
                                    } else {
                                        $cPct = $cBud > 0 ? ($cAct / $cBud) * 100 : 0;
                                        $cClass =
                                            $cPct > 0 ? ($cPct <= 100 ? 'text-success' : 'text-danger') : 'text-muted';
                                    }
                                @endphp
                                <td
                                    class="text-end border-bottom-0 fw-bold {{ $cClass }} {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ $cPct > 0 ? number_format($cPct, 1, ',', '.') . '%' : '-' }}</td>
                            @endfor
                        </tr>

                        <!-- 4. BUDGET PRODUKSI -->
                        <tr class="row-budget-production">
                            <td class="sticky-col-1 border-top-0"></td>
                            <td class="sticky-col-2 ps-4 border-top-0"><i class="bi bi-bullseye me-1 opacity-50"></i>Budget
                                Produksi {{ $prodLabel }}</td>
                            <td class="sticky-col-3 border-top-0">Kg</td>
                            <td class="sticky-col-4 pe-3">
                                {{ $rowTtdBudgetProd > 0 ? number_format($rowTtdBudgetProd, 0, ',', '.') : '-' }}</td>
                            <td class="sticky-col-5 pe-3">
                                {{ $rowTotalBudgetProd > 0 ? number_format($rowTotalBudgetProd, 0, ',', '.') : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <td
                                    class="text-end border-top-0 {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ ($budgetProd[$areaName][$d] ?? 0) > 0 ? number_format($budgetProd[$areaName][$d], 0, ',', '.') : '-' }}
                                </td>
                            @endfor
                        </tr>

                        <!-- 5. ACTUAL PRODUKSI -->
                        <tr class="row-production">
                            <td class="sticky-col-1 border-top-0"></td>
                            <td class="sticky-col-2 ps-4 border-top-0"><i class="bi bi-box-seam me-1 opacity-50"></i>Actual
                                Produksi {{ $prodLabel }}</td>
                            <td class="sticky-col-3 border-top-0">Kg</td>
                            <td class="sticky-col-4 pe-3">
                                {{ $rowTtdProd > 0 ? number_format($rowTtdProd, 0, ',', '.') : '-' }}</td>
                            <td class="sticky-col-5 pe-3">
                                {{ $rowTotalProd > 0 ? number_format($rowTotalProd, 0, ',', '.') : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <td
                                    class="text-end border-top-0 {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ ($actualProd[$areaName][$d] ?? 0) > 0 ? number_format($actualProd[$areaName][$d], 0, ',', '.') : '-' }}
                                </td>
                            @endfor
                        </tr>

                        <!-- 6. PENCAPAIAN PRODUKSI (%) -->
                        <tr class="row-sub-percent">
                            <td class="sticky-col-1 border-bottom-0"></td>
                            <td class="sticky-col-2 ps-4 border-bottom-0"><i
                                    class="bi bi-arrow-return-right me-1 opacity-50"></i>% Pencapaian Prod.</td>
                            <td class="sticky-col-3 border-bottom-0">%</td>
                            <td class="sticky-col-4 pe-3 fw-bold {{ $pctClassTtdProd }}">
                                {{ $rowTtdProdPct > 0 ? number_format($rowTtdProdPct, 1, ',', '.') . '%' : '-' }}</td>
                            <td class="sticky-col-5 pe-3 fw-bold {{ $pctClassTotalProd }}">
                                {{ $rowTotalProdPct > 0 ? number_format($rowTotalProdPct, 1, ',', '.') . '%' : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $pAct = $actualProd[$areaName][$d] ?? 0;
                                    $pBud = $budgetProd[$areaName][$d] ?? 0;
                                    $pPct = $pBud > 0 ? ($pAct / $pBud) * 100 : 0;
                                    $pClass =
                                        $pPct > 0 ? ($pPct >= 100 ? 'text-success' : 'text-danger') : 'text-muted';
                                @endphp
                                <td
                                    class="text-end border-bottom-0 fw-bold {{ $pClass }} {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ $pPct > 0 ? number_format($pPct, 1, ',', '.') . '%' : '-' }}</td>
                            @endfor
                        </tr>

                        <!-- 7. BUDGET COST PER KG -->
                        <tr class="row-budget">
                            <td class="sticky-col-1 border-bottom-0"></td>
                            <td class="sticky-col-2 ps-4 border-bottom-0 fw-medium">Budget Cost per kg</td>
                            <td class="sticky-col-3 border-bottom-0">Rp</td>
                            <td class="sticky-col-4 pe-3">
                                {{ $rowTtdBudgetCpk > 0 ? number_format($rowTtdBudgetCpk, 2, ',', '.') : '-' }}</td>
                            <td class="sticky-col-5 pe-3">
                                {{ $rowTotalBudgetCpk > 0 ? number_format($rowTotalBudgetCpk, 2, ',', '.') : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $bCost = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                                    $bProd = $budgetProd[$areaName][$d] ?? 0;
                                    $bCpk = $bProd > 0 ? $bCost / $bProd : 0;
                                @endphp
                                <td
                                    class="text-end border-bottom-0 fw-medium {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ $bCpk > 0 ? number_format($bCpk, 2, ',', '.') : '-' }}</td>
                            @endfor
                        </tr>

                        <!-- 8. ACTUAL COST PER KG -->
                        <tr class="row-cost-kg">
                            <td class="sticky-col-1 border-bottom-0"></td>
                            <td class="sticky-col-2 ps-4 border-bottom-0">Cost per kg (Actual)</td>
                            <td class="sticky-col-3 border-bottom-0">Rp</td>
                            <td class="sticky-col-4 val-cell pe-3">
                                {{ $rowTtdActualCpk > 0 ? number_format($rowTtdActualCpk, 2, ',', '.') : '-' }}</td>
                            <td class="sticky-col-5 val-cell pe-3">
                                {{ $rowTotalActualCpk > 0 ? number_format($rowTotalActualCpk, 2, ',', '.') : '-' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php $cpk = ($actualProd[$areaName][$d] ?? 0) > 0 ? ($costsPerAreaDay[$areaName][$d] ?? 0) / $actualProd[$areaName][$d] : 0; @endphp
                                <td
                                    class="text-end border-bottom-0 val-cell {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ $cpk > 0 ? number_format($cpk, 2, ',', '.') : '-' }}</td>
                            @endfor
                        </tr>

                        <!-- 9. PENCAPAIAN CPK (%) -->
                        <tr class="row-percent">
                            <td class="sticky-col-1 border-bottom"></td>
                            <td class="sticky-col-2 ps-4 border-bottom fw-bold text-dark"><i
                                    class="bi bi-activity me-2 opacity-50"></i>% Pencapaian Cost per Kg</td>
                            <td class="sticky-col-3 border-bottom text-dark fw-bold">%</td>
                            <td class="sticky-col-4 pe-3 {{ $pctClassTtdCpk }}">
                                {{ $rowTtdCpkPercent > 0 ? number_format($rowTtdCpkPercent, 1, ',', '.') . '%' : '-' }}
                            </td>
                            <td class="sticky-col-5 pe-3 {{ $pctClassTotalCpk }}">
                                {{ $rowTotalCpkPercent > 0 ? number_format($rowTotalCpkPercent, 1, ',', '.') . '%' : '-' }}
                            </td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $cpk =
                                        ($actualProd[$areaName][$d] ?? 0) > 0
                                            ? ($costsPerAreaDay[$areaName][$d] ?? 0) / $actualProd[$areaName][$d]
                                            : 0;
                                    $bCost = $budgetCostsPerAreaDay[$areaName][$d] ?? 0;
                                    $bProd = $budgetProd[$areaName][$d] ?? 0;
                                    $bCpk = $bProd > 0 ? $bCost / $bProd : 0;

                                    if ($isKpiMode) {
                                        $percent = $cpk > 0 ? ($bCpk / $cpk) * 100 : 0;
                                        $textClass =
                                            $percent > 0
                                                ? ($percent >= 100
                                                    ? 'text-success'
                                                    : 'text-danger')
                                                : 'text-muted';
                                    } else {
                                        $percent = $bCpk > 0 && $cpk > 0 ? ($cpk / $bCpk) * 100 : 0;
                                        $textClass =
                                            $percent > 0
                                                ? ($percent <= 100
                                                    ? 'text-success'
                                                    : 'text-danger')
                                                : 'text-muted';
                                    }
                                @endphp
                                <td
                                    class="text-end border-bottom fw-bold {{ $textClass }} {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                    {{ $percent > 0 ? number_format($percent, 1, ',', '.') . '%' : '-' }}</td>
                            @endfor
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="{{ 5 + $daysInMonth }}"
                            style="background-color: #f1f5f9; height: 16px; border:none;"></td>
                    </tr>

                    <!-- ========================================================
                                         GRAND TOTAL PABRIK
                                         ======================================================== -->
                    @php
                        $grandTotalCost = 0;
                        $grandTotalBudgetCost = 0;
                        $grandTotalRss = 0;
                        $grandTotalBudgetRss = 0;

                        $grandTtdCost = 0;
                        $grandTtdBudgetCost = 0;
                        $grandTtdRss = 0;
                        $grandTtdBudgetRss = 0;

                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $dailyTotalCost = 0;
                            $dailyTotalBudgetCost = 0;

                            foreach (array_keys($areas) as $a) {
                                $dailyTotalCost += $costsPerAreaDay[$a][$d] ?? 0;
                                $dailyTotalBudgetCost += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                            }

                            $dailyRss = $actualTotalRss[$d] ?? 0;
                            $dailyBudgetRss = $budgetTotalRss[$d] ?? 0;

                            // TOTAL
                            $grandTotalCost += $dailyTotalCost;
                            $grandTotalBudgetCost += $dailyTotalBudgetCost;
                            $grandTotalRss += $dailyRss;
                            $grandTotalBudgetRss += $dailyBudgetRss;

                            // TTD
                            if ($d <= $ttdDay) {
                                $grandTtdCost += $dailyTotalCost;
                                $grandTtdBudgetCost += $dailyTotalBudgetCost;
                                $grandTtdRss += $dailyRss;
                                $grandTtdBudgetRss += $dailyBudgetRss;
                            }
                        }

                        // PERSENTASE COST GRAND TOTAL
                        if ($isKpiMode) {
                            $grandTotalCostPct =
                                $grandTotalCost > 0 ? ($grandTotalBudgetCost / $grandTotalCost) * 100 : 0;
                            $grandTtdCostPct = $grandTtdCost > 0 ? ($grandTtdBudgetCost / $grandTtdCost) * 100 : 0;
                            $pctClassGrandTotalCost =
                                $grandTotalCostPct > 0
                                    ? ($grandTotalCostPct >= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                            $pctClassGrandTtdCost =
                                $grandTtdCostPct > 0
                                    ? ($grandTtdCostPct >= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                        } else {
                            $grandTotalCostPct =
                                $grandTotalBudgetCost > 0 ? ($grandTotalCost / $grandTotalBudgetCost) * 100 : 0;
                            $grandTtdCostPct =
                                $grandTtdBudgetCost > 0 ? ($grandTtdCost / $grandTtdBudgetCost) * 100 : 0;
                            $pctClassGrandTotalCost =
                                $grandTotalCostPct > 0
                                    ? ($grandTotalCostPct <= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                            $pctClassGrandTtdCost =
                                $grandTtdCostPct > 0
                                    ? ($grandTtdCostPct <= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                        }

                        // PERSENTASE PRODUKSI GRAND TOTAL (>= 100 Hijau)
                        $grandTotalProdPct =
                            $grandTotalBudgetRss > 0 ? ($grandTotalRss / $grandTotalBudgetRss) * 100 : 0;
                        $grandTtdProdPct = $grandTtdBudgetRss > 0 ? ($grandTtdRss / $grandTtdBudgetRss) * 100 : 0;
                        $pctClassGrandTotalProd =
                            $grandTotalProdPct > 0
                                ? ($grandTotalProdPct >= 100
                                    ? 'text-success'
                                    : 'text-danger')
                                : 'text-muted';
                        $pctClassGrandTtdProd =
                            $grandTtdProdPct > 0
                                ? ($grandTtdProdPct >= 100
                                    ? 'text-success'
                                    : 'text-danger')
                                : 'text-muted';

                        // PERSENTASE CPK GRAND TOTAL
                        $grandTotalActualCpk = $grandTotalRss > 0 ? $grandTotalCost / $grandTotalRss : 0;
                        $grandTtdActualCpk = $grandTtdRss > 0 ? $grandTtdCost / $grandTtdRss : 0;
                        $grandTotalBudgetCpk =
                            $grandTotalBudgetRss > 0 ? $grandTotalBudgetCost / $grandTotalBudgetRss : 0;
                        $grandTtdBudgetCpk = $grandTtdBudgetRss > 0 ? $grandTtdBudgetCost / $grandTtdBudgetRss : 0;

                        if ($isKpiMode) {
                            $grandTotalCpkPercent =
                                $grandTotalActualCpk > 0 ? ($grandTotalBudgetCpk / $grandTotalActualCpk) * 100 : 0;
                            $grandTtdCpkPercent =
                                $grandTtdActualCpk > 0 ? ($grandTtdBudgetCpk / $grandTtdActualCpk) * 100 : 0;
                            $pctClassGrandTotalCpk =
                                $grandTotalCpkPercent > 0
                                    ? ($grandTotalCpkPercent >= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                            $pctClassGrandTtdCpk =
                                $grandTtdCpkPercent > 0
                                    ? ($grandTtdCpkPercent >= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                        } else {
                            $grandTotalCpkPercent =
                                $grandTotalBudgetCpk > 0 ? ($grandTotalActualCpk / $grandTotalBudgetCpk) * 100 : 0;
                            $grandTtdCpkPercent =
                                $grandTtdBudgetCpk > 0 ? ($grandTtdActualCpk / $grandTtdBudgetCpk) * 100 : 0;
                            $pctClassGrandTotalCpk =
                                $grandTotalCpkPercent > 0
                                    ? ($grandTotalCpkPercent <= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                            $pctClassGrandTtdCpk =
                                $grandTtdCpkPercent > 0
                                    ? ($grandTtdCpkPercent <= 100
                                        ? 'text-success'
                                        : 'text-danger')
                                    : 'text-muted';
                        }
                    @endphp

                    <!-- 1. GRAND TOTAL BUDGET COST -->
                    <tr class="row-cost-budget" style="border-top: 3px solid #cbd5e1;">
                        <td class="sticky-col-1" style="border-top: 3px solid #cbd5e1;"></td>
                        <td class="sticky-col-2 fw-bold" style="border-top: 3px solid #cbd5e1; letter-spacing: 0.5px;"><i
                                class="bi bi-wallet2 me-1 opacity-50"></i>Total Budget Cost</td>
                        <td class="sticky-col-3 text-secondary" style="border-top: 3px solid #cbd5e1;">Rp</td>
                        <td class="sticky-col-4 pe-3" style="border-top: 3px solid #cbd5e1;">
                            {{ $grandTtdBudgetCost > 0 ? number_format($grandTtdBudgetCost, 0, ',', '.') : '-' }}</td>
                        <td class="sticky-col-5 pe-3" style="border-top: 3px solid #cbd5e1;">
                            {{ $grandTotalBudgetCost > 0 ? number_format($grandTotalBudgetCost, 0, ',', '.') : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $tBudgetCostDay = 0;
                                foreach (array_keys($areas) as $a) {
                                    $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                                }
                            @endphp
                            <td class="text-end val-cell {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}"
                                style="border-top: 3px solid #cbd5e1;">
                                {{ $tBudgetCostDay > 0 ? number_format($tBudgetCostDay, 0, ',', '.') : '-' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 2. GRAND TOTAL ACTUAL COST -->
                    <tr class="row-cost-actual">
                        <td class="sticky-col-1 border-top-0"></td>
                        <td class="sticky-col-2 ps-4 border-top-0 fw-bold">Total Actual Cost</td>
                        <td class="sticky-col-3 border-top-0">Rp</td>
                        <td class="sticky-col-4 text-primary pe-3">
                            {{ $grandTtdCost > 0 ? number_format($grandTtdCost, 0, ',', '.') : '-' }}</td>
                        <td class="sticky-col-5 text-primary pe-3">
                            {{ $grandTotalCost > 0 ? number_format($grandTotalCost, 0, ',', '.') : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $tCostDay = 0;
                                foreach (array_keys($areas) as $a) {
                                    $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                                }
                            @endphp
                            <td class="text-end val-cell {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ $tCostDay > 0 ? number_format($tCostDay, 0, ',', '.') : '-' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 3. GRAND TOTAL PENCAPAIAN COST (%) -->
                    <tr class="row-footer-percent">
                        <td class="sticky-col-1 border-bottom-0"></td>
                        <td class="sticky-col-2 ps-4 border-bottom-0"><i
                                class="bi bi-arrow-return-right me-1 opacity-50"></i>% Total Pencapaian Cost</td>
                        <td class="sticky-col-3 border-bottom-0">%</td>
                        <td class="sticky-col-4 pe-3 fw-bold {{ $pctClassGrandTtdCost }}">
                            {{ $grandTtdCostPct > 0 ? number_format($grandTtdCostPct, 1, ',', '.') . '%' : '-' }}</td>
                        <td class="sticky-col-5 pe-3 fw-bold {{ $pctClassGrandTotalCost }}">
                            {{ $grandTotalCostPct > 0 ? number_format($grandTotalCostPct, 1, ',', '.') . '%' : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $tCostDay = 0;
                                $tBudgetCostDay = 0;
                                foreach (array_keys($areas) as $a) {
                                    $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                                    $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                                }
                                if ($isKpiMode) {
                                    $cPct = $tCostDay > 0 ? ($tBudgetCostDay / $tCostDay) * 100 : 0;
                                    $cClass =
                                        $cPct > 0 ? ($cPct >= 100 ? 'text-success' : 'text-danger') : 'text-muted';
                                } else {
                                    $cPct = $tBudgetCostDay > 0 ? ($tCostDay / $tBudgetCostDay) * 100 : 0;
                                    $cClass =
                                        $cPct > 0 ? ($cPct <= 100 ? 'text-success' : 'text-danger') : 'text-muted';
                                }
                            @endphp
                            <td
                                class="text-end border-bottom-0 fw-bold {{ $cClass }} {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ $cPct > 0 ? number_format($cPct, 1, ',', '.') . '%' : '-' }}</td>
                        @endfor
                    </tr>

                    <!-- 4. GRAND TOTAL BUDGET PRODUKSI (RSS) -->
                    <tr class="row-budget-production">
                        <td class="sticky-col-1 border-top-0"></td>
                        <td class="sticky-col-2 ps-4 border-top-0 fw-bold"><i
                                class="bi bi-bullseye me-1 opacity-50"></i>Total Budget Produksi (RSS)</td>
                        <td class="sticky-col-3 border-top-0">Kg</td>
                        <td class="sticky-col-4 pe-3">
                            {{ $grandTtdBudgetRss > 0 ? number_format($grandTtdBudgetRss, 0, ',', '.') : '-' }}</td>
                        <td class="sticky-col-5 pe-3">
                            {{ $grandTotalBudgetRss > 0 ? number_format($grandTotalBudgetRss, 0, ',', '.') : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            <td
                                class="text-end border-top-0 {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ ($budgetTotalRss[$d] ?? 0) > 0 ? number_format($budgetTotalRss[$d], 0, ',', '.') : '-' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 5. GRAND TOTAL ACTUAL PRODUKSI (RSS) -->
                    <tr class="row-production">
                        <td class="sticky-col-1 border-top-0"></td>
                        <td class="sticky-col-2 ps-4 border-top-0 fw-bold"><i
                                class="bi bi-box-seam me-1 opacity-50"></i>Total Actual Produksi (RSS)</td>
                        <td class="sticky-col-3 border-top-0">Kg</td>
                        <td class="sticky-col-4 pe-3">
                            {{ $grandTtdRss > 0 ? number_format($grandTtdRss, 0, ',', '.') : '-' }}</td>
                        <td class="sticky-col-5 pe-3">
                            {{ $grandTotalRss > 0 ? number_format($grandTotalRss, 0, ',', '.') : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            <td
                                class="text-end border-top-0 {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ ($actualTotalRss[$d] ?? 0) > 0 ? number_format($actualTotalRss[$d], 0, ',', '.') : '-' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 6. GRAND TOTAL PENCAPAIAN PRODUKSI (%) -->
                    <tr class="row-footer-percent">
                        <td class="sticky-col-1 border-bottom-0"></td>
                        <td class="sticky-col-2 ps-4 border-bottom-0"><i
                                class="bi bi-arrow-return-right me-1 opacity-50"></i>% Total Pencapaian Prod.</td>
                        <td class="sticky-col-3 border-bottom-0">%</td>
                        <td class="sticky-col-4 pe-3 fw-bold {{ $pctClassGrandTtdProd }}">
                            {{ $grandTtdProdPct > 0 ? number_format($grandTtdProdPct, 1, ',', '.') . '%' : '-' }}</td>
                        <td class="sticky-col-5 pe-3 fw-bold {{ $pctClassGrandTotalProd }}">
                            {{ $grandTotalProdPct > 0 ? number_format($grandTotalProdPct, 1, ',', '.') . '%' : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $pAct = $actualTotalRss[$d] ?? 0;
                                $pBud = $budgetTotalRss[$d] ?? 0;
                                $pPct = $pBud > 0 ? ($pAct / $pBud) * 100 : 0;
                                $pClass = $pPct > 0 ? ($pPct >= 100 ? 'text-success' : 'text-danger') : 'text-muted';
                            @endphp
                            <td
                                class="text-end border-bottom-0 fw-bold {{ $pClass }} {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ $pPct > 0 ? number_format($pPct, 1, ',', '.') . '%' : '-' }}</td>
                        @endfor
                    </tr>

                    <!-- 7. GRAND TOTAL BUDGET COST PER KG -->
                    <tr class="row-budget">
                        <td class="sticky-col-1 border-bottom-0"></td>
                        <td class="sticky-col-2 ps-4 border-bottom-0 fw-bold">Total Budget Cost per kg</td>
                        <td class="sticky-col-3 border-bottom-0">Rp</td>
                        <td class="sticky-col-4 pe-3">
                            {{ $grandTtdBudgetCpk > 0 ? number_format($grandTtdBudgetCpk, 2, ',', '.') : '-' }}</td>
                        <td class="sticky-col-5 pe-3">
                            {{ $grandTotalBudgetCpk > 0 ? number_format($grandTotalBudgetCpk, 2, ',', '.') : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $tBudgetCostDay = 0;
                                foreach (array_keys($areas) as $a) {
                                    $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                                }
                                $finalBudgetCpk =
                                    ($budgetTotalRss[$d] ?? 0) > 0 ? $tBudgetCostDay / $budgetTotalRss[$d] : 0;
                            @endphp
                            <td
                                class="text-end border-bottom-0 fw-medium {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ $finalBudgetCpk > 0 ? number_format($finalBudgetCpk, 2, ',', '.') : '-' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 8. GRAND TOTAL ACTUAL COST PER KG -->
                    <tr class="row-cost-kg">
                        <td class="sticky-col-1 border-bottom-0"></td>
                        <td class="sticky-col-2 ps-4 border-bottom-0 fw-bold">Total Cost per kg (Actual)</td>
                        <td class="sticky-col-3 border-bottom-0">Rp</td>
                        <td class="sticky-col-4 val-cell pe-3">
                            {{ $grandTtdActualCpk > 0 ? number_format($grandTtdActualCpk, 2, ',', '.') : '-' }}</td>
                        <td class="sticky-col-5 val-cell pe-3">
                            {{ $grandTotalActualCpk > 0 ? number_format($grandTotalActualCpk, 2, ',', '.') : '-' }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $tCostDay = 0;
                                foreach (array_keys($areas) as $a) {
                                    $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                                }
                                $finalCpk = ($actualTotalRss[$d] ?? 0) > 0 ? $tCostDay / $actualTotalRss[$d] : 0;
                            @endphp
                            <td
                                class="text-end border-bottom-0 val-cell {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ $finalCpk > 0 ? number_format($finalCpk, 2, ',', '.') : '-' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 9. GRAND TOTAL PENCAPAIAN CPK (%) -->
                    <tr class="row-percent">
                        <td class="sticky-col-1 border-bottom"></td>
                        <td class="sticky-col-2 ps-4 border-bottom fw-bold text-dark"><i
                                class="bi bi-activity me-2 opacity-50"></i>% Total Pencapaian Cost per Kg</td>
                        <td class="sticky-col-3 border-bottom text-dark fw-bold">%</td>
                        <td class="sticky-col-4 pe-3 {{ $pctClassGrandTtdCpk }}">
                            {{ $grandTtdCpkPercent > 0 ? number_format($grandTtdCpkPercent, 1, ',', '.') . '%' : '-' }}
                        </td>
                        <td class="sticky-col-5 pe-3 {{ $pctClassGrandTotalCpk }}">
                            {{ $grandTotalCpkPercent > 0 ? number_format($grandTotalCpkPercent, 1, ',', '.') . '%' : '-' }}
                        </td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $tCostDay = 0;
                                $tBudgetCostDay = 0;
                                foreach (array_keys($areas) as $a) {
                                    $tCostDay += $costsPerAreaDay[$a][$d] ?? 0;
                                    $tBudgetCostDay += $budgetCostsPerAreaDay[$a][$d] ?? 0;
                                }
                                $finalCpk = ($actualTotalRss[$d] ?? 0) > 0 ? $tCostDay / $actualTotalRss[$d] : 0;
                                $finalBudgetCpk =
                                    ($budgetTotalRss[$d] ?? 0) > 0 ? $tBudgetCostDay / $budgetTotalRss[$d] : 0;

                                if ($isKpiMode) {
                                    $percent = $finalCpk > 0 ? ($finalBudgetCpk / $finalCpk) * 100 : 0;
                                    $textClass =
                                        $percent > 0
                                            ? ($percent >= 100
                                                ? 'text-success'
                                                : 'text-danger')
                                            : 'text-muted';
                                } else {
                                    $percent =
                                        $finalBudgetCpk > 0 && $finalCpk > 0 ? ($finalCpk / $finalBudgetCpk) * 100 : 0;
                                    $textClass =
                                        $percent > 0
                                            ? ($percent <= 100
                                                ? 'text-success'
                                                : 'text-danger')
                                            : 'text-muted';
                                }
                            @endphp
                            <td
                                class="text-end border-bottom fw-bold {{ $textClass }} {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                {{ $percent > 0 ? number_format($percent, 1, ',', '.') . '%' : '-' }}
                            </td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
