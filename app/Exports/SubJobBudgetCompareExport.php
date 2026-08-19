<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubJobBudgetCompareExport implements
FromView, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $month;
    protected $year;
    protected $daysInMonth;

    public function __construct($data, $month, $year, $daysInMonth)
    {
        $this->data        = $data; // Data reports, structure, dll
        $this->month       = $month;
        $this->year        = $year;
        $this->daysInMonth = $daysInMonth;
    }

    public function view(): View
    {
        return view('exports.subjob-budget-compare', [
            'reports'     => $this->data['reports'],
            'structure'   => $this->data['structure'],
            'month'       => $this->month,
            'year'        => $this->year,
            'daysInMonth' => $this->daysInMonth,
        ]);
    }

    /* ================= COLUMN WIDTH ================= */
    public function columnWidths(): array
    {
        return [
            'A' => 5,  // No
            'B' => 15, // Area
            'C' => 25, // Sub Job
            'D' => 10, // P/A
        ];
    }

    // Styling Excel (Optional tapi Recommended biar rapi)
    public function styles(Worksheet $sheet)
    {
        return [
            // Style Header (Baris 1 & 2)
            1           => ['font' => ['bold' => true, 'size' => 12]],
            2           => ['font' => ['bold' => true]],

            // Border untuk seluruh tabel (sesuaikan range cell)
            'A1:AZ1000' => [
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
