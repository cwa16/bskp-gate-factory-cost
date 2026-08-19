@extends('layouts.app')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* ========================================================
                       MODERN ENTERPRISE STYLING
    ======================================================== */
    .container-fluid {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Styling Card KPI */
    .card-stat {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background-color: #ffffff;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.03), 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease-in-out;
    }

    .card-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        border-color: #cbd5e1;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* Styling Filter Toolbar */
    .enterprise-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }

    .enterprise-toolbar select {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-weight: 600;
    }

    .enterprise-toolbar select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    /* Styling Tabs Modern */
    .nav-pills {
        background: #f1f5f9;
        padding: 6px;
        border-radius: 12px;
        display: inline-flex;
    }

    .nav-pills .nav-link {
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 24px;
        color: #64748b;
        background: transparent;
        border: none;
        margin-right: 4px;
        transition: all 0.2s;
    }

    .nav-pills .nav-link:hover {
        color: #1e293b;
    }

    .nav-pills .nav-link.active {
        background-color: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Tweak Typography */
    .text-muted {
        color: #64748b !important;
    }

    .text-dark {
        color: #0f172a !important;
    }

    .border-bottom {
        border-bottom-color: #e2e8f0 !important;
    }

    .border-end {
        border-right-color: #e2e8f0 !important;
    }
</style>

@section('content')
    <div class="container-fluid py-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0" style="letter-spacing: -0.5px;">Dashboard Operasional</h4>
                <p class="text-muted small m-0 mt-1">Performance Overview by Employee Status</p>
            </div>

            <form action="{{ route('dashboard') }}" method="GET" class="d-flex gap-2 enterprise-toolbar align-items-center">
                <span class="text-muted small fw-bold me-1">Periode:</span>
                <select name="month" class="form-select form-select-sm">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm">
                    @php $crYear = date('Y'); @endphp
                    @for ($y = $crYear - 1; $y <= $crYear + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-medium"
                    style="border-radius: 6px;">Filter</button>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-end mb-3 mt-2">
            <div>
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-wallet2 text-primary me-2"></i>Month-to-Date (MTD)</h5>
                <small class="text-muted">Akumulasi pencapaian s.d. tanggal:
                    <strong>{{ \Carbon\Carbon::parse($toDate)->translatedFormat('d F Y') }}</strong></small>
            </div>
        </div>

        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-md-6">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                    Target Produksi</p>
                                <h3 class="mb-0 fw-bold text-dark">
                                    {{ number_format($kpiProduksi['produksi_kg'], 0, ',', '.') }}
                                    <span class="fs-6 fw-normal text-muted ms-1">Kg</span>
                                </h3>
                            </div>
                            <div class="icon-box" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total
                                    Aktual HK</p>
                                <h3 class="mb-0 fw-bold text-dark">
                                    {{ number_format($kpiProduksi['total_hk'], 1, ',', '.') }}
                                    <span class="fs-6 fw-normal text-muted ms-1">HK</span>
                                </h3>
                            </div>
                            <div class="icon-box" style="background-color: rgba(13, 202, 253, 0.1); color: #0dcaf0;">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                    Produktivitas</p>
                                <h3 class="mb-0 fw-bold text-dark">
                                    {{ number_format($kpiProduksi['kg_per_hk'], 2, ',', '.') }}
                                    <span class="fs-6 fw-normal text-muted ms-1">Kg/HK</span>
                                </h3>
                            </div>
                            <div class="icon-box" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-stat h-100 position-relative overflow-hidden border-warning"
                    style="border-left: 4px solid #ffc107;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Unit
                                    Cost (RSS)</p>
                                <h3 class="mb-0 fw-bold text-dark">
                                    <span
                                        class="fs-6 fw-normal text-muted me-1">Rp</span>{{ number_format($kpiProduksi['rp_per_kg'], 0, ',', '.') }}<span
                                        class="fs-6 fw-normal text-muted ms-1">/Kg</span>
                                </h3>
                            </div>
                            <div class="icon-box" style="background-color: rgba(255, 193, 7, 0.15); color: #d39e00;">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                        </div>

                        @php
                            $pct = $kpiProduksi['persentase'];
                            $isOver = $pct > 100;
                            $badgeColor =
                                $pct > 0
                                    ? ($isOver
                                        ? 'bg-danger text-white'
                                        : 'bg-success text-white')
                                    : 'bg-secondary text-white';
                            $iconDir = $isOver ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
                        @endphp

                        <div class="mt-3 d-flex align-items-center">
                            <span class="badge {{ $badgeColor }} shadow-sm px-2 py-1 me-2"
                                style="font-size: 12px; border-radius: 6px;">
                                <i
                                    class="bi {{ $iconDir }} me-1"></i>{{ $pct > 0 ? number_format($pct, 1, ',', '.') : '0' }}%
                            </span>
                            <small class="text-muted" style="font-size: 11px;">vs Plan Budget</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-table text-primary me-2"></i>Rekapitulasi Budget
                        Manpower (MTD)</h6>
                    <p class="text-muted small m-0 mt-1">Perbandingan pemakaian budget vs aktual hingga hari ini.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle text-center" style="font-size: 13px;">
                        <thead class="bg-light text-muted"
                            style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="text-start ps-4 fw-bold border-0 py-3">Kategori Manpower</th>
                                <th class="fw-bold border-0 py-3">Total Budget (1 Bulan)</th>
                                <th class="fw-bold border-0 py-3">Terpakai s.d Hari Ini</th>
                                <th class="fw-bold border-0 py-3">Sisa Budget</th>
                                <th class="fw-bold border-0 pe-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach (['Regular', 'Contract FL', 'Total'] as $cat)
                                @php
                                    $p = $mtdData[$cat]['plan'];
                                    $a = $mtdData[$cat]['actual'];
                                    $sisa = $p - $a;

                                    $statusBadge = '';
                                    if ($p == 0 && $a == 0) {
                                        $statusBadge =
                                            '<span class="badge bg-secondary px-2 py-1" style="font-weight: 500;">No Data</span>';
                                    } elseif ($sisa < 0) {
                                        $statusBadge =
                                            '<span class="badge bg-danger px-2 py-1 shadow-sm" style="font-weight: 500;"><i class="bi bi-exclamation-triangle me-1"></i>Over Budget</span>';
                                    } else {
                                        $statusBadge =
                                            '<span class="badge bg-success px-2 py-1 shadow-sm" style="font-weight: 500;"><i class="bi bi-check-circle me-1"></i>Aman</span>';
                                    }
                                @endphp
                                <tr class="{{ $cat == 'Total' ? 'bg-light fw-bold' : '' }}">
                                    <td
                                        class="text-start ps-4 {{ $cat == 'Total' ? 'text-dark' : 'text-secondary fw-medium' }}">
                                        {{ $cat }}</td>
                                    <td class="text-primary">Rp {{ number_format($p, 0, ',', '.') }}</td>
                                    <td class="text-info">Rp {{ number_format($a, 0, ',', '.') }}</td>
                                    <td class="{{ $sisa < 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                        Rp {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                    <td class="pe-4">{!! $statusBadge !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 fw-bold text-dark"><i class="bi bi-activity text-primary me-2"></i>Trend
                                Harian: Cost per Kg (RSS) vs Target</h6>
                        </div>
                        <div class="d-flex gap-3 small fw-medium">
                            <span class="text-muted"><i class="bi bi-circle-fill text-primary me-1"
                                    style="font-size: 8px;"></i>Actual CPK</span>
                            <span class="text-muted"><i class="bi bi-dash-lg text-danger me-1"></i>Batas Aman
                                (Budget)</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <canvas id="dashboardCpkChart" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="m-0 fw-bold text-dark"><i class="bi bi-bar-chart-steps text-success me-2"></i>Daily Cost Trend
                </h6>
                <p class="text-muted small m-0 mt-1">Perbandingan Rupiah Plan vs Actual per tanggal. <span
                        class="text-danger">(Titik merah = Over Budget)</span></p>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4 pb-2">
                    <div class="col-12">
                        <h6 class="text-center fw-bold text-dark mb-3 text-uppercase"
                            style="letter-spacing: 1px; font-size: 13px;">Total Pabrik (Regular + Contract FL)</h6>
                        <div style="height: 280px; width: 100%;">
                            <canvas id="dailyTrendCombined"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row pt-4 border-top">
                    <div class="col-lg-6 mb-4 mb-lg-0 border-end pe-lg-4">
                        <h6 class="text-center fw-bold text-muted mb-3 text-uppercase"
                            style="letter-spacing: 1px; font-size: 12px;">Karyawan Regular</h6>
                        <div style="height: 220px; width: 100%;">
                            <canvas id="dailyTrendReg"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6 ps-lg-4">
                        <h6 class="text-center fw-bold text-muted mb-3 text-uppercase"
                            style="letter-spacing: 1px; font-size: 12px;">Karyawan Contract FL</h6>
                        <div style="height: 220px; width: 100%;">
                            <canvas id="dailyTrendFL"></canvas>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    @foreach ($dailyTrendPerArea as $areaName => $chartData)
                        <div class="col-xl-6 col-lg-12 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="fw-bold mb-0 text-dark"><i
                                            class="bi bi-graph-up text-primary me-2"></i>Daily Trend: {{ $areaName }}
                                    </h6>
                                    <p class="text-secondary small mt-1 mb-0">Budget vs Actual (Rupiah)</p>
                                </div>
                                <div class="card-body">
                                    <div style="height: 300px;">
                                        <canvas id="chart-{{ Str::slug($areaName) }}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <ul class="nav nav-pills mb-3 shadow-sm" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-regular-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-regular" type="button" role="tab">
                    <i class="bi bi-person-badge me-2"></i>Data Regular
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-contract-tab" data-bs-toggle="pill" data-bs-target="#pills-contract"
                    type="button" role="tab">
                    <i class="bi bi-person-workspace me-2"></i>Data Contract FL
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-regular" role="tabpanel">
                @include('dashboard.partials.content', [
                    'data' => $dataRegular,
                    'id_suffix' => 'Reg',
                    'color' => '#0d6efd',
                ])
            </div>
            <div class="tab-pane fade" id="pills-contract" role="tabpanel">
                @include('dashboard.partials.content', [
                    'data' => $dataContractFL,
                    'id_suffix' => 'FL',
                    'color' => '#198754',
                ])
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. RENDER DAILY TREND COST (3 CHARTS)
            const trendData = @json($dailyTrend);

            // Menghitung array gabungan secara dinamis (Plan & Actual)
            const combinedPlan = trendData['Regular'].plan.map((val, index) => val + (trendData['Contract FL'].plan[
                index] || 0));
            const combinedActual = trendData['Regular'].actual.map((val, index) => val + (trendData['Contract FL']
                .actual[index] || 0));

            function renderTrendChart(canvasId, planData, actualData) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels,
                        datasets: [{
                                label: 'Plan',
                                data: planData,
                                borderColor: 'rgba(148, 163, 184, 1)', // Abu-abu elegan untuk Plan
                                backgroundColor: 'rgba(148, 163, 184, 0.1)',
                                borderWidth: 2,
                                borderDash: [5, 5], // Garis putus-putus untuk Plan
                                pointRadius: 2,
                                fill: true,
                                tension: 0.3
                            },
                            {
                                label: 'Actual',
                                data: actualData,
                                borderColor: 'rgba(15, 23, 42, 1)', // Dark Navy untuk Actual
                                backgroundColor: 'transparent',
                                borderWidth: 2.5,
                                fill: false,
                                tension: 0.3,
                                pointBackgroundColor: function(context) {
                                    let index = context.dataIndex;
                                    let actualValue = context.dataset.data[index];
                                    let planValue = context.chart.data.datasets[0].data[index];
                                    return actualValue > planValue ? '#dc3545' :
                                        '#0f172a'; // Merah jika over
                                },
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointRadius: function(context) {
                                    let index = context.dataIndex;
                                    let actualValue = context.dataset.data[index];
                                    let planValue = context.chart.data.datasets[0].data[index];
                                    return actualValue > planValue ? 5 :
                                        3; // Titik lebih besar jika over
                                },
                                pointHoverRadius: 7
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 12,
                                titleFont: {
                                    size: 13,
                                    family: 'Inter'
                                },
                                bodyFont: {
                                    size: 13,
                                    family: 'Inter'
                                },
                                callbacks: {
                                    title: function(context) {
                                        return 'Tanggal: ' + context[0].label;
                                    },
                                    label: function(context) {
                                        let value = context.raw || 0;
                                        return context.dataset.label + ': Rp ' + value.toLocaleString(
                                            'id-ID');
                                    },
                                    afterBody: function(context) {
                                        if (context.length >= 2) {
                                            let planVal = context[0].datasetIndex === 0 ? context[0]
                                                .raw : context[1].raw;
                                            let actVal = context[1].datasetIndex === 1 ? context[1]
                                                .raw : context[0].raw;
                                            let diff = planVal - actVal;
                                            if (diff < 0) {
                                                return '\n⚠️ OVER: Rp ' + Math.abs(diff).toLocaleString(
                                                    'id-ID');
                                            } else {
                                                return '\n✅ AMAN: Rp ' + diff.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                border: {
                                    display: false
                                },
                                grid: {
                                    color: '#f1f5f9'
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    },
                                    color: '#64748b',
                                    callback: function(value) {
                                        if (value >= 1000000) return (value / 1000000) + ' Jt';
                                        if (value >= 1000) return (value / 1000) + ' Rb';
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Panggil Fungsi Render untuk ke-3 Chart
            renderTrendChart('dailyTrendCombined', combinedPlan, combinedActual);
            renderTrendChart('dailyTrendReg', trendData['Regular'].plan, trendData['Regular'].actual);
            renderTrendChart('dailyTrendFL', trendData['Contract FL'].plan, trendData['Contract FL'].actual);

            // 2. RENDER COST PER KG CHART
            if (document.getElementById('dashboardCpkChart')) {
                const ctxCpk = document.getElementById('dashboardCpkChart').getContext('2d');

                const labelsCpk = @json($chartCpk['labels']);
                const dataActualCpk = @json($chartCpk['actual']);
                const dataTargetCpk = @json($chartCpk['target']);

                let gradientBlue = ctxCpk.createLinearGradient(0, 0, 0, 400);
                gradientBlue.addColorStop(0, 'rgba(13, 110, 253, 0.2)');
                gradientBlue.addColorStop(1, 'rgba(13, 110, 253, 0)');

                new Chart(ctxCpk, {
                    type: 'line',
                    data: {
                        labels: labelsCpk,
                        datasets: [{
                                label: 'Actual Cost (Rp/Kg)',
                                data: dataActualCpk,
                                borderColor: '#0d6efd',
                                backgroundColor: gradientBlue,
                                borderWidth: 3,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#0d6efd',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Batas Budget (Rp/Kg)',
                                data: dataTargetCpk,
                                borderColor: '#dc3545',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                pointHoverRadius: 0,
                                fill: false,
                                tension: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 12,
                                titleFont: {
                                    size: 13,
                                    family: 'Inter'
                                },
                                bodyFont: {
                                    size: 14,
                                    family: 'Inter',
                                    weight: 'bold'
                                },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': Rp ';
                                        if (context.parsed.y !== null) label += new Intl.NumberFormat(
                                            'id-ID').format(context.parsed.y);
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: {
                                    display: false
                                },
                                grid: {
                                    color: '#f1f5f9'
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    },
                                    color: '#64748b',
                                    callback: function(value) {
                                        return 'Rp ' + value;
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    },
                                    color: '#64748b'
                                }
                            }
                        }
                    }
                });
            }

            // Ambil Data Objek dari Controller
            const chartsData = @json($dailyTrendPerArea);

            // Loop setiap area yang ada di dalam objek
            Object.keys(chartsData).forEach(function(areaName) {
                const data = chartsData[areaName];

                // Format nama area menjadi slug untuk dicocokkan dengan ID canvas
                const slug = areaName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
                const canvasElement = document.getElementById('chart-' + slug);

                if (canvasElement) {
                    const ctx = canvasElement.getContext('2d');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                // 1. REGULAR BUDGET (Garis Putus-putus)
                                {
                                    label: 'Regular (Budget)',
                                    data: data.regular_budget,
                                    borderColor: 'rgba(54, 162, 235, 0.5)', // Biru Pudar
                                    borderDash: [5, 5], // Efek Garis Putus-putus
                                    borderWidth: 2,
                                    tension: 0.3,
                                    pointRadius: 1,
                                    fill: false
                                },
                                // 2. REGULAR ACTUAL (Garis Solid)
                                {
                                    label: 'Regular (Actual)',
                                    data: data.regular_actual,
                                    borderColor: 'rgba(54, 162, 235, 1)', // Biru Solid
                                    backgroundColor: 'rgba(54, 162, 235, 0.05)',
                                    borderWidth: 2.5,
                                    tension: 0.3,
                                    pointRadius: 2,
                                    pointHoverRadius: 5,
                                    fill: true
                                },
                                // 3. CONTRACT FL BUDGET (Garis Putus-putus)
                                {
                                    label: 'Contract FL (Budget)',
                                    data: data.contract_budget,
                                    borderColor: 'rgba(255, 99, 132, 0.5)', // Merah Pudar
                                    borderDash: [5, 5], // Efek Garis Putus-putus
                                    borderWidth: 2,
                                    tension: 0.3,
                                    pointRadius: 1,
                                    fill: false
                                },
                                // 4. CONTRACT FL ACTUAL (Garis Solid)
                                {
                                    label: 'Contract FL (Actual)',
                                    data: data.contract_actual,
                                    borderColor: 'rgba(255, 99, 132, 1)', // Merah Solid
                                    backgroundColor: 'rgba(255, 99, 132, 0.05)',
                                    borderWidth: 2.5,
                                    tension: 0.3,
                                    pointRadius: 2,
                                    pointHoverRadius: 5,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 10,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': Rp ' + new Intl
                                                .NumberFormat('id-ID').format(context.raw);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) return 'Rp ' + (value /
                                                1000000) + 'M';
                                            if (value >= 1000) return 'Rp ' + (value / 1000) +
                                                'K';
                                            return 'Rp ' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
