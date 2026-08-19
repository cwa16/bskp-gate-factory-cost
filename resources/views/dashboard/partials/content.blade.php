<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <p class="text-muted small fw-bold mb-0 text-uppercase">Actual Cost</p>
                        <h4 class="fw-bold mb-0">Rp {{ number_format($data['actual']->total_cost ?? 0, 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="icon-box" style="background-color: {{ $color }}15; color: {{ $color }};">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>

                @php
                    $budgetCost = $data['plan']->total_cost > 0 ? $data['plan']->total_cost : 1;
                    $costPersen = ($data['actual']->total_cost / $budgetCost) * 100;
                @endphp
                <div class="progress mb-2" style="height: 5px;">
                    <div class="progress-bar"
                        style="width: {{ min($costPersen, 100) }}%; background-color: {{ $color }};"></div>
                </div>
                <small class="text-muted fw-bold">Budget Plan: Rp {{ number_format($budgetCost, 0, ',', '.') }}</small>

                <div class="mt-3">
                    <p class="text-muted mb-1" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">COST
                        PER AREA (ACTUAL / PLAN)</p>

                    @php
                        $costAreaStats = [];

                        // Fungsi agar angka jutaan/ribuan tidak merusak tabel UI
                        $formatRp = function ($angka) {
                            if ($angka >= 1000000) {
                                return number_format($angka / 1000000, 1, ',', '.') . ' Jt';
                            }
                            if ($angka >= 1000) {
                                return number_format($angka / 1000, 0, ',', '.') . ' Rb';
                            }
                            return number_format($angka, 0, ',', '.');
                        };

                        if (isset($data['chartArea']['labels']) && count($data['chartArea']['labels']) > 0) {
                            // Total HK dipakai sebagai pembagi proporsional
                            $totalHkActChart = array_sum($data['chartArea']['act']);
                            $totalHkPlanChart = array_sum($data['chartArea']['plan']);

                            foreach ($data['chartArea']['labels'] as $idx => $label) {
                                $actHk = $data['chartArea']['act'][$idx] ?? 0;
                                $planHk = $data['chartArea']['plan'][$idx] ?? 0;

                                // Pecah Rupiah berdasarkan porsi HK di area tersebut
                                $actCostArea =
                                    $totalHkActChart > 0
                                        ? ($actHk / $totalHkActChart) * $data['actual']->total_cost
                                        : 0;
                                $planCostArea =
                                    $totalHkPlanChart > 0
                                        ? ($planHk / $totalHkPlanChart) * ($data['plan']->total_cost ?? 0)
                                        : 0;

                                $costAreaStats[] = [
                                    'label' => $label,
                                    'actCost' => $actCostArea,
                                    'planCost' => $planCostArea,
                                ];
                            }

                            // Custom Sorting (Process -> Milling -> SH -> dll)
                            usort($costAreaStats, function ($a, $b) {
                                $getWeight = function ($name) {
                                    if (stripos($name, 'Process') !== false || stripos($name, 'Proses') !== false) {
                                        return 1;
                                    }
                                    if (stripos($name, 'Milling') !== false) {
                                        return 2;
                                    }
                                    if (stripos($name, 'SH') !== false || stripos($name, 'Unloading') !== false) {
                                        return 3;
                                    }
                                    if (stripos($name, 'Grading') !== false) {
                                        return 4;
                                    }
                                    return 5;
                                };

                                $weightA = $getWeight($a['label']);
                                $weightB = $getWeight($b['label']);

                                if ($weightA == $weightB) {
                                    return strcmp($a['label'], $b['label']);
                                }
                                return $weightA <=> $weightB;
                            });
                        }
                    @endphp

                    @if (count($costAreaStats) > 0)
                        @foreach ($costAreaStats as $stat)
                            <div
                                class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-light">
                                <span class="text-secondary fw-medium text-truncate"
                                    style="font-size: 12px; max-width: 45%;">{{ $stat['label'] }}</span>
                                <span class="fw-bold" style="font-size: 12px;">
                                    {{ $formatRp($stat['actCost']) }}
                                    <span class="text-muted fw-normal" style="font-size: 10px;">/
                                        {{ $formatRp($stat['planCost']) }}</span>
                                </span>
                            </div>
                        @endforeach
                    @else
                        <span class="text-muted small">Belum ada data area.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <p class="text-muted small fw-bold mb-0 text-uppercase">Kehadiran (HK)</p>
                        <h4 class="fw-bold mb-0">{{ number_format($data['actual']->total_hk ?? 0) }} <span
                                class="fs-6 text-muted">HK</span></h4>
                    </div>
                    <div class="icon-box" style="background-color: #ffc10720; color: #ffc107;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-muted mb-1" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">DETAIL
                        PER AREA (ACTUAL / PLAN)</p>

                    @php
                        $areaStats = [];
                        // 1. Kumpulkan data dari 3 array terpisah menjadi 1 array kesatuan
                        if (isset($data['chartArea']['labels']) && count($data['chartArea']['labels']) > 0) {
                            foreach ($data['chartArea']['labels'] as $idx => $label) {
                                $areaStats[] = [
                                    'label' => $label,
                                    'act' => $data['chartArea']['act'][$idx] ?? 0,
                                    'plan' => $data['chartArea']['plan'][$idx] ?? 0,
                                ];
                            }

                            // 2. Custom Sorting berdasarkan alur produksi pabrik
                            usort($areaStats, function ($a, $b) {
                                $getWeight = function ($name) {
                                    if (stripos($name, 'Process') !== false || stripos($name, 'Proses') !== false) {
                                        return 1;
                                    }
                                    if (stripos($name, 'Milling') !== false) {
                                        return 2;
                                    }
                                    if (stripos($name, 'SH') !== false || stripos($name, 'Unloading') !== false) {
                                        return 3;
                                    }
                                    if (stripos($name, 'Grading') !== false) {
                                        return 4;
                                    }
                                    return 5; // Lain-lain
                                };

                                $weightA = $getWeight($a['label']);
                                $weightB = $getWeight($b['label']);

                                if ($weightA == $weightB) {
                                    return strcmp($a['label'], $b['label']);
                                }
                                return $weightA <=> $weightB;
                            });
                        }
                    @endphp

                    @if (count($areaStats) > 0)
                        @foreach ($areaStats as $stat)
                            <div
                                class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-light">
                                <span class="text-secondary fw-medium text-truncate"
                                    style="font-size: 12px; max-width: 60%;">{{ $stat['label'] }}</span>
                                <span class="fw-bold" style="font-size: 12px;">
                                    {{ number_format($stat['act'], 1) }}
                                    <span class="text-muted fw-normal" style="font-size: 10px;">/
                                        {{ number_format($stat['plan'], 1) }}</span>
                                </span>
                            </div>
                        @endforeach
                    @else
                        <span class="text-muted small">Belum ada data area.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <p class="text-muted small fw-bold mb-0 text-uppercase">Total Lembur</p>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($data['actual']->total_ot ?? 0, 1) }}
                            <span class="fs-6 text-muted">Jam</span>
                        </h4>
                    </div>
                    <div class="icon-box" style="background-color: #dc354515; color: #dc3545;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <small class="text-muted">Total dari revisi & fingerprint</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <p class="text-muted small fw-bold mb-0 text-uppercase">Sisa Budget Cost</p>
                        @php $var = ($data['plan']->total_cost ?? 0) - ($data['actual']->total_cost ?? 0); @endphp
                        <h5 class="fw-bold mb-0 {{ $var < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $var < 0 ? '-' : '' }} Rp {{ number_format(abs($var), 0, ',', '.') }}
                        </h5>
                    </div>
                    <div class="icon-box"
                        style="background-color: {{ $var < 0 ? '#dc3545' : '#198754' }}15; color: {{ $var < 0 ? '#dc3545' : '#198754' }};">
                        <i class="bi {{ $var < 0 ? 'bi-graph-down-arrow' : 'bi-graph-up-arrow' }}"></i>
                    </div>
                </div>
                <small class="text-muted">{{ $var < 0 ? 'Over Budget' : 'Saving (Under Budget)' }}</small>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

    });
</script>
