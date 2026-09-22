<?php

namespace App\Http\Controllers\Admin;

use App\Exports\IncomeExport;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\PaymentMode;
use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Income::with(['trip', 'customer', 'invoice', 'bank']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trip', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('trip_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('income_type')) {
            $query->where('income_type', $request->income_type);
        }


        if ($request->filled('payment_mode_id')) {
            $query->where('payment_mode_id', $request->payment_mode_id);
        }

        // Total money count — date range applies ONLY to this total, not the table listing.
        $totalQuery = clone $query;
        if ($request->filled('total_from')) {
            $totalQuery->whereDate('income_date', '>=', $request->total_from);
        }
        if ($request->filled('total_to')) {
            $totalQuery->whereDate('income_date', '<=', $request->total_to);
        }
        $totalAmount = $totalQuery->sum('amount');
        $totalCount = $totalQuery->count();

        $perPage = $request->input('per_page', 15);
        $incomes = $query->orderBy('income_date', 'desc')->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        $trashedQuery = Income::onlyTrashed();
        if ($fyDates) {
            $trashedQuery->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
        }
        $trashedCount = $trashedQuery->count();

        $paymentModes = PaymentMode::active()->get();

        return view('admin.income.index', compact('incomes', 'trashedCount', 'paymentModes', 'totalAmount', 'totalCount'));
    }

    public function create(Request $request)
    {
        $trips = Trip::orderBy('trip_number', 'desc')->get();
        $customers = Customer::orderBy('name')->get();
        $invoices = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->orderBy('invoice_number', 'desc')->get();
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $paymentModes = PaymentMode::active()->get();

        $selectedTripId = $request->trip_id;
        $selectedCustomerId = $request->customer_id;
        $selectedInvoiceId = $request->invoice_id;
        $selectedInvoice = null;

        if ($selectedInvoiceId) {
            $selectedInvoice = Invoice::with(['customer', 'trip'])->find($selectedInvoiceId);
            if ($selectedInvoice) {
                $selectedCustomerId = $selectedInvoice->customer_id;
                $selectedTripId = $selectedInvoice->trip_id;
            }
        }

        if ($selectedTripId && !$selectedCustomerId) {
            $trip = Trip::find($selectedTripId);
            if ($trip && $trip->customer_id) {
                $selectedCustomerId = $trip->customer_id;
            }
        }

        return view('admin.income.create', compact(
            'trips',
            'customers',
            'invoices',
            'banks',
            'cashAccount',
            'paymentModes',
            'selectedTripId',
            'selectedCustomerId',
            'selectedInvoiceId',
            'selectedInvoice'
        ));
    }

    public function store(Request $request)
    {
        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'income_type' => 'required|in:trip,advance,other',
            'income_date' => 'required|date',
            'trip_id' => 'nullable|exists:trips,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'amount' => 'required|numeric|min:1|max:999999999',
            'cheque_number' => 'nullable|string|max:20',
            'cheque_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:40',
            'description' => 'nullable|string|max:150',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'attachment.mimes' => 'Attachment must be PDF, JPG, or PNG.',
            'attachment.max' => 'Attachment must not exceed 2MB.',
        ]);

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('incomes', 'public');
        }

        $income = Income::create($validated);

        if ($income->invoice_id) {
            $income->invoice->updatePaymentStatus();
        }

        return redirect()->route('admin.income.index')
            ->with('success', 'Income added successfully.');
    }

    public function show(Income $income)
    {
        $income->load(['trip', 'customer', 'invoice', 'bank']);
        return view('admin.income.show', compact('income'));
    }

    public function edit(Income $income)
    {
        $trips = Trip::orderBy('trip_number', 'desc')->get();
        $customers = Customer::orderBy('name')->get();
        $invoices = Invoice::whereIn('status', ['sent', 'partial', 'overdue', 'paid'])->orderBy('invoice_number', 'desc')->get();
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $paymentModes = PaymentMode::active()->get();

        return view('admin.income.edit', compact('income', 'trips', 'customers', 'invoices', 'banks', 'cashAccount', 'paymentModes'));
    }

    public function update(Request $request, Income $income)
    {
        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'income_type' => 'required|in:trip,advance,other',
            'income_date' => 'required|date',
            'trip_id' => 'nullable|exists:trips,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'amount' => 'required|numeric|min:1|max:999999999',
            'cheque_number' => 'nullable|string|max:20',
            'cheque_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:40',
            'description' => 'nullable|string|max:150',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'remove_attachment' => 'nullable|boolean',
        ], [
            'attachment.mimes' => 'Attachment must be PDF, JPG, or PNG.',
            'attachment.max' => 'Attachment must not exceed 2MB.',
        ]);

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        if ($request->hasFile('attachment')) {
            if ($income->attachment) {
                \Storage::disk('public')->delete($income->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('incomes', 'public');
        } elseif ($request->boolean('remove_attachment')) {
            if ($income->attachment) {
                \Storage::disk('public')->delete($income->attachment);
            }
            $validated['attachment'] = null;
        } else {
            unset($validated['attachment']);
        }
        unset($validated['remove_attachment']);

        $oldInvoiceId = $income->invoice_id;
        $income->update($validated);

        if ($oldInvoiceId && $oldInvoiceId != $income->invoice_id) {
            Invoice::find($oldInvoiceId)?->updatePaymentStatus();
        }

        if ($income->invoice_id) {
            $income->invoice->updatePaymentStatus();
        }

        return redirect()->route('admin.income.index')
            ->with('success', 'Income updated successfully.');
    }

    public function destroy(Income $income)
    {
        $invoiceId = $income->invoice_id;

        $income->delete();

        if ($invoiceId) {
            Invoice::find($invoiceId)?->updatePaymentStatus();
        }

        return redirect()->route('admin.income.index')
            ->with('success', 'Income deleted successfully.');
    }

    public function trashed(Request $request)
    {
        $query = Income::onlyTrashed()->with(['trip', 'customer', 'invoice', 'bank']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $incomes = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();

        $activeQuery = Income::query();
        if ($fyDates) {
            $activeQuery->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        return view('admin.income.trashed', compact('incomes', 'activeCount'));
    }

    public function restore($id)
    {
        $income = Income::onlyTrashed()->findOrFail($id);
        $income->restore();

        if ($income->invoice_id) {
            $income->invoice->updatePaymentStatus();
        }

        return redirect()->route('admin.income.trashed')
            ->with('success', 'Income restored successfully.');
    }

    public function export(Request $request)
    {
        $fyDates = getFinancialYearDates();
        $fyLabel = $fyDates ? $fyDates['start']->format('Y') . '_' . $fyDates['end']->format('Y') : 'all';
        $filename = safeFilename('income_FY_' . $fyLabel . '_' . now()->format('d_m_Y_His')) . '.xlsx';
        return (new IncomeExport($request))->download($filename);
    }

    public function downloadReceipt(Income $income)
    {
        $income->load(['trip', 'customer', 'invoice.company', 'bank', 'paymentMode']);
        $invoice = $income->invoice;

        // Amount received so far up to and including this receipt (chronological by id).
        $paidToDate = null;
        $balanceAfter = null;
        if ($invoice) {
            $paidToDate = (float) $invoice->incomes()
                ->where('id', '<=', $income->id)
                ->sum('amount');
            $balanceAfter = max((float) $invoice->grand_total - $paidToDate, 0);
        }

        $pdf = Pdf::loadView('admin.income.receipt', compact('income', 'invoice', 'paidToDate', 'balanceAfter'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download(safeFilename('Receipt-' . $income->receipt_number, 'Receipt') . '.pdf');
    }
}
