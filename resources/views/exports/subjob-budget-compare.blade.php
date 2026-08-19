<table>
    <thead>
        <tr>
            <th rowspan="2" style="font-weight: bold; background-color: #f0f0f0;">No</th>
            <th rowspan="2" style="font-weight: bold; background-color: #f0f0f0;">Area</th>
            <th rowspan="2" style="font-weight: bold; background-color: #f0f0f0;">Sub Job</th>
            <th rowspan="2" style="font-weight: bold; background-color: #f0f0f0;">Data</th>
            <th colspan="{{ $daysInMonth }}" style="font-weight: bold; background-color: #f0f0f0; text-align: center;">
                {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
            </th>
        </tr>
        <tr>
            @for ($d = 1; $d <= $daysInMonth; $d++)
                <th style="font-weight: bold; background-color: #f0f0f0; text-align: center;">{{ $d }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $report)
            <tr>
                <td colspan="{{ 4 + $daysInMonth }}"
                    style="font-weight: bold; background-color: #ffffcc; text-align: left;">
                    {{ $report['title'] }}
                </td>
            </tr>

            @php $no = 1; @endphp
            @foreach ($structure as $areaName => $subJobs)
                @foreach ($subJobs as $index => $job)
                    <tr>
                        @if ($index === 0)
                            <td rowspan="{{ count($subJobs) * 3 }}">{{ $no++ }}</td>
                            <td rowspan="{{ count($subJobs) * 3 }}">{{ $areaName }}</td>
                        @endif

                        <td rowspan="3" style="background-color: {{ $job['color'] ?? '#ffffff' }};">
                            {{ $job['name'] }}</td>
                        <td style="color: green; font-weight: bold;">Plan</td>

                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                $val = $report['plan'][$job['id']][$dateStr]['qty'] ?? 0; // Ambil Qty saja untuk excel
                            @endphp
                            <td>{{ $val > 0 ? $val : '' }}</td>
                        @endfor
                    </tr>

                    <tr>
                        <td style="color: blue; font-weight: bold;">Act</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                $val = $report['actual'][$job['id']][$dateStr]['qty'] ?? 0;
                            @endphp
                            <td>{{ $val > 0 ? $val : '' }}</td>
                        @endfor
                    </tr>

                    <tr>
                        <td>%</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateStr = sprintf('%s-%02d-%02d', $year, $month, $d);
                                $p = $report['plan'][$job['id']][$dateStr]['qty'] ?? 0;
                                $a = $report['actual'][$job['id']][$dateStr]['qty'] ?? 0;
                                $percent = '';
                                if ($p > 0) {
                                    $percent = round(($a / $p) * 100) . '%';
                                } elseif ($a > 0) {
                                    $percent = '100%';
                                }
                            @endphp
                            <td>{{ $percent }}</td>
                        @endfor
                    </tr>
                @endforeach
            @endforeach

            <tr>
                <td colspan="{{ 4 + $daysInMonth }}"></td>
            </tr>
        @endforeach
    </tbody>
</table>
