<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $query = Bank::with('company')->ordered();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('account_holder', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('ifsc_code', 'like', "%{$search}%")
                  ->orWhere('iban', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $banks = $query->paginate($perPage)->withQueryString();
        $inactiveCount = Bank::onlyTrashed()->count();

        return view('admin.banks.index', compact('banks', 'inactiveCount'));
    }

    public function trashed(Request $request)
    {
        $query = Bank::onlyTrashed()->ordered();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('account_holder', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $banks = $query->paginate($perPage)->withQueryString();
        $activeCount = Bank::count();

        return view('admin.banks.trashed', compact('banks', 'activeCount'));
    }

    public function restore($id)
    {
        $bank = Bank::onlyTrashed()->findOrFail($id);
        $bank->restore();

        return redirect()->route('admin.banks.trashed')
            ->with('success', 'Bank account restored successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type' => 'required|in:savings,current,recurring',
            'bank_name' => 'required|string|max:40',
            'country' => 'required|in:india,uae',
            'account_holder' => 'nullable|string|max:40',
            'account_number' => 'required|string|max:20',
            'ifsc_code' => 'nullable|string|max:11',
            'iban' => 'nullable|string|max:34',
            'branch' => 'nullable|string|max:30',
            'opening_balance' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated = $this->clearCodeForOtherRegion($validated);

        $maxSortOrder = Bank::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSortOrder + 1;

        Bank::create($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank account added successfully.');
    }

    // Indian accounts carry an IFSC, UAE accounts an IBAN — never both.
    private function clearCodeForOtherRegion(array $data): array
    {
        if (($data['country'] ?? 'india') === 'uae') {
            $data['ifsc_code'] = null;
        } else {
            $data['iban'] = null;
        }

        return $data;
    }

    public function show(Request $request, Bank $bank)
    {
        $fyDates = getFinancialYearDates();

        // The bank statement is derived purely from Income (credits) and
        // Expense (debits) tied to this bank. There is no separate ledger.
        $incomeQuery = $bank->incomes()->where('amount', '>', 0);
        if ($fyDates) {
            $incomeQuery->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
        }

        $expenseQuery = $bank->expenses()->where('paid_amount', '>', 0);
        if ($fyDates) {
            $expenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }

        $credits = $incomeQuery->get()->map(function ($income) {
            return (object) [
                'id' => $income->id,
                'type' => 'credit',
                'amount' => (float) $income->amount,
                'transaction_date' => $income->income_date,
                'description' => $income->description ?: $income->income_type_display,
                'source' => 'income',
                'reference' => $income->receipt_number,
            ];
        });

        $debits = $expenseQuery->get()->map(function ($expense) {
            return (object) [
                'id' => $expense->id,
                'type' => 'debit',
                'amount' => (float) $expense->paid_amount,
                'transaction_date' => $expense->expense_date,
                'description' => $expense->description ?: $expense->expense_type_display,
                'source' => 'expense',
                'reference' => $expense->expense_number,
            ];
        });

        $transactions = (new Collection())
            ->concat($credits)
            ->concat($debits)
            ->sortByDesc(fn($t) => [optional($t->transaction_date)->format('Y-m-d'), $t->id])
            ->values();

        $activeCount = $transactions->count();

        return view('admin.banks.show', compact('bank', 'transactions', 'activeCount'));
    }

    public function update(Request $request, Bank $bank)
    {
        if ($bank->is_protected) {
            $validated = $request->validate([
                'opening_balance' => 'nullable|numeric|min:0|max:99999999.99',
            ]);
            $bank->update(['opening_balance' => $validated['opening_balance'] ?? 0]);

            return redirect()->route('admin.banks.index')
                ->with('success', 'Cash account updated successfully.');
        }

        $validated = $request->validate([
            'account_type' => 'required|in:savings,current,recurring',
            'bank_name' => 'required|string|max:40',
            'country' => 'required|in:india,uae',
            'account_holder' => 'nullable|string|max:40',
            'account_number' => 'required|string|max:20',
            'ifsc_code' => 'nullable|string|max:11',
            'iban' => 'nullable|string|max:34',
            'branch' => 'nullable|string|max:30',
            'opening_balance' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;

        // A bank assigned to a company always keeps that company's region.
        if ($bank->company_id && $bank->company) {
            $validated['country'] = $bank->company->country;
        }
        $validated = $this->clearCodeForOtherRegion($validated);

        $bank->update($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank account updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        if ($bank->is_protected) {
            return redirect()->route('admin.banks.index')
                ->with('error', 'This bank account cannot be deleted.');
        }

        $bank->delete();

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank account deleted successfully.');
    }

    public function storeTransaction(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        // A manual adjustment is just money in or money out: credit -> Income,
        // debit -> Expense. No separate ledger exists.
        if ($validated['type'] === 'credit') {
            Income::create([
                'income_type' => 'other',
                'income_date' => $validated['transaction_date'],
                'bank_id' => $bank->id,
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?: 'Manual adjustment',
            ]);
        } else {
            Expense::create([
                'expense_type' => 'general',
                'expense_date' => $validated['transaction_date'],
                'bank_id' => $bank->id,
                'sub_total' => $validated['amount'],
                'gst_percentage' => 0,
                'gst_amount' => 0,
                'grand_total' => $validated['amount'],
                'paid_amount' => $validated['amount'],
                'payment_status' => 'paid',
                'description' => $validated['description'] ?: 'Manual adjustment',
            ]);
        }

        return redirect()->route('admin.banks.show', $bank)
            ->with('success', ucfirst($validated['type']) . ' added successfully.');
    }
}
