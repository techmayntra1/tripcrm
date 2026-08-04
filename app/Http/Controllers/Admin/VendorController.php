<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Expense;
use App\Models\Task;
use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $categoryId = $request->category;
            $query->whereJsonContains('categories', (int) $categoryId);
        }

        $perPage = $request->input('per_page', 15);
        $vendors = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        $categories = VendorCategory::active()->ordered()->get();
        $trashedCount = Vendor::onlyTrashed()->count();

        return view('admin.vendors.index', compact('vendors', 'categories', 'trashedCount'));
    }

    public function trashed(Request $request)
    {
        $query = Vendor::onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $categoryId = $request->category;
            $query->whereJsonContains('categories', (int) $categoryId);
        }

        $perPage = $request->input('per_page', 15);
        $vendors = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();
        $categories = VendorCategory::active()->ordered()->get();
        $activeCount = Vendor::count();

        return view('admin.vendors.trashed', compact('vendors', 'categories', 'activeCount'));
    }

    public function restore($id)
    {
        $vendor = Vendor::onlyTrashed()->findOrFail($id);
        $vendor->restore();

        return redirect()->route('admin.vendors.trashed')->with('success', 'Vendor restored successfully.');
    }

    public function showTrashed($id)
    {
        $vendor = Vendor::onlyTrashed()->findOrFail($id);
        $vendor->load('expenses');
        $banks = collect();

        $tasks = Task::where('assignee_type', 'vendor')
            ->where('assignee_id', $vendor->id)
            ->whereNull('deleted_at')
            ->with(['project', 'status'])
            ->orderBy('due_at')
            ->get();

        return view('admin.vendors.show', compact('vendor', 'banks', 'tasks'));
    }

    public function create()
    {
        $categories = VendorCategory::active()->ordered()->get();
        return view('admin.vendors.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:30',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:vendor_categories,id',
            'contact_person' => 'nullable|string|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
            'opening_balance' => 'nullable|numeric|min:0|max:999999999',
            'address' => 'nullable|string|max:500',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:20',
            'ifsc_code' => 'nullable|string|max:11',
            'notes' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Vendor name is required.',
            'name.min' => 'Vendor name must be at least 2 characters.',
            'categories.required' => 'Please select at least one category.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'gst_number.max' => 'GST number must not exceed 15 characters.',
            'pan_number.max' => 'PAN number must not exceed 10 characters.',
            'ifsc_code.max' => 'IFSC code must not exceed 11 characters.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $validated['categories'] = array_map('intval', $validated['categories']);
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;

        Vendor::create($validated);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load('expenses');
        $banks = Bank::orderBy('bank_name')->get();

        $tasks = Task::where('assignee_type', 'vendor')
            ->where('assignee_id', $vendor->id)
            ->whereNull('deleted_at')
            ->with(['project', 'status'])
            ->orderBy('due_at')
            ->get();

        return view('admin.vendors.show', compact('vendor', 'banks', 'tasks'));
    }

    public function edit(Vendor $vendor)
    {
        $categories = VendorCategory::active()->ordered()->get();
        return view('admin.vendors.edit', compact('vendor', 'categories'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:30',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:vendor_categories,id',
            'contact_person' => 'nullable|string|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:20',
            'ifsc_code' => 'nullable|string|max:11',
            'notes' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Vendor name is required.',
            'name.min' => 'Vendor name must be at least 2 characters.',
            'categories.required' => 'Please select at least one category.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'gst_number.max' => 'GST number must not exceed 15 characters.',
            'pan_number.max' => 'PAN number must not exceed 10 characters.',
            'ifsc_code.max' => 'IFSC code must not exceed 11 characters.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $validated['categories'] = array_map('intval', $validated['categories']);

        $vendor->update($validated);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    public function recordPayment(Request $request, Vendor $vendor, Expense $expense)
    {
        if ($expense->vendor_id !== $vendor->id) {
            return redirect()->back()->with('error', 'Invalid expense.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $expense->balance,
            'bank_id' => 'required|exists:banks,id',
            'payment_date' => 'nullable|date',
        ]);

        $paymentAmount = $validated['amount'];
        $paymentDate = $validated['payment_date'] ?? now()->toDateString();

        $expense->paid_amount += $paymentAmount;
        $expense->bank_id = $validated['bank_id'];
        $expense->updatePaymentStatus();

        return redirect()->route('admin.vendors.show', $vendor)->with('success', 'Payment of ' . formatMoney($paymentAmount) . ' recorded successfully.');
    }

    public function markFullyPaid(Request $request, Vendor $vendor, Expense $expense)
    {
        if ($expense->vendor_id !== $vendor->id) {
            return redirect()->back()->with('error', 'Invalid expense.');
        }

        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
        ]);

        $remainingBalance = $expense->balance;

        if ($remainingBalance <= 0) {
            return redirect()->back()->with('error', 'Expense is already fully paid.');
        }

        $expense->paid_amount = $expense->grand_total;
        $expense->bank_id = $validated['bank_id'];
        $expense->updatePaymentStatus();

        return redirect()->route('admin.vendors.show', $vendor)->with('success', 'Expense marked as fully paid. ' . formatMoney($remainingBalance) . ' debited.');
    }
}
