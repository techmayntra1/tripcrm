<?php

namespace App\Exports;

use App\Models\Income;
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

class IncomeExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    use Exportable;

    public function __construct(protected Request $request) {}

    public function collection()
    {
        $r = $this->request;
        $query = Income::with(['trip', 'customer', 'invoice', 'paymentMode', 'bank']);

        // A customer-scoped export is a lifetime ledger (matches the customer
        // page), so the financial-year filter only applies to the global export.
        if (!$r->filled('customer_id')) {
            $fyDates = getFinancialYearDates();
            if ($fyDates) {
                $query->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
            }
        }
        if ($r->filled('search')) {
            $search = $r->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('trip', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('trip_number', 'like', "%{$search}%"));
            });
        }
        if ($r->filled('income_type')) {
            $query->where('income_type', $r->income_type);
        }
        if ($r->filled('payment_mode_id')) {
            $query->where('payment_mode_id', $r->payment_mode_id);
        }
        if ($r->filled('trip_id')) {
            $query->where('trip_id', $r->trip_id);
        }
        if ($r->filled('customer_id')) {
            $cid = $r->customer_id;
            $query->where(function ($q) use ($cid) {
                $q->where('customer_id', $cid)
                    ->orWhereHas('trip', fn($pq) => $pq->where('customer_id', $cid));
            });
        }

        return $query->orderBy('income_date', 'desc')->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Date', 'Receipt #', 'Type', 'Trip', 'Customer', 'Invoice', 'Payment Mode', 'Bank', 'Description', 'Amount'];
    }

    public function map($income): array
    {
        return [
            optional($income->income_date)->format('d-m-Y') ?? '-',
            $income->receipt_number ?? '-',
            ucfirst($income->income_type ?? '-'),
            $income->trip->name ?? '-',
            $income->customer->name ?? '-',
            $income->invoice->invoice_number ?? '-',
            $income->paymentMode->name ?? '-',
            $income->bank->bank_name ?? '-',
            $income->description ?? '-',
            (float) $income->amount,
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
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
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
