<div class="table-scroll">
    <table class="table table-bordered table-hover align-middle text-center table-sm subjob-table">

        <thead>
            <tr>
                <th rowspan="2" class="freeze-col freeze-0">No</th>
                <th rowspan="2" class="freeze-col freeze-1">Area</th>
                <th rowspan="2" class="freeze-col freeze-2">Sub Job</th>
                <th rowspan="2" class="freeze-col freeze-3">P / A</th>
                <th colspan="{{ $dates->count() }}">
                    {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                </th>
                <th rowspan="2" class="freeze-col freeze-total">TOTAL</th>
            </tr>
            <tr>
                @foreach ($dates as $date)
                    @php $holiday = isHoliday($date, $holidayMap); @endphp
                    <th class="date-col {{ $holiday ? 'holiday-col' : '' }}">
                        {{ $date->day }}
                    </th>
                @endforeach

            </tr>

        </thead>

        <tbody>
            @foreach ($subJobs as $job)
                {{-- PLAN (BUDGET) --}}
                <tr>
                    <td rowspan="3" class="freeze-col freeze-0">{{ $loop->iteration }}</td>
                    <td rowspan="3" class="freeze-col freeze-1">{{ $job->area }}</td>
                    <td rowspan="3" class="freeze-col freeze-2" style="background: {{ $job->color }}">
                        {{ $job->name }}
                    </td>
                    <td class="freeze-col freeze-3 fw-semibold">Plan</td>
                    @php $rowPlanTotal = 0; @endphp

                    @foreach ($dates as $date)
                        @php
                            $key = $job->code . '|' . $date->toDateString();

                            $val = $planQty[$key] ?? 0;
                            $rp = $planRp[$key] ?? 0;

                            $rowPlanTotal += $val;
                        @endphp

                        <td class="date-col {{ isHoliday($date, $holidayMap) ? 'holiday-col' : '' }}">
                            <span class="val-qty">{{ $val ?: '' }}</span>
                            <span class="val-rp d-none">
                                {{ $rp ? 'Rp ' . number_format($rp, 0, ',', '.') : '' }}
                            </span>
                        </td>
                    @endforeach


                    @php
                        $rowPlanRp = 0;
                        foreach ($dates as $d) {
                            $rowPlanRp += $planRp[$job->code . '|' . $d->toDateString()] ?? 0;
                        }
                    @endphp

                    <td class="fw-bold">
                        <span class="val-qty">{{ number_format($rowPlanTotal) ?: '' }}</span>
                        <span class="val-rp d-none">
                            {{ $rowPlanRp ? 'Rp ' . number_format($rowPlanRp, 0, ',', '.') : '' }}
                        </span>
                    </td>


                </tr>

                {{-- ACTUAL --}}
                @php $rowActualTotal = 0; @endphp
                <tr>
                    <td class="freeze-col freeze-3 fw-semibold">Actual</td>

                    @foreach ($dates as $date)
                        @php
                            $key = $job->code . '|' . $date->toDateString();

                            $qty = $actualQty[$key] ?? 0;
                            $rp = $actualRp[$key] ?? 0;

                            $rowActualTotal += $qty;
                        @endphp

                        <td class="date-col {{ isHoliday($date, $holidayMap) ? 'holiday-col' : '' }}">
                            <span class="val-qty">{{ $qty ? number_format($qty, 1) : '' }}</span>
                            <span class="val-rp d-none">
                                {{ $rp ? 'Rp ' . number_format($rp, 0, ',', '.') : '' }}
                            </span>
                        </td>
                    @endforeach
                    @php
                        $rowActualRp = 0;
                        foreach ($dates as $d) {
                            $rowActualRp += $actualRp[$job->code . '|' . $d->toDateString()] ?? 0;
                        }
                    @endphp

                    <td class="fw-bold">
                        <span class="val-qty">{{ number_format($rowActualTotal) ?: '' }}</span>
                        <span class="val-rp d-none">
                            {{ $rowActualRp ? 'Rp ' . number_format($rowActualRp, 0, ',', '.') : '' }}
                        </span>
                    </td>

                </tr>


                {{-- % --}}
                {{-- PERCENTAGE --}}

                @php
                    $rowPercent = $rowPlanTotal > 0 ? round(($rowActualTotal / $rowPlanTotal) * 100) : null;
                @endphp
                <tr class="text-muted">
                    <td class="freeze-col freeze-3">%</td>

                    @foreach ($dates as $date)
                        @php
                            $planVal =
                                optional($plan->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                    ?->qty ?? 0;

                            $actualVal =
                                optional($actual->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                    ?->total ?? 0;

                            $percent = $planVal > 0 ? round(($actualVal / $planVal) * 100) : null;
                        @endphp

                        <td
                            class="date-col small fw-semibold {{ $percent > 100 ? 'text-danger' : ($percent >= 80 ? 'text-primary' : 'text-secondary') }}">
                            {{ $percent !== null ? $percent . '%' : '' }}
                        </td>
                    @endforeach
                    <td
                        class="date-col fw-bold  {{ $rowPercent >= 100 ? 'text-success' : ($rowPercent >= 80 ? 'text-warning' : 'text-danger') }}">
                        {{ $rowPercent !== null ? $rowPercent . '%' : '' }}
                    </td>
                </tr>
            @endforeach

            {{-- ================= TOTAL PLAN ================= --}}
            {{-- <tr class="fw-bold bg-light">
                <td colspan="3" class="freeze-col text-end pe-2">
                    TOTAL PLAN
                </td>
                <td class="freeze-col">Plan</td>

                @foreach ($dates as $date)
                    @php
                        $totalPlan = 0;
                        foreach ($subJobs as $job) {
                            $totalPlan +=
                                optional($plan->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                    ?->qty ?? 0;
                        }
                    @endphp
                    <td>{{ $totalPlan ?: '' }}</td>
                @endforeach

                @php
                    $rowPlanRp = 0;
                    foreach ($dates as $d) {
                        $rowPlanRp += $planRp[$job->code . '|' . $d->toDateString()] ?? 0;
                    }
                @endphp

                <td class="fw-bold">
                    <span class="val-qty">{{ $rowPlanTotal ?: '' }}</span>
                    <span class="val-rp d-none">
                        {{ $rowPlanRp ? 'Rp ' . number_format($rowPlanRp, 0, ',', '.') : '' }}
                    </span>
                </td>


            </tr> --}}

            {{-- ================= TOTAL ACTUAL ================= --}}
            {{-- <tr class="fw-bold bg-light">
                <td colspan="3" class="freeze-col text-end pe-2">
                    TOTAL ACTUAL
                </td>
                <td class="freeze-col">Actual</td>

                @foreach ($dates as $date)
                    @php
                        $totalActual = 0;
                        foreach ($subJobs as $job) {
                            $totalActual +=
                                optional($actual->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                    ?->total ?? 0;
                        }
                    @endphp
                    <td>{{ $totalActual ?: '' }}</td>
                @endforeach
                <td class="fw-bold">
                    {{ collect($subJobs)->sum(function ($job) use ($actual) {
                        return optional($actual->get($job->code))->sum('total');
                    }) }}
                </td>

            </tr> --}}
            {{-- ================= TOTAL PERCENTAGE ================= --}}
            {{-- <tr class="fw-bold bg-secondary-subtle">
                <td colspan="3" class="freeze-col text-end pe-2">
                    TOTAL %
                </td>
                <td class="freeze-col">%</td>

                @foreach ($dates as $date)
                    @php
                        $totalPlan = 0;
                        $totalActual = 0;

                        foreach ($subJobs as $job) {
                            $totalPlan +=
                                optional($plan->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                    ?->qty ?? 0;

                            $totalActual +=
                                optional($actual->get($job->code))?->firstWhere('work_date', $date->toDateString())
                                    ?->total ?? 0;
                        }

                        $totalPercent = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100) : null;
                    @endphp

                    <td
                        class=" {{ $totalPercent >= 100 ? 'text-success' : ($totalPercent >= 80 ? 'text-warning' : 'text-danger') }}">
                        {{ $totalPercent !== null ? $totalPercent . '%' : '' }}
                    </td>
                @endforeach

                @php
                    $grandPlan = collect($subJobs)->sum(fn($job) => optional($plan->get($job->code))->sum('qty'));

                    $grandActual = collect($subJobs)->sum(fn($job) => optional($actual->get($job->code))->sum('total'));

                    $grandPercent = $grandPlan > 0 ? round(($grandActual / $grandPlan) * 100) : null;
                @endphp

                <td
                    class="fw-bold {{ $grandPercent >= 100 ? 'text-success' : ($grandPercent >= 80 ? 'text-warning' : 'text-danger') }}">
                    {{ $grandPercent !== null ? $grandPercent . '%' : '' }}
                </td>

            </tr> --}}


        </tbody>

    </table>
</div>
