<div class="table-scroll mb-3">
    <table class="table table-bordered table-hover align-middle text-center table-sm subjob-table">

        <thead>
            <tr>
                <th rowspan="2" class="freeze-col freeze-0">No</th>
                <th rowspan="2" class="freeze-col freeze-1">Area</th>
                <th rowspan="2" class="freeze-col freeze-2">Sub Job</th>
                <th colspan="{{ $dates->count() }}">
                    {{ \Carbon\Carbon::create(request('year'), request('month'))->translatedFormat('F Y') }}
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
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $job->area }}</td>
                    <td style="background: {{ $job->color }}">
                        {{ $job->name }}
                    </td>

                    @foreach ($dates as $date)
                        @php
                            $key = $job->id . '|' . $date->toDateString();
                            $val = $budgets->get($key)?->first()->qty ?? '';
                        @endphp
                        <td class="p-0">
                            <input type="number" class="form-control form-control-sm budget-input text-center"
                                value="{{ $val }}" data-subjob="{{ $job->id }}"
                                data-date="{{ $date->toDateString() }}" data-status="{{ $status }}"
                                min="0" placeholder="-">
                        </td>
                    @endforeach
                </tr>
            @endforeach
            {{-- ================= TOTAL PER HARI ================= --}}
            <tr class="fw-bold bg-light">
                <td colspan="3" class="text-end pe-2">
                    TOTAL
                </td>

                @foreach ($dates as $date)
                    @php
                        $totalDay = 0;

                        foreach ($subJobs as $job) {
                            $key = $job->id . '|' . $date->toDateString();
                            $totalDay += $budgets->get($key)?->first()->qty ?? 0;
                        }
                    @endphp
                    <td>
                        {{ $totalDay ?: '' }}
                    </td>
                @endforeach
            </tr>
        </tbody>

    </table>
</div>
