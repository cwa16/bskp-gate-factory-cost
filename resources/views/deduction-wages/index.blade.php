@extends('layouts.app')

@section('title', 'Input Potongan Gaji')
@section('page-title', 'Input Potongan Gaji')

<style>
    /* STRUKTUR & CONTAINER */
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

    /* TABEL EXCEL STYLE */
    .ts-table {
        font-size: 11px;
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
        padding: 0;
        vertical-align: middle;
        box-sizing: border-box;
        background-color: #fff;
    }

    /* Header Styling */
    .bg-header {
        background-color: #f8fafc !important;
        color: #0f172a;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px !important;
    }

    /* ENGINE FREEZE PANE */
    .sticky-left {
        position: sticky;
        z-index: 10;
        background-color: #fff;
        padding: 6px 10px !important;
    }

    .super-sticky {
        position: sticky;
        top: 0;
        z-index: 30 !important;
        background-color: #f8fafc !important;
    }

    .sticky-top-th {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    /* Bayangan Pembatas Kiri */
    .col-jabatan {
        box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.15);
        border-right: 2px solid #cbd5e1 !important;
    }

    .super-sticky.col-jabatan {
        box-shadow: 2px 2px 5px -2px rgba(0, 0, 0, 0.15) !important;
    }

    /* Koordinat X Axis */
    .col-no {
        left: 0px;
        width: 40px;
        min-width: 40px;
    }

    .col-name {
        left: 40px;
        width: 220px;
        min-width: 220px;
        text-align: left !important;
    }

    .col-jabatan {
        left: 260px;
        width: 120px;
        min-width: 120px;
    }

    /* Input Excel Style Modern */
    .excel-input {
        width: 100%;
        height: 100%;
        min-height: 35px;
        border: none;
        text-align: right;
        padding: 5px 10px;
        font-weight: 600;
        color: #0f172a;
        font-size: 11.5px;
        transition: 0.2s;
    }

    .excel-input:hover {
        background-color: #f1f5f9;
    }

    .excel-input:focus {
        outline: none;
        background-color: #e0f2fe;
        box-shadow: inset 0 0 0 2px #0ea5e9;
    }

    .excel-input::-webkit-outer-spin-button,
    .excel-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

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
            background-color: #fff;
            color: #0f172a;
        }
    }
</style>

@section('content')

    <div class="container-fluid py-3">
        <div class="filter-box d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-secondary">
                <i class="bi bi-cash-coin me-1"></i> Input Potongan Karyawan:
                <span class="text-primary">{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</span>
            </h5>
            <div>
                <form action="{{ route('deduction.index') }}" method="GET" class="d-flex gap-2 mb-0">
                    <select name="month" class="form-select form-select-sm w-auto border-light" style="font-size: 11px;">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                    <select name="year" class="form-select form-select-sm w-auto border-light" style="font-size: 11px;">
                        @php $currentYear = date('Y'); @endphp
                        @for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold"
                        style="font-size: 11px;">Tampilkan</button>
                </form>
            </div>
        </div>

        <div class="table-responsive-freeze show-shadow">
            <table class="table ts-table mb-0">
                <thead>
                    <tr>
                        <th class="super-sticky col-no bg-header">No</th>
                        <th class="super-sticky col-name bg-header">Nama Karyawan</th>
                        <th class="super-sticky col-jabatan bg-header">Posisi</th>

                        <th class="sticky-top-th bg-header" style="min-width: 120px;">SPSI (Rp)</th>
                        <th class="sticky-top-th bg-header" style="min-width: 120px;">Astek (Rp)</th>
                        <th class="sticky-top-th bg-header" style="min-width: 120px;">Listrik (Rp)</th>
                        <th class="sticky-top-th bg-header" style="min-width: 120px;">Kantin (Rp)</th>
                        <th class="sticky-top-th bg-header" style="min-width: 120px;">Spd Motor (Rp)</th>
                        <th class="sticky-top-th bg-header" style="min-width: 120px;">Bank (Rp)</th>
                        <th class="sticky-top-th bg-header" style="min-width: 120px;">Lain-Lain (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $index => $emp)
                        @php
                            // Ambil data potongan jika sudah ada, jika tidak default 0 / kosong
                            $data = $deductions[$emp->nik] ?? null;
                        @endphp
                        <tr>
                            <td class="sticky-left col-no text-muted">{{ $index + 1 }}</td>
                            <td class="sticky-left col-name">
                                <div class="fw-bold text-dark" style="font-size: 12px;">{{ $emp->name }}</div>
                                <div class="text-muted" style="font-size: 10px; margin-top: 1px;">{{ $emp->nik }} -
                                    {{ $emp->status }}</div>
                            </td>
                            <td class="sticky-left col-jabatan text-muted">{{ $emp->jabatan }}</td>

                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->spsi > 0 ? $data->spsi : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'spsi')">
                            </td>
                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->astek > 0 ? $data->astek : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'astek')">
                            </td>
                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->listrik > 0 ? $data->listrik : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'listrik')">
                            </td>
                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->kantin > 0 ? $data->kantin : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'kantin')">
                            </td>
                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->spd_motor > 0 ? $data->spd_motor : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'spd_motor')">
                            </td>
                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->bank > 0 ? $data->bank : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'bank')">
                            </td>
                            <td>
                                <input type="number" class="excel-input" placeholder="0"
                                    value="{{ $data && $data->other > 0 ? $data->other : '' }}"
                                    onchange="saveDeduction(this, '{{ $emp->nik }}', 'other')">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function saveDeduction(inputElement, nik, field) {
            let val = parseFloat(inputElement.value);
            if (isNaN(val)) val = 0;

            const period = "{{ $period }}"; // Contoh: "2026-05"

            fetch("{{ route('deduction.update') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        nik: nik,
                        month: period,
                        field: field,
                        value: val
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Animasi Flash Warna Hijau saat Berhasil
                        inputElement.classList.remove('save-success'); // Reset animasi
                        void inputElement.offsetWidth; // Trigger DOM reflow
                        inputElement.classList.add('save-success');
                    } else {
                        alert('Gagal menyimpan data!');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan koneksi!');
                });
        }
    </script>
@endsection
