@extends('layouts.app')

@section('title', 'Input Aktual Borongan')
@section('page-title', 'Input Aktual Borongan (Piece Rate)')

<style>
    /* --- 1. WRAPPER & BASE TABLE --- */
    .table-freeze-wrapper {
        max-height: 70vh;
        overflow: auto;
        border: 1px solid #ced4da;
        border-radius: 6px 6px 0 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        background-color: #fff;
        position: relative;
    }

    .table-input {
        width: 100%;
        font-size: 11px;
        /* Disesuaikan agar muat banyak kolom */
        border-collapse: separate;
        border-spacing: 0;
        color: #212529;
        margin-bottom: 0;
    }

    /* --- 2. BORDERS & PADDING --- */
    .table-input th,
    .table-input td {
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
        text-align: center;
        padding: 0;
        height: 32px;
    }

    /* --- 3. STICKY HEADER --- */
    .table-input thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 700;
        padding: 8px 4px;
        text-transform: uppercase;
    }

    .sticky-th-top {
        position: sticky;
        top: 0;
        z-index: 50;
        border-bottom: 2px solid #adb5bd !important;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        background-color: #f8f9fa;
    }

    .sticky-th-date {
        position: sticky;
        top: 36px;
        z-index: 40;
        border-bottom: 2px solid #adb5bd !important;
        min-width: 35px;
        background-color: #f8f9fa;
    }

    /* --- 4. STICKY COLUMN (KIRI) --- */
    .sticky-corner {
        position: sticky;
        top: 0;
        left: 0;
        z-index: 60 !important;
        background-color: #e9ecef !important;
        border-right: 2px solid #6c757d !important;
        border-bottom: 2px solid #adb5bd !important;
    }

    .sticky-col-body {
        position: sticky;
        left: 0;
        z-index: 30;
        background-color: #fff;
        border-right: 2px solid #6c757d !important;
        padding: 0 10px !important;
        text-align: left;
        font-weight: 500;
        white-space: nowrap;
    }

    .col-no {
        width: 40px;
        min-width: 40px;
        text-align: center !important;
        left: 0;
    }

    .col-name {
        width: 200px;
        min-width: 200px;
        left: 40px;
    }

    /* --- 5. INPUT FIELD STYLE --- */
    .input-cell {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .borongan-input {
        width: 100%;
        height: 32px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 600;
        color: #0d6efd;
        transition: all 0.2s;
    }

    .borongan-input:focus {
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
        background-color: #e6f2ff;
        color: #000;
        border-radius: 2px;
    }

    .borongan-input::-webkit-outer-spin-button,
    .borongan-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* --- 6. HOVER, HIGHLIGHT & WAGES --- */
    .table-input tbody tr:hover td {
        background-color: #f8f9fa;
    }

    .table-input tbody tr:hover td.sticky-col-body {
        background-color: #f8f9fa;
    }

    .has-value {
        background-color: #d1e7dd !important;
    }

    /* Style Kolom Wages Mirip OT Management */
    .bg-wages-brutto {
        background-color: #c55a5a !important;
        color: #ffffff !important;
        font-weight: bold;
        padding: 0 8px !important;
    }

    .bg-wages-netto {
        background-color: #ffffff !important;
        font-weight: bold;
        color: #0f172a;
        padding: 0 8px !important;
    }

    /* Sticky Footer */
    .sticky-bottom-tf td {
        position: sticky;
        bottom: 0;
        z-index: 55;
        background-color: #f1f5f9 !important;
        border-top: 2px solid #94a3b8 !important;
        font-weight: bold;
        color: #0f172a;
        padding: 8px 10px !important;
    }
</style>

@section('content')
    <div class="container-fluid py-4">

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="fw-bold m-0 text-success d-flex align-items-center">
                    <i class="bi bi-boxes me-2 fs-4"></i> Data Input Borongan
                </h5>

                <form action="{{ route('borongan.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-success text-white fw-bold border-success">
                            <i class="bi bi-tag-fill me-1"></i> Pekerjaan
                        </span>
                        <select name="sub_job_id" class="form-select fw-bold border-success text-success"
                            onchange="this.form.submit()">
                            @foreach ($boronganJobs as $bj)
                                <option value="{{ $bj->id }}" {{ $subJobId == $bj->id ? 'selected' : '' }}>
                                    {{ strtoupper($bj->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-light fw-bold border-secondary">
                            <i class="bi bi-calendar-month me-1"></i> Periode
                        </span>
                        <select name="month" class="form-select border-secondary text-center fw-bold"
                            onchange="this.form.submit()">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="form-select border-secondary text-center fw-bold"
                            onchange="this.form.submit()">
                            @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if ($boronganJobs->isEmpty())
            <div class="alert alert-warning border-start border-4 border-warning fw-bold shadow-sm">
                <i class="bi bi-exclamation-triangle me-2"></i> Belum ada Master Sub Job dengan Payment System = 'borongan'.
            </div>
        @else
            <div class="table-freeze-wrapper">
                <table class="table-input">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sticky-corner col-no">No</th>
                            <th rowspan="2" class="sticky-corner col-name text-start ps-3">Nama Karyawan</th>

                            <th colspan="{{ $daysInMonth }}" class="sticky-th-top">
                                {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                            </th>

                            <th rowspan="2" class="sticky-th-top bg-header" style="min-width: 70px; z-index: 50;">TOTAL
                                PLAT</th>
                            <th rowspan="2" class="sticky-th-top bg-header" style="min-width: 110px; z-index: 50;">WAGES
                                BRUTTO</th>
                            <th rowspan="2" class="sticky-th-top bg-header" style="min-width: 110px; z-index: 50;">WAGES
                                NETTO</th>
                        </tr>
                        <tr>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <th class="sticky-th-date">{{ $d }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            @php
                                // --- KALKULASI TOTAL POTONGAN KARYAWAN ---
                                $ded = $deductionsMap[$user->nik] ?? null;
                                $spsi = $ded ? (float) $ded->spsi : 0;
                                $astek = $ded ? (float) $ded->astek : 0;
                                $listrik = $ded ? (float) $ded->listrik : 0;
                                $kantin = $ded ? (float) $ded->kantin : 0;
                                $spd_motor = $ded ? (float) $ded->spd_motor : 0;
                                $bank = $ded ? (float) $ded->bank : 0;
                                $other = $ded ? (float) $ded->other : 0;

                                $totalPotongan = $spsi + $astek + $listrik + $kantin + $spd_motor + $bank + $other;
                            @endphp

                            <tr class="row-emp" data-rate="{{ $rateDW }}" data-potongan="{{ $totalPotongan }}">
                                <td class="sticky-col-body col-no">{{ $index + 1 }}</td>
                                <td class="sticky-col-body col-name">{{ $user->name }}</td>

                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                        $qtyVal = $mapData[$user->nik][$d] ?? '';
                                    @endphp
                                    <td class="input-cell {{ $qtyVal > 0 ? 'has-value' : '' }}">
                                        <input type="number" class="borongan-input" value="{{ $qtyVal }}"
                                            data-nik="{{ $user->nik }}" data-date="{{ $dateStr }}"
                                            data-subjob="{{ $subJobId }}" min="0" placeholder="-">
                                    </td>
                                @endfor

                                <td class="fw-bold text-primary cell-total-qty fs-6">0</td>
                                <td class="bg-wages-brutto text-end pe-2 cell-brutto">Rp 0</td>
                                <td class="bg-wages-netto text-end pe-2 cell-netto">0</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="sticky-bottom-tf">
                            <td colspan="2" class="text-start text-uppercase px-3">Grand Total</td>
                            <td colspan="{{ $daysInMonth }}">
                                <span class="badge bg-secondary" style="font-size: 11px; padding: 5px 8px;">
                                    Rate DW: Rp {{ number_format($rateDW, 0, ',', '.') }} / Plat
                                </span>
                            </td>
                            <td id="footerTotalQty" class="text-primary text-center fs-6 fw-bold">0</td>
                            <td id="footerTotalBrutto" class="text-end pe-2 fw-bold"
                                style="background-color: #ffd8d8 !important; color: #000;">Rp 0</td>
                            <td id="footerTotalNetto" class="text-end pe-2 fw-bold"
                                style="background-color: #e2e8f0 !important; color: #000;">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        const SUBMIT_URL = "{{ route('borongan.store') }}";

        // Fungsi Kalkulator Live ala Excel
        function recalculateTable() {
            let grandQty = 0;
            let grandBrutto = 0;
            let grandNetto = 0;

            document.querySelectorAll('.row-emp').forEach(row => {
                let rowQty = 0;

                // Jumlahkan semua input di baris ini
                row.querySelectorAll('.borongan-input').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    rowQty += val;
                });

                // Ambil rate dari attribute HTML
                let rate = parseFloat(row.dataset.rate) || 0;
                let potongan = parseFloat(row.dataset.potongan) || 0; // Jika ada potongan

                let brutto = rowQty * rate;
                let netto = brutto - potongan;

                // Update text di kolom kanan
                row.querySelector('.cell-total-qty').innerText = rowQty > 0 ? rowQty : '';
                row.querySelector('.cell-brutto').innerText = brutto > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID')
                    .format(brutto) : 'Rp 0';
                row.querySelector('.cell-netto').innerText = netto > 0 ? new Intl.NumberFormat('id-ID').format(
                    netto) : '0';

                // Akumulasi ke Grand Total
                grandQty += rowQty;
                grandBrutto += brutto;
                grandNetto += netto;
            });

            // Update text di Footer Bawah
            document.getElementById('footerTotalQty').innerText = grandQty > 0 ? grandQty : '0';
            document.getElementById('footerTotalBrutto').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                grandBrutto);
            document.getElementById('footerTotalNetto').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                grandNetto);
        }

        // Jalankan kalkulator pertama kali saat halaman dimuat
        document.addEventListener('DOMContentLoaded', recalculateTable);

        document.querySelectorAll('.borongan-input').forEach(input => {
            // 1. Pilih teks otomatis saat klik
            input.addEventListener('focus', function() {
                this.select();
            });

            // 2. Kalkulasi REAL-TIME saat mengetik (sebelum di-save)
            input.addEventListener('input', function() {
                // Efek visual sel terisi
                if (this.value > 0) {
                    this.parentElement.classList.add('has-value');
                } else {
                    this.parentElement.classList.remove('has-value');
                }
                // Hitung ulang tabel
                recalculateTable();
            });

            // 3. Simpan Data via AJAX saat user memindahkan kursor / Selesai mengetik (onchange)
            input.addEventListener('change', function() {
                let el = this;
                fetch(SUBMIT_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nik: el.dataset.nik,
                        work_date: el.dataset.date,
                        sub_job: el.dataset.subjob,
                        qty: el.value
                    })
                }).then(res => res.json()).then(data => {
                    // Berhasil disimpan, tidak perlu alert agar tidak mengganggu kecepatan input user
                }).catch(err => {
                    alert('Gagal menyimpan data ke server!');
                    console.error(err);
                });
            });

            // 4. Navigasi pakai tombol Panah Keyboard Kiri/Kanan
            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowRight') {
                    let next = this.parentElement.nextElementSibling?.querySelector('input');
                    if (next) {
                        next.focus();
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowLeft') {
                    let prev = this.parentElement.previousElementSibling?.querySelector('input');
                    if (prev) {
                        prev.focus();
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
@endpush
