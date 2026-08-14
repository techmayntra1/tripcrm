<?php

namespace App\Exports;

use App\Models\Trip;
use App\Models\Staff;
use App\Models\Vendor;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TripExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(protected Trip $trip) {}

    public function sheets(): array
    {
        $trip = $this->trip->load([
            'customer', 'company', 'quotation', 'files',
            'tripServices.addons', 'tripServices.serviceExpenses.bank',
            'addons',
            'expenses.category', 'expenses.vendor', 'expenses.staff', 'expenses.paymentMode', 'expenses.bank',
            'incomes.customer', 'incomes.invoice', 'incomes.paymentMode', 'incomes.bank',
            'invoices.customer',
        ]);

        $sheets = [];

        $sheets[] = new TripSheet('Summary', $this->summaryRows($trip));

        if ($trip->tripServices->count() > 0) {
            $sheets[] = new TripSheet('Services', $this->serviceRows($trip));
            $paymentRows = $this->servicePaymentRows($trip);
            if (count($paymentRows) > 1) {
                $sheets[] = new TripSheet('Service Payments', $paymentRows);
            }
        }

        $addonRows = $this->addonRows($trip);
        if (count($addonRows) > 1) {
            $sheets[] = new TripSheet('Add-Ons', $addonRows);
        }

        $expenseTypes = [
            'trip' => 'Trip Expenses',
            'vendor' => 'Vendor Payments',
            'general' => 'General Expenses',
            'salary' => 'Salary Payments',
        ];
        foreach ($expenseTypes as $type => $label) {
            $rows = $this->expenseRows($trip, $type);
            if (count($rows) > 1) {
                $sheets[] = new TripSheet($label, $rows);
            }
        }

        if ($trip->incomes->count() > 0) {
            $sheets[] = new TripSheet('Income', $this->incomeRows($trip));
        }

        if ($trip->invoices->count() > 0) {
            $sheets[] = new TripSheet('Invoices', $this->invoiceRows($trip));
        }

        $taskRows = $this->taskRows($trip);
        if (count($taskRows) > 1) {
            $sheets[] = new TripSheet('Tasks', $taskRows);
        }

        if ($trip->files->count() > 0) {
            $sheets[] = new TripSheet('Files', $this->fileRows($trip));
        }

        return $sheets;
    }

    protected function fileRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'File Name', 'Type', 'Uploaded At'],
        ];
        $i = 1;
        foreach ($trip->files as $file) {
            $rows[] = [
                $i++,
                $file->original_name ?? '-',
                $file->mime_type ?? '-',
                optional($file->created_at)->format('d-m-Y H:i') ?: '-',
            ];
        }
        return $rows;
    }

    protected function summaryRows(Trip $trip): array
    {
        $staffNames = $this->namesOf(Staff::class, $trip->assigned_staff_ids ?? []);
        $vendorNames = $this->namesOf(Vendor::class, $trip->assigned_vendor_ids ?? []);
        $workTypes = is_array($trip->work_type) ? implode(', ', $trip->work_type) : ($trip->work_type ?? '-');

        // Use the same accessors as the on-screen trip page so the export and
        // the screen always agree.
        $totalIncome = (float) $trip->total_income;
        $totalExpense = (float) $trip->total_spent;
        $pendingToReceive = max(0, (float) $trip->total_budget - $totalIncome);

        return [
            ['TRIP REPORT'],
            [],
            ['Trip Number', $trip->trip_number],
            ['Trip Name', $trip->name],
            ['Status', ucfirst($trip->status ?? '-')],
            ['Work Type', $workTypes],
            [],
            ['Customer', $trip->customer->name ?? '-'],
            ['Customer Mobile', $trip->customer->mobile ?? '-'],
            ['Customer Email', $trip->customer->email ?? '-'],
            ['Company', $trip->company->name ?? '-'],
            ['Quotation', $trip->quotation->quotation_number ?? '-'],
            [],
            ['Start Date', optional($trip->start_date)->format('d-m-Y') ?: '-'],
            ['Expected End Date', optional($trip->expected_end_date)->format('d-m-Y') ?: '-'],
            ['Actual End Date', optional($trip->actual_end_date)->format('d-m-Y') ?: '-'],
            [],
            ['Site Address', $trip->site_address ?? '-'],
            ['Description', $trip->description ?? '-'],
            ['Notes', $trip->notes ?? '-'],
            [],
            ['Assigned Staff', $staffNames ?: '-'],
            ['Assigned Vendors', $vendorNames ?: '-'],
            [],
            ['FINANCIALS'],
            ['Budget', (float) $trip->budget],
            ['Add-Ons', (float) $trip->add_on_total],
            ['Total Trip Value', (float) $trip->total_budget],
            ['Total Income', $totalIncome],
            ['Total Expenses (Paid)', $totalExpense],
            ['Profit / Loss', $totalIncome - $totalExpense],
            ['Pending to Receive', $pendingToReceive],
            ['Pending to Give', (float) $trip->pending_to_give],
        ];
    }

    protected function serviceRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'Service(s)', 'Amount', 'Advance', 'Advance Bank', 'Add-ons Total', 'Total', 'Paid', 'Balance', 'Due Date', 'Status', 'Note'],
        ];
        $i = 1;
        foreach ($trip->tripServices as $ps) {
            $rows[] = [
                $i++,
                implode(', ', $ps->service_names),
                (float) $ps->amount,
                (float) $ps->advance,
                $ps->advanceBank->bank_name ?? '-',
                (float) $ps->addons_total,
                (float) $ps->total_amount,
                (float) $ps->paid_amount,
                (float) $ps->balance,
                optional($ps->due_date)->format('d-m-Y') ?: '-',
                ucfirst($ps->payment_status ?? '-'),
                $ps->note ?? '-',
            ];
        }
        if ($trip->tripServices->count() > 0) {
            $rows[] = [
                'TOTAL', '',
                (float) $trip->tripServices->sum('amount'),
                (float) $trip->tripServices->sum('advance'),
                '',
                (float) $trip->tripServices->sum(fn($s) => $s->addons_total),
                (float) $trip->tripServices->sum(fn($s) => $s->total_amount),
                (float) $trip->tripServices->sum(fn($s) => $s->paid_amount),
                (float) $trip->tripServices->sum(fn($s) => $s->balance),
                '', '', '',
            ];
        }
        return $rows;
    }

    protected function servicePaymentRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'Payment Date', 'Bank', 'Amount', 'Note'],
        ];
        $i = 1;
        $total = 0;
        foreach ($trip->tripServices as $ps) {
            foreach ($ps->serviceExpenses as $payment) {
                $rows[] = [
                    $i++,
                    optional($payment->expense_date)->format('d-m-Y') ?: '-',
                    $payment->bank->bank_name ?? '-',
                    (float) $payment->paid_amount,
                    $payment->description ?? '-',
                ];
                $total += (float) $payment->paid_amount;
            }
        }
        if (count($rows) > 1) {
            $rows[] = ['TOTAL', '', '', $total, ''];
        }
        return $rows;
    }

    protected function addonRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'Source', 'Description', 'Service(s)', 'Amount'],
        ];
        $i = 1;
        $total = 0;

        foreach ($trip->tripServices as $ps) {
            $source = 'Service: ' . implode(', ', $ps->service_names);
            foreach ($ps->addons as $addon) {
                $rows[] = [
                    $i++,
                    $source,
                    $addon->description ?? '-',
                    implode(', ', $addon->service_names ?? []),
                    (float) $addon->amount,
                ];
                $total += (float) $addon->amount;
            }
        }

        foreach ($trip->addons ?? [] as $addon) {
            $rows[] = [
                $i++,
                'Trip Add-on',
                $addon->name ?? $addon->description ?? '-',
                '-',
                (float) ($addon->amount ?? 0),
            ];
            $total += (float) ($addon->amount ?? 0);
        }

        if (count($rows) > 1) {
            $rows[] = ['TOTAL', '', '', '', $total];
        }
        return $rows;
    }

    protected function expenseRows(Trip $trip, string $type): array
    {
        $rows = [
            ['SR', 'Date', 'Expense #', 'Category', 'Party (Vendor/Staff)', 'Description', 'Items', 'Sub Total', 'GST %', 'GST', 'Grand Total', 'Paid', 'Unpaid', 'Status', 'Payment Mode', 'Bank'],
        ];
        $expenses = $trip->expenses->where('expense_type', $type)->sortByDesc('expense_date');

        $i = 1;
        foreach ($expenses as $expense) {
            $party = $expense->vendor->name
                ?? $expense->staff->name
                ?? '-';

            $items = is_array($expense->items) ? $expense->items : (json_decode($expense->items, true) ?? []);
            $itemsSummary = count($items) > 0
                ? collect($items)->map(fn($it) => ($it['description'] ?? '-') . ': ' . ($it['amount'] ?? 0))->implode(' | ')
                : '-';

            $rows[] = [
                $i++,
                optional($expense->expense_date)->format('d-m-Y') ?: '-',
                $expense->expense_number,
                $expense->category->name ?? '-',
                $party,
                $expense->description ?? '-',
                $itemsSummary,
                (float) $expense->sub_total,
                (float) $expense->gst_percentage,
                (float) $expense->gst_amount,
                (float) $expense->grand_total,
                (float) $expense->paid_amount,
                (float) $expense->balance,
                ucfirst($expense->payment_status),
                $expense->paymentMode->name ?? '-',
                $expense->bank->bank_name ?? '-',
            ];
        }

        if ($expenses->count() > 0) {
            $rows[] = [
                'TOTAL', '', '', '', '', '', '',
                (float) $expenses->sum('sub_total'),
                '',
                (float) $expenses->sum('gst_amount'),
                (float) $expenses->sum('grand_total'),
                (float) $expenses->sum('paid_amount'),
                (float) $expenses->sum(fn($e) => $e->grand_total - $e->paid_amount),
                '', '', '',
            ];
        }

        return $rows;
    }

    protected function incomeRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'Date', 'Receipt #', 'Type', 'Customer', 'Invoice #', 'Payment Mode', 'Bank', 'Cheque #', 'Cheque Date', 'Description', 'Amount'],
        ];
        $i = 1;
        $incomes = $trip->incomes->sortByDesc('income_date');

        foreach ($incomes as $income) {
            $rows[] = [
                $i++,
                optional($income->income_date)->format('d-m-Y') ?: '-',
                $income->receipt_number ?? '-',
                ucfirst($income->income_type ?? '-'),
                $income->customer->name ?? '-',
                $income->invoice->invoice_number ?? '-',
                $income->paymentMode->name ?? '-',
                $income->bank->bank_name ?? '-',
                $income->cheque_number ?? '-',
                optional($income->cheque_date)->format('d-m-Y') ?: '-',
                $income->description ?? '-',
                (float) $income->amount,
            ];
        }

        if ($incomes->count() > 0) {
            $rows[] = ['TOTAL', '', '', '', '', '', '', '', '', '', '', (float) $incomes->sum('amount')];
        }
        return $rows;
    }

    protected function invoiceRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'Date', 'Due Date', 'Invoice #', 'Subject', 'Customer', 'Type', 'Sub Total', 'Discount', 'GST %', 'GST', 'Grand Total', 'Paid', 'Balance', 'Status', 'Notes'],
        ];
        $i = 1;
        $invoices = $trip->invoices->sortByDesc('date');

        foreach ($invoices as $invoice) {
            $rows[] = [
                $i++,
                optional($invoice->date)->format('d-m-Y') ?: '-',
                optional($invoice->due_date)->format('d-m-Y') ?: '-',
                $invoice->invoice_number,
                $invoice->subject ?? '-',
                $invoice->customer->name ?? '-',
                ucfirst($invoice->invoice_type ?? '-'),
                (float) $invoice->subtotal,
                (float) $invoice->discount,
                (float) $invoice->gst_percent,
                (float) $invoice->gst,
                (float) $invoice->grand_total,
                (float) $invoice->amount_paid,
                (float) $invoice->balance_due,
                ucfirst($invoice->status ?? '-'),
                $invoice->notes ?? '-',
            ];
        }

        if ($invoices->count() > 0) {
            $rows[] = [
                'TOTAL', '', '', '', '', '', '',
                (float) $invoices->sum('subtotal'),
                (float) $invoices->sum('discount'),
                '',
                (float) $invoices->sum('gst'),
                (float) $invoices->sum('grand_total'),
                (float) $invoices->sum('amount_paid'),
                (float) $invoices->sum('balance_due'),
                '', '',
            ];
        }
        return $rows;
    }

    protected function taskRows(Trip $trip): array
    {
        $rows = [
            ['SR', 'Title', 'Assignee', 'Assignee Type', 'Start', 'Due', 'Status', 'Location', 'Description'],
        ];
        $tasks = Task::with('status')->where('trip_id', $trip->id)->orderBy('start_at', 'desc')->get();

        $i = 1;
        foreach ($tasks as $task) {
            $rows[] = [
                $i++,
                $task->title,
                $task->assignee_name,
                ucfirst($task->assignee_type ?? '-'),
                optional($task->start_at)->format('d-m-Y H:i') ?: '-',
                optional($task->due_at)->format('d-m-Y H:i') ?: '-',
                $task->status_name ?? '-',
                $task->location ?? '-',
                $task->description ?? '-',
            ];
        }
        return $rows;
    }

    protected function namesOf(string $class, array $ids): string
    {
        if (empty($ids)) return '';
        return $class::whereIn('id', $ids)->pluck('name')->implode(', ');
    }
}

class TripSheet implements FromArray, WithTitle, WithEvents
{
    public function __construct(protected string $title, protected array $rows) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return substr($this->title, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                if ($highestRow >= 1) {
                    $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343A40']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(22);
                }

                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                if ($highestRow > 1) {
                    $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                    ]);

                    // Highlight TOTAL row(s) — any row where col A == 'TOTAL'
                    for ($r = 2; $r <= $highestRow; $r++) {
                        $val = $sheet->getCell('A' . $r)->getValue();
                        if ($val === 'TOTAL') {
                            $sheet->getStyle('A' . $r . ':' . $highestColumn . $r)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
                            ]);
                        }
                    }
                }
            },
        ];
    }
}
