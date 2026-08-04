<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\GstRate;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'project', 'company']);

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
                    ->orWhereHas('project', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('project_number', 'like', "%{$search}%");
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
        $query = Invoice::onlyTrashed()->with(['customer', 'project', 'company']);

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
        $projects = Project::orderBy('project_number', 'desc')->get();
        $companies = Company::orderBy('name')->get();
        $quotations = Quotation::where('status', 'accepted')
            ->whereDoesntHave('invoices')
            ->orderBy('quotation_number', 'desc')
            ->get();

        $selectedProjectId = $request->project_id;
        $selectedCustomerId = $request->customer_id;
        $selectedQuotationId = $request->quotation_id;

        if ($selectedQuotationId) {
            $quotation = Quotation::find($selectedQuotationId);
            if ($quotation && $quotation->hasInvoice()) {
                return redirect()->route('admin.quotations.show', $selectedQuotationId)
                    ->with('error', 'An invoice has already been created from this quotation.');
            }
        }

        if ($selectedProjectId) {
            $project = Project::find($selectedProjectId);
            if ($project && $project->customer_id) {
                $selectedCustomerId = $project->customer_id;
            }
        }

        $units = Unit::active()->get();
        $gstRates = GstRate::active()->get();

        return view('admin.invoices.create', compact(
            'customers',
            'projects',
            'companies',
            'quotations',
            'selectedProjectId',
            'selectedCustomerId',
            'selectedQuotationId',
            'units',
            'gstRates'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'company_id' => 'nullable|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'required|exists:projects,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'subject' => 'nullable|string|max:200',
            'invoice_type' => 'required|in:items,pdf',
            'pdf_description' => 'nullable|string|max:500',
            'items' => 'nullable|array',
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

        if (!empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if ($project && (int) $project->customer_id !== (int) $validated['customer_id']) {
                return back()->withInput()
                    ->withErrors(['project_id' => 'The selected project does not belong to the selected customer.']);
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
        $invoice->load(['customer', 'project', 'company', 'quotation', 'incomes']);
        $fromProject = $request->from_project;
        return view('admin.invoices.show', compact('invoice', 'fromProject'));
    }

    public function edit(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $projects = Project::orderBy('project_number', 'desc')->get();
        $companies = Company::orderBy('name')->get();
        $quotations = Quotation::orderBy('quotation_number', 'desc')->get();
        $fromProject = $request->from_project;
        $units = Unit::active()->get();
        $gstRates = GstRate::active()->get();

        return view('admin.invoices.edit', compact('invoice', 'customers', 'projects', 'companies', 'quotations', 'fromProject', 'units', 'gstRates'));
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
            'project_id' => 'nullable|exists:projects,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'subject' => 'nullable|string|max:200',
            'invoice_type' => 'required|in:items,pdf',
            'invoice_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_description' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'notes' => 'nullable|string|max:150',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|in:5,18',
            'gst' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'status' => 'nullable|in:sent,partial,paid,overdue,cancelled',
        ]);

        if (!empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if ($project && (int) $project->customer_id !== (int) $validated['customer_id']) {
                return back()->withInput()
                    ->withErrors(['project_id' => 'The selected project does not belong to the selected customer.']);
            }
        }

        if ($request->hasFile('invoice_pdf')) {
            $validated['invoice_pdf'] = $request->file('invoice_pdf')->store('invoices', 'public');
        }

        $validated['balance_due'] = $validated['grand_total'] - $invoice->amount_paid;
        $validated['gst_inclusive'] = $request->has('gst_inclusive');
        $validated['gst_split'] = $request->has('gst_split');

        $invoice->update($validated);

        if ($request->filled('from_project')) {
            return redirect()->route('admin.projects.show', $request->from_project)
                ->with('success', 'Invoice updated successfully.');
        }

        if ($invoice->project_id) {
            return redirect()->route('admin.projects.show', $invoice->project_id)
                ->with('success', 'Invoice updated successfully.');
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $projectId = $invoice->project_id;
        $fromProject = $request->from_project;
        $invoice->delete();

        if ($fromProject) {
            return redirect()->route('admin.projects.show', $fromProject)
                ->with('success', 'Invoice deleted successfully.');
        }

        if ($projectId) {
            return redirect()->route('admin.projects.show', $projectId)
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
            'project_id' => $invoice->project_id,
        ])->with('info', 'Please record the payment with bank/cash account to mark invoice as paid.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'project', 'quotation']);

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
