<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Bank;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withTrashed()->with(['banks', 'quotations', 'invoices'])->get();

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        $banks = Bank::whereNull('company_id')->active()->ordered()->get();

        return view('admin.companies.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:15',
            'country' => 'required|in:india,uae',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:10',
            'vat_number' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'quotation_number_series' => ['required', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\-\/]+$/'],
            'invoice_number_series' => ['required', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\-\/]+$/'],
            'bank_ids' => 'nullable|array',
            'bank_ids.*' => 'exists:banks,id',
        ], [
            'quotation_number_series.required' => 'Quotation number series is required.',
            'quotation_number_series.regex' => 'Quotation series must contain only letters, numbers, hyphens, and slashes.',
            'invoice_number_series.required' => 'Invoice number series is required.',
            'invoice_number_series.regex' => 'Invoice series must contain only letters, numbers, hyphens, and slashes.',
        ]);

        unset($validated['bank_ids']);
        $validated = $this->clearTaxFieldsForOtherRegion($validated);

        $company = Company::create($validated);

        $this->assignBanks($company, $request->input('bank_ids', []));

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company created successfully.');
    }

    // An Indian company only carries GST/PAN, a UAE company only carries VAT.
    private function clearTaxFieldsForOtherRegion(array $data): array
    {
        if (($data['country'] ?? 'india') === Company::COUNTRY_UAE) {
            $data['gst_number'] = null;
            $data['pan_number'] = null;
        } else {
            $data['vat_number'] = null;
        }

        return $data;
    }

    // Banks follow their company's region so currency and IFSC/IBAN always match.
    private function assignBanks(Company $company, array $bankIds): void
    {
        Bank::where('company_id', $company->id)->update(['company_id' => null]);

        if (!empty($bankIds)) {
            Bank::whereIn('id', $bankIds)->update([
                'company_id' => $company->id,
                'country' => $company->country,
            ]);
        }
    }

    public function show(Company $company)
    {
        $company->load(['banks', 'quotations', 'invoices']);

        $totalIncome = $company->invoices()->where('status', 'paid')->sum('grand_total');

        $totalReceivable = $company->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('balance_due');

        return view('admin.companies.show', compact('company', 'totalIncome', 'totalReceivable'));
    }

    public function edit(Company $company)
    {
        $company->load('banks');
        $availableBanks = Bank::where(function($q) use ($company) {
            $q->whereNull('company_id')
              ->orWhere('company_id', $company->id);
        })->active()->ordered()->get();

        return view('admin.companies.edit', compact('company', 'availableBanks'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:15',
            'country' => 'required|in:india,uae',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:10',
            'vat_number' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'quotation_number_series' => ['required', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\-\/]+$/'],
            'invoice_number_series' => ['required', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\-\/]+$/'],
            'bank_ids' => 'nullable|array',
            'bank_ids.*' => 'exists:banks,id',
        ], [
            'quotation_number_series.required' => 'Quotation number series is required.',
            'quotation_number_series.regex' => 'Quotation series must contain only letters, numbers, hyphens, and slashes.',
            'invoice_number_series.required' => 'Invoice number series is required.',
            'invoice_number_series.regex' => 'Invoice series must contain only letters, numbers, hyphens, and slashes.',
        ]);

        unset($validated['bank_ids']);
        $validated = $this->clearTaxFieldsForOtherRegion($validated);
        $company->update($validated);

        $this->assignBanks($company, $request->input('bank_ids', []));

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        Bank::where('company_id', $company->id)->update(['company_id' => null]);

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    public function toggle($id)
    {
        $company = Company::withTrashed()->findOrFail($id);
        if ($company->trashed()) {
            $company->restore();
            return redirect()->back()->with('success', 'Company restored successfully.');
        } else {
            $company->delete();
            return redirect()->back()->with('success', 'Company deleted successfully.');
        }
    }
}
