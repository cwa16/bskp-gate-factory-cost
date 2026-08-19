@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Input Budget Produksi
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success py-2">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('rss-budget.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bulan</label>
                                <select name="month" class="form-select" required>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tahun</label>
                                <select name="year" class="form-select" required>
                                    @php $crYear = date('Y'); @endphp
                                    @for ($y = $crYear - 1; $y <= $crYear + 2; $y++)
                                        <option value="{{ $y }}" {{ $crYear == $y ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Target Produksi RSS#1 (Kg)</label>
                                <input type="number" step="0.01" name="target_qty" class="form-control"
                                    placeholder="Contoh: 150000" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Budget</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold text-dark border-bottom">
                        <i class="bi bi-table me-2"></i>History Budget Produksi RSS#1
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0 text-center align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="10%">No</th>
                                        <th>Periode (Bulan - Tahun)</th>
                                        <th>Target Produksi (Kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($budgets as $index => $bg)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-bold text-secondary">
                                                {{ \Carbon\Carbon::create(null, $bg->month)->translatedFormat('F') }}
                                                {{ $bg->year }}
                                            </td>
                                            <td class="text-primary fw-bold">
                                                {{ number_format($bg->target_qty, 0, ',', '.') }} Kg
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted py-4">Belum ada data budget produksi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
