<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpenseExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseType;
use App\Models\PaymentMode;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\Bank;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['vendor', 'staff', 'trip', 'category', 'bank']);

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
        $query = Expense::onlyTrashed()->with(['vendor', 'staff', 'trip', 'category', 'bank']);

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
        $trips = Trip::orderBy('name')->get();
        $categories = ExpenseCategory::orderBy('name')->get();
        $expenseTypes = ExpenseType::active()->get();
        $paymentModes = PaymentMode::active()->get();
        $units = Unit::active()->get();
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();

        $selectedVendorId = $request->vendor_id;
        $selectedTripId = $request->trip_id;
        $selectedExpenseType = $request->expense_type;

        // Build trip-vendor mapping for vendor payment filtering
        $tripVendorMap = $trips->mapWithKeys(function ($trip) {
            return [$trip->id => $trip->assigned_vendor_ids ?? []];
        });

        return view('admin.expenses.create', compact(
            'vendors',
            'trips',
            'categories',
            'expenseTypes',
            'paymentModes',
            'units',
            'banks',
            'cashAccount',
            'selectedVendorId',
            'selectedTripId',
            'selectedExpenseType',
            'tripVendorMap'
        ));
    }

    public function checkDuplicate(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'trip_id' => 'nullable|integer',
            'vendor_id' => 'nullable|integer',
            'grand_total' => 'required|numeric',
        ]);

        $query = Expense::query()
            ->whereDate('expense_date', $validated['expense_date'])
            ->where('grand_total', $validated['grand_total']);

        if (!empty($validated['trip_id'])) {
            $query->where('trip_id', $validated['trip_id']);
        } else {
            $query->whereNull('trip_id');
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
            'expense_type' => 'required|in:trip,vendor,general,salary',
            'expense_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'trip_id' => 'nullable|exists:trips,id',
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
            'trip_id' => $validated['trip_id'] ?? null,
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

        if ($request->filled('trip_id')) {
            return redirect()->route('admin.trips.show', $request->trip_id)
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
        $expense->load(['vendor', 'trip', 'category', 'bank']);
        $fromTrip = $request->from_trip;
        return view('admin.expenses.show', compact('expense', 'fromTrip'));
    }

    public function edit(Request $request, Expense $expense)
    {
        if ($expense->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Paid expenses cannot be edited.');
        }

        $vendors = Vendor::orderBy('name')->get();
        $trips = Trip::orderBy('name')->get();
        $categories = ExpenseCategory::orderBy('name')->get();
        $expenseTypes = ExpenseType::active()->get();
        $paymentModes = PaymentMode::active()->get();
        $units = Unit::active()->get();
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $fromTrip = $request->from_trip;

        // Build trip-vendor mapping for vendor payment filtering
        $tripVendorMap = $trips->mapWithKeys(function ($trip) {
            return [$trip->id => $trip->assigned_vendor_ids ?? []];
        });

        return view('admin.expenses.edit', compact('expense', 'vendors', 'trips', 'categories', 'expenseTypes', 'paymentModes', 'units', 'banks', 'cashAccount', 'fromTrip', 'tripVendorMap'));
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Paid expenses cannot be updated.');
        }

        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'expense_type' => 'required|in:trip,vendor,general,salary',
            'expense_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'trip_id' => 'nullable|exists:trips,id',
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
            'trip_id' => $validated['trip_id'] ?? null,
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

        if ($request->filled('from_trip')) {
            return redirect()->route('admin.trips.show', $request->from_trip)
                ->with('success', 'Expense updated successfully.');
        }

        if ($expense->trip_id) {
            return redirect()->route('admin.trips.show', $expense->trip_id)
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
        $tripId = $expense->trip_id;
        $vendorId = $expense->vendor_id;
        $fromTrip = $request->from_trip;
        $expense->delete();

        if ($fromTrip) {
            return redirect()->route('admin.trips.show', $fromTrip)
                ->with('success', 'Expense deleted successfully.');
        }

        if ($tripId) {
            return redirect()->route('admin.trips.show', $tripId)
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
        $filename = safeFilename('expenses_FY_' . $fyLabel . '_' . now()->format('d_m_Y_His')) . '.xlsx';
        return (new ExpenseExport($request))->download($filename);
    }
}
