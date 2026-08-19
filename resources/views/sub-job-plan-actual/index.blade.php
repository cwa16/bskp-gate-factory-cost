@extends('layouts.app')

@section('title', 'Plan vs Actual Sub Job')
@section('page-title', 'Plan vs Actual Sub Job')

@section('content')

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-2">
            <label class="form-label small">Bulan</label>
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary w-100">Tampilkan</button>
        </div>
    </form>

    <div class="table-freeze">
        <table class="table table-bordered table-hover align-middle text-center subjob-table table-sm" style="font-size: 0.85rem;">

            <thead>
                <tr>
                    <th rowspan="2" class="freeze-col freeze-0">No</th>
                    <th rowspan="2" class="freeze-col freeze-1">Area</th>
                    <th rowspan="2" class="freeze-col freeze-2">Sub Job</th>
                    <th rowspan="2" class="freeze-col freeze-3">P / A</th>
                    <th colspan="{{ $dates->count() }}">
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
                    {{-- PLAN --}}
                    <tr>
                        <td rowspan="2" class="freeze-col freeze-0">{{ $loop->iteration }}</td>
                        <td rowspan="2" class="freeze-col freeze-1">{{ $job->area }}</td>
                        <td rowspan="2" class="freeze-col freeze-2" style="background: {{ $job->color }}">
                            {{ $job->name }}
                        </td>
                        <td class="freeze-col freeze-3 fw-semibold">Plan</td>

                        @foreach ($dates as $date)
                            @php
                                $val =
                                    optional($plan->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                        ?->total ?? 0;
                            @endphp
                            <td>{{ $val ?: '' }}</td>
                        @endforeach
                    </tr>

                    {{-- ACTUAL --}}
                    <tr>
                        <td class="freeze-col freeze-3 fw-semibold">Actual</td>

                        @foreach ($dates as $date)
                            @php
                                $val =
                                    optional($actual->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                        ?->total ?? 0;
                            @endphp
                            <td>{{ $val ?: '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

@endsection
