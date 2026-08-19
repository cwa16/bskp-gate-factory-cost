@extends('layouts.app')

@section('title', 'Rekap Sub Job')
@section('page-title', 'Rekap Sub Job Harian')

<style>
    /* No */
    .freeze-0 {
        width: 42px;
        min-width: 42px;
        max-width: 42px;
        text-align: center;
    }

    /* Area */
    .freeze-1 {
        width: 110px;
        min-width: 110px;
        max-width: 110px;
    }

    /* Sub Job */
    .freeze-2 {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
    }


    .freeze-col {
        position: sticky;
        background: #fff;
        z-index: 5;
    }

    .subjob-table thead .freeze-col {
        z-index: 10;
    }

    .subjob-table {
        font-size: 11px;
    }

    .subjob-table th,
    .subjob-table td {
        padding: 3px 4px;
        line-height: 1.1;
    }

    .subjob-table thead th {
        font-size: 10px;
        font-weight: 600;
        padding: 4px 3px;
        white-space: nowrap;
    }

    .subjob-table tbody tr {
        height: 26px;
    }

    .subjob-table {
        font-size: 10px;
    }

    .subjob-table tbody tr {
        height: 24px;
    }
</style>

@section('content')

    {{-- FILTER BULAN --}}
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-2">
            <label class="form-label small">Bulan</label>
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary w-100">
                Tampilkan
            </button>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="table-freeze">
        <table class="table table-bordered table-hover align-middle text-center subjob-table">

            <thead>
                <tr>
                    <th class="freeze-col freeze-0" rowspan="2">No</th>
                    <th class="freeze-col freeze-1" rowspan="2">Area</th>
                    <th class="freeze-col freeze-1" rowspan="2">Sub Job</th>
                    <th colspan="{{ count($dates) }}">
                        {{ \Carbon\Carbon::parse($month)->format('F Y') }}
                    </th>
                </tr>
                <tr>
                    @foreach ($dates as $date)
                        <th>{{ $date->day }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($subJobs as $job)
                    <tr>
                        <td class="freeze-col freeze-0">{{ $loop->iteration }}</td>
                        <td class="freeze-col freeze-1 text-start fw-semibold">
                            {{ $job->area }}
                        </td>

                        <td class="freeze-col freeze-1 text-start fw-semibold" style="background: {{ $job->color }}">
                            {{ $job->name }}
                        </td>

                        @foreach ($dates as $date)
                            @php
                                $count =
                                    optional($summary->get($job->code)?->firstWhere('work_date', $date->toDateString()))
                                        ->total ?? 0;
                            @endphp
                            <td>
                                {{ $count > 0 ? $count : '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

@endsection
