<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    use Exportable;

    public function __construct(protected Request $request) {}

    public function collection()
    {
        $r = $this->request;
        $query = Expense::with(['vendor', 'staff', 'trip', 'category', 'paymentMode', 'bank']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }
        if ($r->filled('search')) {
            $search = $r->search;
            $query->where(fn($q) => $q->where('expense_number', 'like', "%{$search}%"));
        }
        if ($r->filled('expense_type')) {
            $query->where('expense_type', $r->expense_type);
        }
        if ($r->filled('payment_status')) {
            $query->where('payment_status', $r->payment_status);
        }
        if ($r->filled('trip_id')) {
            $query->where('trip_id', $r->trip_id);
        }

        return $query->orderBy('expense_type')->orderBy('expense_date', 'desc')->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Date', 'Expense #', 'Type', 'Category', 'Trip', 'Vendor / Staff', 'Description', 'Amount', 'Paid', 'Unpaid', 'Status', 'Payment Mode', 'Bank'];
    }

    public function map($expense): array
    {
        $party = $expense->vendor->name
            ?? $expense->staff->name
            ?? '-';

        return [
            optional($expense->expense_date)->format('d-m-Y') ?? '-',
            $expense->expense_number,
            $expense->expense_type_display,
            $expense->category->name ?? '-',
            $expense->trip->name ?? '-',
            $party,
            $expense->description ?? '-',
            (float) $expense->grand_total,
            (float) $expense->paid_amount,
            (float) $expense->balance,
            ucfirst($expense->payment_status),
            $expense->paymentMode->name ?? '-',
            $expense->bank->bank_name ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                if ($highestRow > 1) {
                    $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                    ]);
                }
            },
        ];
    }
}
