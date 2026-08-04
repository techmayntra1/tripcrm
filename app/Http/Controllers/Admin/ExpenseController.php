<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpenseExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseType;
use App\Models\PaymentMode;
use App\Models\Project;
use App\Models\Unit;
use App\Models\Bank;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['vendor', 'staff', 'project', 'category', 'bank']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }


        // Total money count — date range applies ONLY to these totals, not the table listing.
        $totalQuery = clone $query;
        if ($request->filled('total_from')) {
            $totalQuery->whereDate('expense_date', '>=', $request->total_from);
        }
        if ($request->filled('total_to')) {
            $totalQuery->whereDate('expense_date', '<=', $request->total_to);
        }
        $totalGrandTotal = $totalQuery->sum('grand_total');
        $totalPaid = $totalQuery->sum('paid_amount');
        $totalCount = $totalQuery->count();

        $perPage = $request->input('per_page', 15);
        $expenses = $query->orderBy('expense_date', 'desc')->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        $trashedQuery = Expense::onlyTrashed();
        if ($fyDates) {
            $trashedQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }
        $trashedCount = $trashedQuery->count();

        return view('admin.expenses.index', compact('expenses', 'trashedCount', 'totalGrandTotal', 'totalPaid', 'totalCount'));
    }

    public function trashed(Request $request)
    {
        $query = Expense::onlyTrashed()->with(['vendor', 'staff', 'project', 'category', 'bank']);

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }

        $perPage = $request->input('per_page', 15);
        $expenses = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();

        $activeQuery = Expense::query();
        if ($fyDates) {
            $activeQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        return view('admin.expenses.trashed', compact('expenses', 'activeCount'));
    }

    public function restore($id)
    {
        $expense = Expense::onlyTrashed()->findOrFail($id);
        $expense->restore();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense restored successfully.');
    }

    public function create(Request $request)
    {
        $vendors = Vendor::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $categories = ExpenseCategory::orderBy('name')->get();
        $expenseTypes = ExpenseType::active()->get();
        $paymentModes = PaymentMode::active()->get();
        $units = Unit::active()->get();
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();

        $selectedVendorId = $request->vendor_id;
        $selectedProjectId = $request->project_id;
        $selectedExpenseType = $request->expense_type;

        // Build project-vendor mapping for vendor payment filtering
        $projectVendorMap = $projects->mapWithKeys(function ($project) {
            return [$project->id => $project->assigned_vendor_ids ?? []];
        });

        return view('admin.expenses.create', compact(
            'vendors',
            'projects',
            'categories',
            'expenseTypes',
            'paymentModes',
            'units',
            'banks',
            'cashAccount',
            'selectedVendorId',
            'selectedProjectId',
            'selectedExpenseType',
            'projectVendorMap'
        ));
    }

    public function checkDuplicate(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'project_id' => 'nullable|integer',
            'vendor_id' => 'nullable|integer',
            'grand_total' => 'required|numeric',
        ]);

        $query = Expense::query()
            ->whereDate('expense_date', $validated['expense_date'])
            ->where('grand_total', $validated['grand_total']);

        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        } else {
            $query->whereNull('project_id');
        }

        if (!empty($validated['vendor_id'])) {
            $query->where('vendor_id', $validated['vendor_id']);
        } else {
            $query->whereNull('vendor_id');
        }

        $existing = $query->first();

        return response()->json([
            'duplicate' => (bool) $existing,
            'expense_id' => $existing?->id,
        ]);
    }

    public function store(Request $request)
    {
        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'expense_type' => 'required|in:project,vendor,general,salary',
            'expense_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'nullable|exists:expense_categories,id',
            'entry_type' => 'required|in:bill,items',
            'items' => 'nullable|array',
            'sub_total' => 'required|numeric|min:1|max:999999999',
            'grand_total' => 'required|numeric|min:1|max:999999999',
            'payment_status' => 'nullable|in:unpaid,paid',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'bill_description' => 'nullable|string|max:150',
        ], [
            'sub_total.min' => 'Sub Total cannot be 0.',
            'grand_total.min' => 'Grand Total cannot be 0.',
            'attachment.mimes' => 'Attachment must be PDF, JPG, or PNG.',
            'attachment.max' => 'Attachment must not exceed 2MB.',
        ]);

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        $attachmentPath = null;
        if ($request->entry_type === 'bill' && $request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses', 'public');
        }

        $items = $request->entry_type === 'items' ? ($validated['items'] ?? null) : null;
        $description = $request->entry_type === 'bill' ? ($validated['bill_description'] ?? null) : null;

        $paymentStatus = $validated['payment_status'] ?? 'unpaid';
        $paidAmount = $paymentStatus === 'paid' ? $validated['grand_total'] : 0;

        $expense = Expense::create([
            'expense_type' => $validated['expense_type'],
            'expense_date' => $validated['expense_date'],
            'payment_mode_id' => $validated['payment_mode_id'],
            'bank_id' => $validated['bank_id'],
            'vendor_id' => $validated['vendor_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'items' => $items,
            'sub_total' => $validated['sub_total'],
            'gst_percentage' => 0,
            'gst_amount' => 0,
            'grand_total' => $validated['grand_total'],
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'attachment' => $attachmentPath,
            'description' => $description,
        ]);

        if ($request->filled('project_id')) {
            return redirect()->route('admin.projects.show', $request->project_id)
                ->with('success', 'Expense added successfully.');
        }

        if ($request->filled('vendor_id')) {
            return redirect()->route('admin.vendors.show', $request->vendor_id)
                ->with('success', 'Expense added successfully.');
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    public function show(Request $request, Expense $expense)
    {
        $expense->load(['vendor', 'project', 'category', 'bank']);
        $fromProject = $request->from_project;
        return view('admin.expenses.show', compact('expense', 'fromProject'));
    }

    public function edit(Request $request, Expense $expense)
    {
        if ($expense->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Paid expenses cannot be edited.');
        }

        $vendors = Vendor::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $categories = ExpenseCategory::orderBy('name')->get();
        $expenseTypes = ExpenseType::active()->get();
        $paymentModes = PaymentMode::active()->get();
        $units = Unit::active()->get();
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $fromProject = $request->from_project;

        // Build project-vendor mapping for vendor payment filtering
        $projectVendorMap = $projects->mapWithKeys(function ($project) {
            return [$project->id => $project->assigned_vendor_ids ?? []];
        });

        return view('admin.expenses.edit', compact('expense', 'vendors', 'projects', 'categories', 'expenseTypes', 'paymentModes', 'units', 'banks', 'cashAccount', 'fromProject', 'projectVendorMap'));
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Paid expenses cannot be updated.');
        }

        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'expense_type' => 'required|in:project,vendor,general,salary',
            'expense_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'nullable|exists:expense_categories,id',
            'entry_type' => 'required|in:bill,items',
            'items' => 'nullable|array',
            'sub_total' => 'required|numeric|min:1|max:999999999',
            'grand_total' => 'required|numeric|min:1|max:999999999',
            'payment_status' => 'nullable|in:unpaid,paid',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'bill_description' => 'nullable|string|max:150',
        ], [
            'sub_total.min' => 'Sub Total cannot be 0.',
            'grand_total.min' => 'Grand Total cannot be 0.',
            'attachment.mimes' => 'Attachment must be PDF, JPG, or PNG.',
            'attachment.max' => 'Attachment must not exceed 2MB.',
        ]);

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        $attachmentPath = $expense->attachment;
        if ($request->entry_type === 'bill') {
            if ($request->hasFile('attachment')) {
                if ($expense->attachment) {
                    \Storage::disk('public')->delete($expense->attachment);
                }
                $attachmentPath = $request->file('attachment')->store('expenses', 'public');
            }
        } else {
            if ($expense->attachment) {
                \Storage::disk('public')->delete($expense->attachment);
            }
            $attachmentPath = null;
        }

        $items = $request->entry_type === 'items' ? ($validated['items'] ?? null) : null;
        $description = $request->entry_type === 'bill' ? ($validated['bill_description'] ?? null) : null;

        $paymentStatus = $validated['payment_status'] ?? $expense->payment_status;
        $paidAmount = $paymentStatus === 'paid' ? $validated['grand_total'] : 0;

        $expense->update([
            'expense_type' => $validated['expense_type'],
            'expense_date' => $validated['expense_date'],
            'payment_mode_id' => $validated['payment_mode_id'],
            'bank_id' => $validated['bank_id'],
            'vendor_id' => $validated['vendor_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'items' => $items,
            'sub_total' => $validated['sub_total'],
            'gst_percentage' => 0,
            'gst_amount' => 0,
            'grand_total' => $validated['grand_total'],
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'attachment' => $attachmentPath,
            'description' => $description,
        ]);

        if ($request->filled('from_project')) {
            return redirect()->route('admin.projects.show', $request->from_project)
                ->with('success', 'Expense updated successfully.');
        }

        if ($expense->project_id) {
            return redirect()->route('admin.projects.show', $expense->project_id)
                ->with('success', 'Expense updated successfully.');
        }

        if ($expense->vendor_id) {
            return redirect()->route('admin.vendors.show', $expense->vendor_id)
                ->with('success', 'Expense updated successfully.');
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $projectId = $expense->project_id;
        $vendorId = $expense->vendor_id;
        $fromProject = $request->from_project;
        $expense->delete();

        if ($fromProject) {
            return redirect()->route('admin.projects.show', $fromProject)
                ->with('success', 'Expense deleted successfully.');
        }

        if ($projectId) {
            return redirect()->route('admin.projects.show', $projectId)
                ->with('success', 'Expense deleted successfully.');
        }

        if ($vendorId) {
            return redirect()->route('admin.vendors.show', $vendorId)
                ->with('success', 'Expense deleted successfully.');
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function export(Request $request)
    {
        $fyDates = getFinancialYearDates();
        $fyLabel = $fyDates ? $fyDates['start']->format('Y') . '_' . $fyDates['end']->format('Y') : 'all';
        $filename = 'expenses_FY_' . $fyLabel . '_' . now()->format('d_m_Y_His') . '.xlsx';
        return (new ExpenseExport($request))->download($filename);
    }
}
