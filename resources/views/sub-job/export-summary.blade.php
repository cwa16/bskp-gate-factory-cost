<table border="1">
    <thead>
        <tr>
            <th colspan="9" style="font-weight:bold; font-size: 14px;">WAGES SUMMARY
                {{ \Carbon\Carbon::create(null, $month)->format('F Y') }}</th>
        </tr>
        <tr style="background-color: #d1ecf1;">
            <th>No</th>
            <th>NIK</th>
            <th>Nama</th>
            <th>Total HK</th>
            <th>Original (jam)</th>
            <th>Final (jam)</th>
            <th>Rp (OT)</th>
            <th>Gaji</th>
            <th>Total Gaji + Overtime</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $i => $emp)
            @php $tot = $employeeTotals[$emp->nik]; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $emp->nik }}</td>
                <td>{{ $emp->name }}</td>
                <td style="mso-number-format:'0';">{{ $tot['total_hk'] }}</td>
                <td style="mso-number-format:'0.0';">{{ $tot['total_ot'] }}</td>
                <td style="mso-number-format:'0.0';">{{ $tot['total_ot'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $tot['total_ot_rp'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $tot['total_hk_rp'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $tot['total_rp'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
