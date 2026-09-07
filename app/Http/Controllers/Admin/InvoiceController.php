<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\GstRate;
use App\Models\Invoice;
use App\Models\Trip;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'trip', 'company']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trip', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('trip_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $invoices = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        $activeQuery = Invoice::query();
        if ($fyDates) {
            $activeQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        $trashedQuery = Invoice::onlyTrashed();
        if ($fyDates) {
            $trashedQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }
        $trashedCount = $trashedQuery->count();

        return view('admin.invoices.index', compact('invoices', 'activeCount', 'trashedCount'));
    }

    public function trashed(Request $request)
    {
        $query = Invoice::onlyTrashed()->with(['customer', 'trip', 'company']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('per_page', 15);
        $invoices = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();

        $activeQuery = Invoice::query();
        if ($fyDates) {
            $activeQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        return view('admin.invoices.trashed', compact('invoices', 'activeCount'));
    }

    public function restore($id)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $invoice->restore();

        return redirect()->route('admin.invoices.trashed')->with('success', 'Invoice restored successfully.');
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $trips = Trip::orderBy('trip_number', 'desc')->get();
        $companies = Company::orderBy('name')->get();
        $quotations = Quotation::where('status', 'accepted')
            ->whereDoesntHave('invoices')
            ->orderBy('quotation_number', 'desc')
            ->get();

        $selectedTripId = $request->trip_id;
        $selectedCustomerId = $request->customer_id;
        $selectedQuotationId = $request->quotation_id;

        if ($selectedQuotationId) {
            $quotation = Quotation::find($selectedQuotationId);
            if ($quotation && $quotation->hasInvoice()) {
                return redirect()->route('admin.quotations.show', $selectedQuotationId)
                    ->with('error', 'An invoice has already been created from this quotation.');
            }
        }

        if ($selectedTripId) {
            $trip = Trip::find($selectedTripId);
            if ($trip) {
                $existingInvoice = $trip->invoices()->first();
                if ($existingInvoice) {
                    return redirect()->route('admin.invoices.show', $existingInvoice)
                        ->with('info', 'This trip already has an invoice. Record additional payments here.');
                }
                if ($trip->customer_id) {
                    $selectedCustomerId = $trip->customer_id;
                }
            }
        }

        $units = Unit::active()->get();
        $gstRates = GstRate::active()->get();
        $services = Service::active()->ordered()->get();

        return view('admin.invoices.create', compact(
            'customers',
            'trips',
            'companies',
            'quotations',
            'selectedTripId',
            'selectedCustomerId',
            'selectedQuotationId',
            'units',
            'gstRates',
            'services'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'company_id' => 'nullable|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'trip_id' => 'required|exists:trips,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'subject' => 'nullable|string|max:200',
            'invoice_type' => 'required|in:items,pdf',
            'pdf_description' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.service_name' => 'nullable|string|max:100',
            'items.*.tax_type' => 'nullable|in:none,gst,vat',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:150',
            'subtotal' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|in:5,18',
            'gst' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0.01',
            'status' => 'nullable|in:sent,partial,paid,overdue,cancelled',
        ];

        if ($request->invoice_type === 'pdf') {
            $rules['invoice_pdf'] = 'required|file|mimes:pdf|max:10240';
        } else {
            $rules['invoice_pdf'] = 'nullable|file|mimes:pdf|max:10240';
        }

        $validated = $request->validate($rules);
        
        if (!empty($validated['quotation_id'])) {
            $quotation = Quotation::find($validated['quotation_id']);
            if ($quotation && $quotation->hasInvoice()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'An invoice has already been created from this quotation.');
            }
        }

        if (!empty($validated['trip_id'])) {
            $trip = Trip::find($validated['trip_id']);
            if ($trip && (int) $trip->customer_id !== (int) $validated['customer_id']) {
                return back()->withInput()
                    ->withErrors(['trip_id' => 'The selected trip does not belong to the selected customer.']);
            }
            if ($trip && $trip->hasInvoice()) {
                return redirect()->route('admin.invoices.show', $trip->invoices()->first())
                    ->with('info', 'This trip already has an invoice. Record additional payments here.');
            }
        }

        $validated['status'] = $validated['status'] ?? 'sent';
        $validated['amount_paid'] = 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['balance_due'] = $validated['grand_total'];
        $validated['gst_inclusive'] = $request->has('gst_inclusive');
        $validated['gst_split'] = $request->has('gst_split');

        if ($request->hasFile('invoice_pdf')) {
            $validated['invoice_pdf'] = $request->file('invoice_pdf')->store('invoices', 'public');
        }

        $invoice = Invoice::create($validated);

        if (!empty($validated['quotation_id'])) {
            return redirect()->route('admin.quotations.show', $validated['quotation_id'])
                ->with('success', 'Invoice created successfully.');
        }

        return redirect()->route('admin.customers.show', $validated['customer_id'])
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Request $request, Invoice $invoice)
    {
        $invoice->load(['customer', 'trip', 'company', 'quotation', 'incomes']);
        $fromTrip = $request->from_trip;
        return view('admin.invoices.show', compact('invoice', 'fromTrip'));
    }

    public function edit(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $trips = Trip::orderBy('trip_number', 'desc')->get();
        $companies = Company::orderBy('name')->get();
        $quotations = Quotation::orderBy('quotation_number', 'desc')->get();
        $fromTrip = $request->from_trip;
        $units = Unit::active()->get();
        $gstRates = GstRate::active()->get();
        $services = Service::active()->ordered()->get();

        return view('admin.invoices.edit', compact('invoice', 'customers', 'trips', 'companies', 'quotations', 'fromTrip', 'units', 'gstRates', 'services'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be modified.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'company_id' => 'nullable|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'trip_id' => 'nullable|exists:trips,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'subject' => 'nullable|string|max:200',
            'invoice_type' => 'required|in:items,pdf',
            'invoice_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_description' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.service_name' => 'nullable|string|max:100',
            'items.*.tax_type' => 'nullable|in:none,gst,vat',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:150',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|in:5,18',
            'gst' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'status' => 'nullable|in:sent,partial,paid,overdue,cancelled',
        ]);

        if (!empty($validated['trip_id'])) {
            $trip = Trip::find($validated['trip_id']);
            if ($trip && (int) $trip->customer_id !== (int) $validated['customer_id']) {
                return back()->withInput()
                    ->withErrors(['trip_id' => 'The selected trip does not belong to the selected customer.']);
            }
        }

        if ($request->hasFile('invoice_pdf')) {
            $validated['invoice_pdf'] = $request->file('invoice_pdf')->store('invoices', 'public');
        }

        $validated['balance_due'] = $validated['grand_total'] - $invoice->amount_paid;
        $validated['gst_inclusive'] = $request->has('gst_inclusive');
        $validated['gst_split'] = $request->has('gst_split');

        $invoice->update($validated);

        if ($request->filled('from_trip')) {
            return redirect()->route('admin.trips.show', $request->from_trip)
                ->with('success', 'Invoice updated successfully.');
        }

        if ($invoice->trip_id) {
            return redirect()->route('admin.trips.show', $invoice->trip_id)
                ->with('success', 'Invoice updated successfully.');
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $tripId = $invoice->trip_id;
        $fromTrip = $request->from_trip;
        $invoice->delete();

        if ($fromTrip) {
            return redirect()->route('admin.trips.show', $fromTrip)
                ->with('success', 'Invoice deleted successfully.');
        }

        if ($tripId) {
            return redirect()->route('admin.trips.show', $tripId)
                ->with('success', 'Invoice deleted successfully.');
        }

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function markAsSent(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);

        return redirect()->back()->with('success', 'Invoice marked as sent.');
    }

    public function markAsPaid(Invoice $invoice)
    {
        return redirect()->route('admin.income.create', [
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'trip_id' => $invoice->trip_id,
        ])->with('info', 'Please record the payment with bank/cash account to mark invoice as paid.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'trip', 'quotation']);

        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }
}
