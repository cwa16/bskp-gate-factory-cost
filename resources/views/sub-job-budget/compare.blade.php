@extends('layouts.app')

@section('content')
    <style>
        .table-budget {
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
            border-color: #e0e0e0;
        }

        .table-budget th {
            background-color: #f8f9fa;
            font-weight: 700;
            border: 1px solid #ddd;
            padding: 6px;
        }

        .table-budget td {
            border: 1px solid #eee;
            padding: 4px 2px;
        }

        /* Layout */
        .row-plan {
            background-color: #fff;
        }

        .row-actual {
            background-color: #fcfcfc;
        }

        .row-persen {
            background-color: #f4f4f4;
            color: #555;
            font-style: italic;
            font-size: 10px;
        }

        .sub-job-cell {
            font-weight: bold;
            color: #333;
        }

        /* Sticky Headers */
        .sticky-area {
            position: sticky;
            left: 0;
            z-index: 5;
            background: #fff !important;
            border-right: 2px solid #ddd !important;
        }

        .sticky-sub {
            position: sticky;
            left: 40px;
            z-index: 5;
            background: #fff !important;
            border-right: 1px solid #eee;
        }

        .col-no {
            width: 35px;
            min-width: 35px;
        }

        .col-area {
            width: 80px;
            min-width: 80px;
        }

        .col-sub {
            width: 140px;
            min-width: 140px;
        }

        .col-pa {
            width: 50px;
            min-width: 50px;
        }

        .mode-rp {
            font-size: 10px;
            font-weight: bold;
            color: #555;
        }

        .btn-rp-active {
            background-color: #198754 !important;
            color: white !important;
            border-color: #198754 !important;
        }
    </style>

    <div class="container-fluid py-4">

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-auto">
                        <form action="{{ route('subjob-budget.compare') }}" method="GET"
                            class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-muted small">Filter:</span>

                            <select name="type" class="form-select form-select-sm fw-medium text-primary">
                                <option value="gabungan" {{ $type == 'gabungan' ? 'selected' : '' }}>Regular + Contract FL
                                </option>
                                <option value="regular" {{ $type == 'regular' ? 'selected' : '' }}>Hanya Regular</option>
                                <option value="contract_fl" {{ $type == 'contract_fl' ? 'selected' : '' }}>Hanya Contract FL
                                </option>
                            </select>

                            <span class="fw-bold text-muted small ms-2">Periode:</span>
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
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
                        </form>
                    </div>

                    <div class="col-auto">
                        <button type="button" id="btnToggleRp" class="btn btn-sm btn-outline-success px-3"
                            onclick="toggleRupiah()">
                            <i class="bi bi-cash-stack me-1"></i> <span id="btnText">Tampilkan Rupiah</span>
                        </button>
                        <a href="{{ route('subjob-budget.compare.export') }}" class="btn btn-sm btn-success ms-1"><i
                                class="bi bi-file-excel"></i> Export</a>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($reports as $report)
            @php
                // Inisialisasi Grand Total Per Tabel/Report
                $grandPlanQty = 0;
                $grandPlanRp = 0;
                $grandActualQty = 0;
                $grandActualRp = 0;

                // --- VARIABLE BARU UNTUK GRAND TOTAL TTD ---
                $grandTtdPlanQty = 0;
                $grandTtdPlanRp = 0;
                $grandTtdActualQty = 0;
                $grandTtdActualRp = 0;

                $dailyTotals = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dailyTotals[$d] = [
                        'plan_qty' => 0,
                        'plan_rp' => 0,
                        'actual_qty' => 0,
                        'actual_rp' => 0,
                    ];
                }
            @endphp

            <div class="card shadow-sm mb-5 border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 text-primary">{{ $report['title'] }}</h5>
                    <span class="badge bg-secondary mode-rp d-none">Nilai Rp dalam Ribuan (x1.000)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 60vh;">
                        <table class="table table-bordered table-budget mb-0">
                            <thead class="bg-light sticky-top" style="z-index: 10;">
                                <tr>
                                    <th rowspan="2" class="col-no">No</th>
                                    <th rowspan="2" class="col-area">Area</th>
                                    <th rowspan="2" class="col-sub">Sub Job</th>
                                    <th rowspan="2" class="col-pa">Data</th>
                                    <th colspan="{{ $daysInMonth }}" class="text-center">
                                        {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                                    </th>
                                    <th rowspan="2" class="bg-info-subtle text-dark text-center"
                                        style="min-width: 70px;">TTD</th>
                                    <th rowspan="2" class="bg-warning-subtle text-dark text-center"
                                        style="min-width: 80px;">TOTAL</th>
                                </tr>
                                <tr>
                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        <th class="text-center"
                                            style="min-width: 30px; {{ $d == $ttdDay ? 'background-color: #e0f7fa;' : '' }}">
                                            {{ $d }}
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach ($structure as $areaName => $subJobs)
                                    @foreach ($subJobs as $index => $job)
                                        @php
                                            $rowPlanQty = 0;
                                            $rowPlanRp = 0;
                                            $rowActualQty = 0;
                                            $rowActualRp = 0;

                                            // --- VARIABLE BARU UNTUK ROW TTD ---
                                            $rowTtdPlanQty = 0;
                                            $rowTtdPlanRp = 0;
                                            $rowTtdActualQty = 0;
                                            $rowTtdActualRp = 0;
                                        @endphp

                                        <tr class="row-plan">
                                            @if ($index === 0)
                                                <td rowspan="{{ count($subJobs) * 3 }}" class="text-center align-middle">
                                                    {{ $no++ }}</td>
                                                <td rowspan="{{ count($subJobs) * 3 }}" class="fw-bold align-middle">
                                                    {{ $areaName }}</td>
                                            @endif
                                            <td rowspan="3" class="sub-job-cell align-middle"
                                                style="background-color: {{ $job['color'] ?? '#fff' }}; opacity: 0.9;">
                                                {{ $job['name'] }}
                                            </td>
                                            <td class="fw-bold text-success">Plan</td>

                                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                                @php
                                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                                    $data = $report['plan'][$job['id']][$dateStr] ?? [
                                                        'qty' => 0,
                                                        'rp' => 0,
                                                    ];

                                                    $rowPlanQty += $data['qty'];
                                                    $rowPlanRp += $data['rp'];
                                                    $dailyTotals[$d]['plan_qty'] += $data['qty'];
                                                    $dailyTotals[$d]['plan_rp'] += $data['rp'];
                                                    $grandPlanQty += $data['qty'];
                                                    $grandPlanRp += $data['rp'];

                                                    // Jika tanggal <= Hari Ini, masukkan ke TTD
                                                    if ($d <= $ttdDay) {
                                                        $rowTtdPlanQty += $data['qty'];
                                                        $rowTtdPlanRp += $data['rp'];
                                                        $grandTtdPlanQty += $data['qty'];
                                                        $grandTtdPlanRp += $data['rp'];
                                                    }
                                                @endphp
                                                <td
                                                    class="text-center {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                                    <span
                                                        class="mode-unit">{{ $data['qty'] > 0 ? number_format($data['qty'], 1) : '' }}</span>
                                                    <span
                                                        class="mode-rp d-none">{{ $data['rp'] > 0 ? number_format($data['rp'] / 1000, 0, ',', '.') : '' }}</span>
                                                </td>
                                            @endfor

                                            <td class="bg-info-subtle fw-bold text-primary text-center">
                                                <span
                                                    class="mode-unit">{{ $rowTtdPlanQty > 0 ? number_format($rowTtdPlanQty, 1) : '-' }}</span>
                                                <span
                                                    class="mode-rp d-none">{{ $rowTtdPlanRp > 0 ? number_format($rowTtdPlanRp / 1000, 0, ',', '.') : '-' }}</span>
                                            </td>

                                            <td class="bg-warning-subtle fw-bold text-success text-center">
                                                <span
                                                    class="mode-unit">{{ $rowPlanQty > 0 ? number_format($rowPlanQty, 1) : '-' }}</span>
                                                <span
                                                    class="mode-rp d-none">{{ $rowPlanRp > 0 ? number_format($rowPlanRp / 1000, 0, ',', '.') : '-' }}</span>
                                            </td>
                                        </tr>

                                        <tr class="row-actual">
                                            <td class="fw-bold text-primary">Act</td>
                                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                                @php
                                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                                    $data = $report['actual'][$job['id']][$dateStr] ?? [
                                                        'qty' => 0,
                                                        'rp' => 0,
                                                    ];

                                                    $rowActualQty += $data['qty'];
                                                    $rowActualRp += $data['rp'];
                                                    $dailyTotals[$d]['actual_qty'] += $data['qty'];
                                                    $dailyTotals[$d]['actual_rp'] += $data['rp'];
                                                    $grandActualQty += $data['qty'];
                                                    $grandActualRp += $data['rp'];

                                                    // Jika tanggal <= Hari Ini, masukkan ke TTD
                                                    if ($d <= $ttdDay) {
                                                        $rowTtdActualQty += $data['qty'];
                                                        $rowTtdActualRp += $data['rp'];
                                                        $grandTtdActualQty += $data['qty'];
                                                        $grandTtdActualRp += $data['rp'];
                                                    }
                                                @endphp
                                                <td
                                                    class="text-center {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                                    <span
                                                        class="mode-unit">{{ $data['qty'] > 0 ? number_format($data['qty'], 1) : '' }}</span>
                                                    <span
                                                        class="mode-rp d-none">{{ $data['rp'] > 0 ? number_format($data['rp'] / 1000, 0, ',', '.') : '' }}</span>
                                                </td>
                                            @endfor

                                            <td class="bg-info-subtle fw-bold text-primary text-center">
                                                <span
                                                    class="mode-unit">{{ $rowTtdActualQty > 0 ? number_format($rowTtdActualQty, 1) : '-' }}</span>
                                                <span
                                                    class="mode-rp d-none">{{ $rowTtdActualRp > 0 ? number_format($rowTtdActualRp / 1000, 0, ',', '.') : '-' }}</span>
                                            </td>

                                            <td class="bg-warning-subtle fw-bold text-primary text-center">
                                                <span
                                                    class="mode-unit">{{ $rowActualQty > 0 ? number_format($rowActualQty, 1) : '-' }}</span>
                                                <span
                                                    class="mode-rp d-none">{{ $rowActualRp > 0 ? number_format($rowActualRp / 1000, 0, ',', '.') : '-' }}</span>
                                            </td>
                                        </tr>

                                        <tr class="row-persen">
                                            <td class="text-muted fw-bold">%</td>
                                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                                @php
                                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                                    $p = $report['plan'][$job['id']][$dateStr]['qty'] ?? 0;
                                                    $a = $report['actual'][$job['id']][$dateStr]['qty'] ?? 0;

                                                    $percent = '';
                                                    $style = 'color: #6c757d;';
                                                    if ($p > 0) {
                                                        $percent = round(($a / $p) * 100) . '%';
                                                        if ($a > $p) {
                                                            $style = 'color: #dc3545; font-weight: bold;';
                                                        }
                                                    } elseif ($a > 0) {
                                                        $percent = '100%';
                                                        $style = 'color: #dc3545; font-weight: bold;';
                                                    }
                                                @endphp
                                                <td class="text-center {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}"
                                                    style="{{ $style }}">{{ $percent }}</td>
                                            @endfor

                                            @php
                                                $rowTtdPercent = '';
                                                $rowTtdStyle = 'color: #6c757d;';
                                                if ($rowTtdPlanQty > 0) {
                                                    $rowTtdPercent =
                                                        round(($rowTtdActualQty / $rowTtdPlanQty) * 100) . '%';
                                                    if ($rowTtdActualQty > $rowTtdPlanQty) {
                                                        $rowTtdStyle = 'color: #dc3545; font-weight: bold;';
                                                    }
                                                } elseif ($rowTtdActualQty > 0) {
                                                    $rowTtdPercent = '100%';
                                                    $rowTtdStyle = 'color: #dc3545; font-weight: bold;';
                                                }
                                            @endphp
                                            <td class="bg-info-subtle text-center" style="{{ $rowTtdStyle }}">
                                                <span class="mode-unit">{{ $rowTtdPercent }}</span>
                                                <span class="mode-rp d-none">{{ $rowTtdPercent }}</span>
                                            </td>

                                            @php
                                                $rowPercent = '';
                                                $rowStyle = 'color: #6c757d;';
                                                if ($rowPlanQty > 0) {
                                                    $rowPercent = round(($rowActualQty / $rowPlanQty) * 100) . '%';
                                                    if ($rowActualQty > $rowPlanQty) {
                                                        $rowStyle = 'color: #dc3545; font-weight: bold;';
                                                    }
                                                } elseif ($rowActualQty > 0) {
                                                    $rowPercent = '100%';
                                                    $rowStyle = 'color: #dc3545; font-weight: bold;';
                                                }
                                            @endphp
                                            <td class="bg-warning-subtle text-center" style="{{ $rowStyle }}">
                                                <span class="mode-unit">{{ $rowPercent }}</span>
                                                <span class="mode-rp d-none">{{ $rowPercent }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>

                            <tfoot class="fw-bold sticky-bottom shadow-sm" style="z-index: 10;">
                                <tr class="bg-success-subtle text-dark">
                                    <td colspan="3" rowspan="3"
                                        class="text-end align-middle pe-3 bg-light text-secondary border-top-0">GRAND TOTAL
                                    </td>
                                    <td class="text-success">Plan</td>
                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        <td
                                            class="text-center {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                            <span
                                                class="mode-unit">{{ $dailyTotals[$d]['plan_qty'] > 0 ? number_format($dailyTotals[$d]['plan_qty'], 1) : '-' }}</span>
                                            <span
                                                class="mode-rp d-none">{{ $dailyTotals[$d]['plan_rp'] > 0 ? number_format($dailyTotals[$d]['plan_rp'] / 1000, 0, ',', '.') : '-' }}</span>
                                        </td>
                                    @endfor

                                    <td class="text-center bg-info-subtle text-dark border-start border-info">
                                        <span class="mode-unit">{{ number_format($grandTtdPlanQty, 1) }}</span>
                                        <span
                                            class="mode-rp d-none">{{ number_format($grandTtdPlanRp / 1000, 0, ',', '.') }}</span>
                                    </td>

                                    <td class="text-center bg-warning-subtle text-dark border-start border-warning">
                                        <span class="mode-unit">{{ number_format($grandPlanQty, 1) }}</span>
                                        <span
                                            class="mode-rp d-none">{{ number_format($grandPlanRp / 1000, 0, ',', '.') }}</span>
                                    </td>
                                </tr>

                                <tr class="bg-primary-subtle text-dark">
                                    <td class="text-primary">Act</td>
                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        <td
                                            class="text-center {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}">
                                            <span
                                                class="mode-unit">{{ $dailyTotals[$d]['actual_qty'] > 0 ? number_format($dailyTotals[$d]['actual_qty'], 1) : '-' }}</span>
                                            <span
                                                class="mode-rp d-none">{{ $dailyTotals[$d]['actual_rp'] > 0 ? number_format($dailyTotals[$d]['actual_rp'] / 1000, 0, ',', '.') : '-' }}</span>
                                        </td>
                                    @endfor

                                    <td class="text-center bg-info-subtle text-dark border-start border-info">
                                        <span class="mode-unit">{{ number_format($grandTtdActualQty, 1) }}</span>
                                        <span
                                            class="mode-rp d-none">{{ number_format($grandTtdActualRp / 1000, 0, ',', '.') }}</span>
                                    </td>

                                    <td class="text-center bg-warning-subtle text-dark border-start border-warning">
                                        <span class="mode-unit">{{ number_format($grandActualQty, 1) }}</span>
                                        <span
                                            class="mode-rp d-none">{{ number_format($grandActualRp / 1000, 0, ',', '.') }}</span>
                                    </td>
                                </tr>

                                <tr class="bg-light text-secondary border-bottom-0">
                                    <td class="text-muted">%</td>
                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $gtP = $dailyTotals[$d]['plan_qty'];
                                            $gtA = $dailyTotals[$d]['actual_qty'];
                                            $gtPercent = '';
                                            $gtStyle = '';
                                            if ($gtP > 0) {
                                                $gtPercent = round(($gtA / $gtP) * 100) . '%';
                                                if ($gtA > $gtP) {
                                                    $gtStyle = 'color: #dc3545;';
                                                }
                                            } elseif ($gtA > 0) {
                                                $gtPercent = '100%';
                                                $gtStyle = 'color: #dc3545;';
                                            }
                                        @endphp
                                        <td class="text-center {{ $d == $ttdDay ? 'border-end border-info border-2' : '' }}"
                                            style="{{ $gtStyle }}">{{ $gtPercent }}</td>
                                    @endfor

                                    @php
                                        $finalTtdPercent = '';
                                        $finalTtdStyle = '';
                                        if ($grandTtdPlanQty > 0) {
                                            $finalTtdPercent =
                                                round(($grandTtdActualQty / $grandTtdPlanQty) * 100) . '%';
                                            if ($grandTtdActualQty > $grandTtdPlanQty) {
                                                $finalTtdStyle = 'color: #dc3545;';
                                            }
                                        } elseif ($grandTtdActualQty > 0) {
                                            $finalTtdPercent = '100%';
                                            $finalTtdStyle = 'color: #dc3545;';
                                        }
                                    @endphp
                                    <td class="text-center bg-info-subtle text-dark border-start border-info"
                                        style="{{ $finalTtdStyle }}">
                                        {{ $finalTtdPercent }}
                                    </td>

                                    @php
                                        $finalPercent = '';
                                        $finalStyle = '';
                                        if ($grandPlanQty > 0) {
                                            $finalPercent = round(($grandActualQty / $grandPlanQty) * 100) . '%';
                                            if ($grandActualQty > $grandPlanQty) {
                                                $finalStyle = 'color: #dc3545;';
                                            }
                                        } elseif ($grandActualQty > 0) {
                                            $finalPercent = '100%';
                                            $finalStyle = 'color: #dc3545;';
                                        }
                                    @endphp
                                    <td class="text-center bg-warning-subtle text-dark border-start border-warning"
                                        style="{{ $finalStyle }}">
                                        {{ $finalPercent }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function toggleRupiah() {
            const btn = document.getElementById('btnToggleRp');
            const btnText = document.getElementById('btnText');
            const units = document.querySelectorAll('.mode-unit');
            const rps = document.querySelectorAll('.mode-rp');
            const isShowingRp = btn.classList.contains('btn-rp-active');

            if (!isShowingRp) {
                btn.classList.add('btn-rp-active');
                btn.classList.remove('btn-outline-success');
                btnText.innerText = "Tampilkan Unit (HK)";
                units.forEach(el => el.classList.add('d-none'));
                rps.forEach(el => el.classList.remove('d-none'));
            } else {
                btn.classList.remove('btn-rp-active');
                btn.classList.add('btn-outline-success');
                btnText.innerText = "Tampilkan Rupiah";
                units.forEach(el => el.classList.remove('d-none'));
                rps.forEach(el => el.classList.add('d-none'));
            }
        }
    </script>
@endsection
