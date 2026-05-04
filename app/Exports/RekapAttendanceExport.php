<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithCustomStartCell
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapAttendanceExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithCustomStartCell
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($item) {
            return [
                $item['name'],
                $item['total_hari'],
                $item['hadir'],
                $item['terlambat'],
                $item['pulang_cepat'],

                $item['DLAW'],
                $item['DLAK'],
                $item['DLP'],

                $item['IJIN1'],
                $item['IJIN2'],
                $item['I'],

                $item['sakit'],
                $item['cuti'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Total Hari',
            'Hadir',
            'Terlambat',
            'Pulang Cepat',

            'DLAW',
            'DLAK',
            'DLP',

            'IJIN1',
            'IJIN2',
            'I',

            'Sakit',
            'Cuti',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            3 => [ 
                'font' => ['bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->setCellValue('A1', 'REKAPITULASI KEHADIRAN');
                $sheet->mergeCells("A1:{$highestColumn}1");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $sheet->setCellValue('A2', 'Nama');
                $sheet->mergeCells('A2:A3');

                $sheet->setCellValue('B2', 'Hari');
                $sheet->mergeCells('B2:B3');

                $sheet->setCellValue('C2', 'Kehadiran');
                $sheet->mergeCells('C2:E2');

                $sheet->setCellValue('F2', 'Dinas Luar');
                $sheet->mergeCells('F2:H2');

                $sheet->setCellValue('I2', 'Izin');
                $sheet->mergeCells('I2:K2');

                $sheet->setCellValue('L2', 'Lainnya');
                $sheet->mergeCells('L2:M2');

                $sheet->getStyle("A2:M3")->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                $sheet->getStyle('C2:E2')->getFill()
                    ->setFillType('solid')
                    ->getStartColor()->setARGB('D9EAD3'); 

                $sheet->getStyle('F2:H2')->getFill()
                    ->setFillType('solid')
                    ->getStartColor()->setARGB('FFF2CC');

                $sheet->getStyle('I2:K2')->getFill()
                    ->setFillType('solid')
                    ->getStartColor()->setARGB('F4CCCC');

                $sheet->getStyle('L2:M2')->getFill()
                    ->setFillType('solid')
                    ->getStartColor()->setARGB('D9D9D9');

                $sheet->getStyle("A2:M{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);

                $sheet->freezePane('A4');

                $sheet->getStyle("B4:M{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal('center');
            },
        ];
    }
}