@extends('layouts.app')

@section('title', 'Wages Summary')
@section('page-title', 'Wages Summary')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table.dataTable {
            font-size: 12px;
            border-collapse: collapse !important;
        }

        .table thead {
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
        }

        .table th,
        .table td {
            padding: 6px 10px !important;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
        }

        .table tbody tr:hover {
            background: #f1f5f9;
        }

        .fw-bold {
            color: #0f172a;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark m-0">Wages Summary - {{ \Carbon\Carbon::create(null, $month)->format('F Y') }}
                </h5>
                <form action="{{ route('subjob.summary') }}" method="GET" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="form-select form-select-sm">
                        @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Tampilkan</button>
                    <a href="{{ route('subjob.wages.export', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-sm btn-success px-3">Export</a>
                </form>
            </div>

            <table class="table table-hover w-100" id="wagesTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>HK</th>
                        <th>Ori (jam)</th>
                        <th>Final (jam)</th>
                        <th>Rp (OT)</th>
                        <th>Gaji</th>
                        <th>Total Gaji + OT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $i => $emp)
                        @php $tot = $employeeTotals[$emp->nik]; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $emp->nik }}</td>
                            <td class="fw-medium">{{ $emp->name }}</td>
                            <td class="text-center">{{ $tot['total_hk'] }}</td>
                            <td class="text-center">{{ $tot['total_ot'] }}</td>
                            <td class="text-center">{{ $tot['total_ot_final'] }}</td>
                            <td class="text-end">{{ number_format($tot['total_ot_rp'], 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($tot['total_hk_rp'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($tot['total_rp'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#wagesTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [10, 25, 50, 100],
                "language": {
                    "search": "Cari Karyawan:"
                }
            });
        });
    </script>
@endsection
