<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\GstRate;
use App\Models\Trip;
use App\Models\Quotation;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['company', 'customer', 'invoices']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $quotations = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        $companies = Company::orderBy('name')->get();

        $trashedQuery = Quotation::onlyTrashed();
        if ($fyDates) {
            $trashedQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }
        $trashedCount = $trashedQuery->count();

        return view('admin.quotations.index', compact('quotations', 'companies', 'trashedCount'));
    }

    public function trashed(Request $request)
    {
        $query = Quotation::onlyTrashed()->with(['company', 'customer']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        $perPage = $request->input('per_page', 15);
        $quotations = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();
        $companies = Company::orderBy('name')->get();

        $activeQuery = Quotation::query();
        if ($fyDates) {
            $activeQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        return view('admin.quotations.trashed', compact('quotations', 'companies', 'activeCount'));
    }

    public function restore($id)
    {
        $quotation = Quotation::onlyTrashed()->findOrFail($id);
        $quotation->restore();

        return redirect()->route('admin.quotations.trashed')->with('success', 'Quotation restored successfully.');
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $trips = Trip::orderBy('trip_number', 'desc')->get();
        $units = Unit::active()->get();
        $gstRates = GstRate::active()->get();

        return view('admin.quotations.create', compact('companies', 'customers', 'trips', 'units', 'gstRates'));
    }

    public function store(Request $request)
    {
        $rules = [
            'date' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'trip_id' => 'nullable|exists:trips,id',
            'subject' => 'nullable|string|max:150',
            'quotation_type' => 'required|in:pdf,items',
            'quotation_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_description' => 'nullable|string|max:500',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric',
            'gst' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'terms' => 'nullable|string|max:2000',
        ];


        if ($request->input('quotation_type') === 'items') {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.description'] = 'required|string|min:1';
            $rules['items.*.unit'] = 'nullable|string';
            $rules['items.*.height'] = 'nullable|numeric|min:0';
            $rules['items.*.width'] = 'nullable|numeric|min:0';
            $rules['items.*.total'] = 'nullable|numeric|min:0';
            $rules['items.*.qty'] = 'required|numeric|min:0.01';
            $rules['items.*.rate'] = 'nullable|numeric|min:0';
            $rules['items.*.amount'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules, [
            'company_id.required' => 'Please select a company.',
            'customer_id.required' => 'Please select a customer.',
            'date.required' => 'Please select a date.',
            'items.required' => 'Please add at least one item.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.description.min' => 'Item description must be at least 1 character.',
            'items.*.qty.required' => 'Item quantity is required.',
        ]);

        if (!empty($validated['trip_id'])) {
            $trip = Trip::find($validated['trip_id']);
            if ($trip && (int) $trip->customer_id !== (int) $validated['customer_id']) {
                return back()->withInput()
                    ->withErrors(['trip_id' => 'The selected trip does not belong to the selected customer.']);
            }
        }

        $pdfPath = null;
        if ($request->hasFile('quotation_pdf')) {
            $pdfPath = $request->file('quotation_pdf')->store('quotations', 'public');
        }

        $items = null;
        if ($validated['quotation_type'] === 'items' && !empty($validated['items'] ?? null)) {
            $items = collect($validated['items'])->map(function ($item) {
                if (!isset($item['amount']) || $item['amount'] === null || $item['amount'] === '') {
                    if (strtolower($item['unit'] ?? '') === 'sqft') {
                        $item['amount'] = ($item['total'] ?? 0) * ($item['qty'] ?? 0) * ($item['rate'] ?? 0);
                    } else {
                        $item['amount'] = ($item['qty'] ?? 0) * ($item['rate'] ?? 0);
                    }
                }
                return $item;
            })->toArray();
        }

        $status = 'sent';

        $quotation = Quotation::create([
            'date' => $validated['date'],
            'company_id' => $validated['company_id'],
            'customer_id' => $validated['customer_id'],
            'trip_id' => $validated['trip_id'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'quotation_type' => $validated['quotation_type'],
            'quotation_pdf' => $pdfPath,
            'pdf_description' => $validated['pdf_description'] ?? null,
            'items' => $items,
            'subtotal' => $validated['subtotal'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'gst_percent' => $validated['gst_percent'] ?? 0,
            'gst_inclusive' => $request->has('gst_inclusive'),
            'gst_split' => $request->has('gst_split'),
            'gst' => $validated['gst'] ?? 0,
            'grand_total' => $validated['grand_total'] ?? 0,
            'terms' => $validated['terms'] ?? null,
            'status' => $status,
        ]);

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['company', 'customer', 'invoices']);
        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->status === 'accepted') {
            return redirect()->route('admin.quotations.show', $quotation);
        }

        $companies = Company::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $trips = Trip::orderBy('trip_number', 'desc')->get();
        $quotation->load(['company', 'customer']);
        $units = Unit::active()->get();
        $gstRates = GstRate::active()->get();

        return view('admin.quotations.edit', compact('quotation', 'companies', 'customers', 'trips', 'units', 'gstRates'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->status === 'accepted') {
            return redirect()->route('admin.quotations.show', $quotation);
        }

        $rules = [
            'date' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'trip_id' => 'nullable|exists:trips,id',
            'subject' => 'nullable|string|max:150',
            'quotation_type' => 'required|in:pdf,items',
            'quotation_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_description' => 'nullable|string|max:500',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric',
            'gst' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'terms' => 'nullable|string|max:2000',
            'status' => 'nullable|in:sent,accepted,rejected,expired',
        ];


        if ($request->input('quotation_type') === 'items') {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.description'] = 'required|string|min:1';
            $rules['items.*.unit'] = 'nullable|string';
            $rules['items.*.height'] = 'nullable|numeric|min:0';
            $rules['items.*.width'] = 'nullable|numeric|min:0';
            $rules['items.*.total'] = 'nullable|numeric|min:0';
            $rules['items.*.qty'] = 'required|numeric|min:0.01';
            $rules['items.*.rate'] = 'nullable|numeric|min:0';
            $rules['items.*.amount'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules, [
            'company_id.required' => 'Please select a company.',
            'customer_id.required' => 'Please select a customer.',
            'date.required' => 'Please select a date.',
            'items.required' => 'Please add at least one item.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.description.min' => 'Item description must be at least 1 character.',
            'items.*.qty.required' => 'Item quantity is required.',
        ]);

        if (!empty($validated['trip_id'])) {
            $trip = Trip::find($validated['trip_id']);
            if ($trip && (int) $trip->customer_id !== (int) $validated['customer_id']) {
                return back()->withInput()
                    ->withErrors(['trip_id' => 'The selected trip does not belong to the selected customer.']);
            }
        }

        if ($request->hasFile('quotation_pdf')) {
            if ($quotation->quotation_pdf) {
                Storage::disk('public')->delete($quotation->quotation_pdf);
            }
            $validated['quotation_pdf'] = $request->file('quotation_pdf')->store('quotations', 'public');
        }

        if ($validated['quotation_type'] === 'items' && !empty($validated['items'] ?? null)) {
            $validated['items'] = collect($validated['items'])->map(function ($item) {
                if (!isset($item['amount']) || $item['amount'] === null || $item['amount'] === '') {
                    if (strtolower($item['unit'] ?? '') === 'sqft') {
                        $item['amount'] = ($item['total'] ?? 0) * ($item['qty'] ?? 0) * ($item['rate'] ?? 0);
                    } else {
                        $item['amount'] = ($item['qty'] ?? 0) * ($item['rate'] ?? 0);
                    }
                }
                return $item;
            })->toArray();
        } else {
            $validated['items'] = null;
        }

        $quotation->update([
            'date' => $validated['date'],
            'company_id' => $validated['company_id'],
            'customer_id' => $validated['customer_id'],
            'trip_id' => $validated['trip_id'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'quotation_type' => $validated['quotation_type'],
            'quotation_pdf' => $validated['quotation_pdf'] ?? $quotation->quotation_pdf,
            'pdf_description' => $validated['pdf_description'] ?? null,
            'items' => $validated['items'],
            'subtotal' => $validated['subtotal'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'gst_percent' => $validated['gst_percent'] ?? 0,
            'gst_inclusive' => $request->has('gst_inclusive'),
            'gst_split' => $request->has('gst_split'),
            'gst' => $validated['gst'] ?? 0,
            'grand_total' => $validated['grand_total'] ?? 0,
            'terms' => $validated['terms'] ?? null,
            'status' => $validated['status'] ?? $quotation->status,
        ]);

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted successfully.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'status' => 'required|in:sent,accepted,rejected,expired',
        ]);

        $quotation->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Quotation status updated successfully.');
    }

    public function downloadPdf(Quotation $quotation)
    {
        $quotation->load(['company', 'customer']);

        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download('Quotation-' . $quotation->quotation_number . '.pdf');
    }
}
