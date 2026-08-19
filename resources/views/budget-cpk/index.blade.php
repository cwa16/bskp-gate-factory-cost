@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0">Input Budget Cost per Kg (Harian)</h4>
                <p class="text-muted small m-0 mt-1">Isi target budget per tanggal. Kosongkan jika tidak ada target.</p>
            </div>

            <form action="{{ route('budget-cpk.index') }}" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select fw-medium shadow-sm">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="form-select fw-medium shadow-sm">
                    @php $crYear = date('Y'); @endphp
                    @for ($y = $crYear - 1; $y <= $crYear + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary shadow-sm px-4">Tampilkan</button>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('budget-cpk.store') }}" method="POST">
            @csrf
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary"><i class="bi bi-grid-3x3 me-2"></i>Spreadsheet Editor</span>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="fillDown()">
                            <i class="bi bi-arrow-down-square me-1"></i>Salin Tgl 1 ke Bawah
                        </button>
                        <button type="submit" class="btn btn-success btn-sm fw-bold">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-bordered table-hover mb-0 text-center align-middle"
                        style="font-variant-numeric: tabular-nums;">
                        <thead class="bg-light sticky-top" style="z-index: 10;">
                            <tr>
                                <th width="10%">Tanggal</th>
                                @foreach ($areas as $area)
                                    <th>{{ $area }} <br><small class="text-muted fw-normal">(Rp/Kg)</small></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                    $isWeekend = \Carbon\Carbon::parse($dateStr)->isWeekend();
                                @endphp
                                <tr style="{{ $isWeekend ? 'background-color: #fdf2f2;' : '' }}">
                                    <td class="fw-bold {{ $isWeekend ? 'text-danger' : 'text-secondary' }}">
                                        {{ $d }} <br>
                                        <small
                                            class="fw-normal opacity-75">{{ \Carbon\Carbon::parse($dateStr)->translatedFormat('D') }}</small>
                                    </td>
                                    @foreach ($areas as $area)
                                        @php
                                            // Tarik value jika ada di database
                                            $val = $budgetMap[$dateStr][$area] ?? '';
                                        @endphp
                                        <td class="p-1">
                                            <input type="number" step="0.01"
                                                name="budgets[{{ $dateStr }}][{{ $area }}]"
                                                class="form-control form-control-sm text-center border-0 bg-transparent input-budget"
                                                data-area="{{ $area }}" value="{{ $val > 0 ? (float) $val : '' }}"
                                                placeholder="-">
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <script>
        function fillDown() {
            if (!confirm('Angka di Tanggal 1 akan disalin ke seluruh tanggal di bawahnya. Lanjutkan?')) return;

            let areas = @json($areas);

            areas.forEach(function(area) {
                // Ambil input pertama (Tanggal 1) untuk area ini
                let inputs = document.querySelectorAll(`input[data-area="${area}"]`);
                if (inputs.length > 0) {
                    let valToCopy = inputs[0].value;
                    // Salin ke input 2 sampai 31
                    for (let i = 1; i < inputs.length; i++) {
                        inputs[i].value = valToCopy;
                    }
                }
            });
        }
    </script>
@endsection
