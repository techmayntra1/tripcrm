<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMode;
use App\Models\SalaryAdvance;
use App\Models\SalaryAdvanceDeduction;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryPaymentController extends Controller
{
    public function index(Request $request, Staff $staff)
    {
        $perPage = $request->input('per_page', 15);
        $salaryPayments = Expense::where('staff_id', $staff->id)
            ->where('expense_type', 'salary')
            ->orderBy('expense_date', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $advances = $staff->salaryAdvances()->orderBy('advance_date', 'desc')->get();

        $totalPaidThisYear = Expense::where('staff_id', $staff->id)
            ->where('expense_type', 'salary')
            ->whereYear('expense_date', now()->year)
            ->sum('grand_total');

        $totalAdvanceGiven = $staff->salaryAdvances()->sum('amount');
        $totalAdvanceRecovered = $staff->salaryAdvances()->sum('amount') - $staff->salaryAdvances()->sum('remaining_amount');
        $pendingAdvance = $staff->total_pending_advance;

        return view('admin.staff.salary-payments.index', compact(
            'staff',
            'salaryPayments',
            'advances',
            'totalPaidThisYear',
            'totalAdvanceGiven',
            'totalAdvanceRecovered',
            'pendingAdvance'
        ));
    }

    public function create(Staff $staff)
    {
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $pendingAdvances = $staff->activeAdvances()->get();
        $totalPendingAdvance = $staff->total_pending_advance;
        $paymentModes = PaymentMode::active()->get();

        return view('admin.staff.salary-payments.create', compact(
            'staff',
            'banks',
            'cashAccount',
            'pendingAdvances',
            'totalPendingAdvance',
            'paymentModes'
        ));
    }

    public function store(Request $request, Staff $staff)
    {
        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'base_salary' => 'required|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $baseSalary = $validated['base_salary'];
        $overtimeAmount = $validated['overtime_amount'] ?? 0;
        $bonus = $validated['bonus'] ?? 0;
        $advanceDeduction = $validated['advance_deduction'] ?? 0;

        $totalPendingAdvance = $staff->total_pending_advance;
        if ($advanceDeduction > $totalPendingAdvance) {
            return back()->withErrors(['advance_deduction' => "Advance deduction cannot exceed pending advance of ₹{$totalPendingAdvance}"])->withInput();
        }

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        $grossSalary = $baseSalary + $overtimeAmount + $bonus;
        $netSalary = $grossSalary - $advanceDeduction;

        if ($netSalary < 0) {
            return back()->withErrors(['base_salary' => 'Net salary cannot be negative'])->withInput();
        }

        DB::beginTransaction();

        try {
            $salaryCategory = ExpenseCategory::firstOrCreate(
                ['name' => 'Salary'],
                ['description' => 'Staff salary payments']
            );

            $expense = Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'expense_type' => 'salary',
                'expense_date' => $validated['payment_date'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'staff_id' => $staff->id,
                'category_id' => $salaryCategory->id,
                'bank_id' => $validated['bank_id'],
                'items' => json_encode($this->buildSalaryItems($baseSalary, $overtimeAmount, $bonus, $advanceDeduction)),
                'description' => "Salary Payment",
                'sub_total' => $netSalary,
                'gst_percentage' => 0,
                'gst_amount' => 0,
                'grand_total' => $netSalary,
                'paid_amount' => $netSalary,
                'payment_status' => 'paid',
                'notes' => $validated['notes'],
            ]);

            if ($advanceDeduction > 0) {
                $remainingDeduction = $advanceDeduction;
                $activeAdvances = $staff->activeAdvances()->orderBy('advance_date', 'asc')->get();

                foreach ($activeAdvances as $advance) {
                    if ($remainingDeduction <= 0) break;

                    $deducted = $advance->deduct($remainingDeduction, $expense->id);
                    $remainingDeduction -= $deducted;
                }
            }

            DB::commit();

            return redirect()->route('admin.staff.salary-payments.index', $staff)
                ->with('success', 'Salary payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record salary payment: ' . $e->getMessage()])->withInput();
        }
    }

    private function buildSalaryItems($baseSalary, $overtimeAmount, $bonus, $advanceDeduction)
    {
        $items = [];

        $items[] = [
            'description' => 'Base Salary',
            'amount' => $baseSalary,
            'type' => 'addition',
        ];

        if ($overtimeAmount > 0) {
            $items[] = [
                'description' => 'Overtime',
                'amount' => $overtimeAmount,
                'type' => 'addition',
            ];
        }

        if ($bonus > 0) {
            $items[] = [
                'description' => 'Bonus',
                'amount' => $bonus,
                'type' => 'addition',
            ];
        }

        if ($advanceDeduction > 0) {
            $items[] = [
                'description' => 'Advance Deduction',
                'amount' => $advanceDeduction,
                'type' => 'deduction',
            ];
        }

        return $items;
    }

    public function show(Staff $staff, Expense $payment)
    {
        $payment->load('bank');
        $items = json_decode($payment->items, true) ?? [];

        return view('admin.staff.salary-payments.show', compact('staff', 'payment', 'items'));
    }

    public function edit(Staff $staff, Expense $payment)
    {
        if ($payment->staff_id !== $staff->id || $payment->expense_type !== 'salary') {
            abort(404);
        }

        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $paymentModes = PaymentMode::active()->get();

        $items = is_array($payment->items) ? $payment->items : (json_decode($payment->items, true) ?? []);
        $existingDeduction = (float) collect($items)->where('description', 'Advance Deduction')->sum('amount');
        $baseSalary = (float) collect($items)->where('description', 'Base Salary')->sum('amount');
        $overtimeAmount = (float) collect($items)->where('description', 'Overtime')->sum('amount');
        $bonus = (float) collect($items)->where('description', 'Bonus')->sum('amount');

        // Pending advance available now PLUS what this payment already recovered (allowed to re-allocate)
        $totalPendingAdvance = $staff->total_pending_advance + $existingDeduction;
        $pendingAdvances = $staff->activeAdvances()->get();

        return view('admin.staff.salary-payments.edit', compact(
            'staff',
            'payment',
            'banks',
            'cashAccount',
            'paymentModes',
            'totalPendingAdvance',
            'pendingAdvances',
            'baseSalary',
            'overtimeAmount',
            'bonus',
            'existingDeduction'
        ));
    }

    public function update(Request $request, Staff $staff, Expense $payment)
    {
        if ($payment->staff_id !== $staff->id || $payment->expense_type !== 'salary') {
            abort(404);
        }

        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'base_salary' => 'required|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $baseSalary = $validated['base_salary'];
        $overtimeAmount = $validated['overtime_amount'] ?? 0;
        $bonus = $validated['bonus'] ?? 0;
        $advanceDeduction = $validated['advance_deduction'] ?? 0;

        // Reverse any existing advance deductions tied to this payment first
        $existingRecovered = $this->reverseAdvanceDeductions($payment);

        $totalPendingAdvance = $staff->fresh()->total_pending_advance;
        if ($advanceDeduction > $totalPendingAdvance) {
            // Re-apply the previously recovered deductions so we don't lose state
            $this->reapplyAdvanceDeductions($staff, $existingRecovered, $payment->id);
            return back()->withErrors(['advance_deduction' => "Advance deduction cannot exceed pending advance of ₹{$totalPendingAdvance}"])->withInput();
        }

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        $grossSalary = $baseSalary + $overtimeAmount + $bonus;
        $netSalary = $grossSalary - $advanceDeduction;

        if ($netSalary < 0) {
            $this->reapplyAdvanceDeductions($staff, $existingRecovered, $payment->id);
            return back()->withErrors(['base_salary' => 'Net salary cannot be negative'])->withInput();
        }

        DB::beginTransaction();

        try {
            $payment->update([
                'expense_date' => $validated['payment_date'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'bank_id' => $validated['bank_id'],
                'items' => json_encode($this->buildSalaryItems($baseSalary, $overtimeAmount, $bonus, $advanceDeduction)),
                'sub_total' => $netSalary,
                'grand_total' => $netSalary,
                'paid_amount' => $netSalary,
                'payment_status' => 'paid',
                'notes' => $validated['notes'],
            ]);

            if ($advanceDeduction > 0) {
                $remainingDeduction = $advanceDeduction;
                $activeAdvances = $staff->activeAdvances()->orderBy('advance_date', 'asc')->get();

                foreach ($activeAdvances as $advance) {
                    if ($remainingDeduction <= 0) break;

                    $deducted = $advance->deduct($remainingDeduction, $payment->id);
                    $remainingDeduction -= $deducted;
                }
            }

            DB::commit();

            return redirect()->route('admin.staff.salary-payments.index', $staff)
                ->with('success', 'Salary payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update salary payment: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Staff $staff, Expense $payment)
    {
        if ($payment->staff_id !== $staff->id || $payment->expense_type !== 'salary') {
            abort(404);
        }

        DB::beginTransaction();
        try {
            $this->reverseAdvanceDeductions($payment);
            $payment->delete();
            DB::commit();

            return redirect()->route('admin.staff.salary-payments.index', $staff)
                ->with('success', 'Salary payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete salary payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Reverse all advance deductions linked to this expense.
     * Returns array of [advance_id => recovered_amount] so caller can re-apply on validation failure.
     */
    private function reverseAdvanceDeductions(Expense $payment): array
    {
        $deductions = SalaryAdvanceDeduction::where('expense_id', $payment->id)->get();
        $recovered = [];

        foreach ($deductions as $deduction) {
            $advance = $deduction->salaryAdvance;
            if ($advance) {
                $advance->remaining_amount += $deduction->deduction_amount;
                if ($advance->remaining_amount > 0 && $advance->status === 'completed') {
                    $advance->status = 'active';
                }
                $advance->save();
                $recovered[$advance->id] = ($recovered[$advance->id] ?? 0) + $deduction->deduction_amount;
            }
            $deduction->delete();
        }

        return $recovered;
    }

    private function reapplyAdvanceDeductions(Staff $staff, array $recovered, int $expenseId): void
    {
        foreach ($recovered as $advanceId => $amount) {
            $advance = SalaryAdvance::find($advanceId);
            if ($advance) {
                $advance->deduct($amount, $expenseId);
            }
        }
    }

    public function createAdvance(Staff $staff)
    {
        $banks = Bank::where('is_protected', false)->orderBy('bank_name')->get();
        $cashAccount = Bank::where('is_protected', true)->first();
        $paymentModes = PaymentMode::active()->get();

        return view('admin.staff.salary-payments.create-advance', compact('staff', 'banks', 'cashAccount', 'paymentModes'));
    }

    public function storeAdvance(Request $request, Staff $staff)
    {
        $paymentMode = PaymentMode::find($request->payment_mode_id);
        $isCash = $paymentMode && $paymentMode->slug === 'cash';

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'advance_date' => 'required|date',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'bank_id' => $isCash ? 'nullable|exists:banks,id' : 'required|exists:banks,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($isCash && empty($validated['bank_id'])) {
            $cashAccount = Bank::where('is_protected', true)->first();
            $validated['bank_id'] = $cashAccount?->id;
        }

        DB::beginTransaction();

        try {
            $advance = SalaryAdvance::create([
                'staff_id' => $staff->id,
                'amount' => $validated['amount'],
                'remaining_amount' => $validated['amount'],
                'advance_date' => $validated['advance_date'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'bank_id' => $validated['bank_id'],
                'reason' => $validated['reason'],
                'status' => 'active',
            ]);

            $advanceCategory = ExpenseCategory::firstOrCreate(
                ['name' => 'Salary Advance'],
                ['description' => 'Advance payments to staff']
            );

            Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'expense_type' => 'salary',
                'expense_date' => $validated['advance_date'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'staff_id' => $staff->id,
                'category_id' => $advanceCategory->id,
                'bank_id' => $validated['bank_id'],
                'items' => json_encode([
                    ['description' => 'Salary Advance', 'amount' => $validated['amount'], 'type' => 'advance']
                ]),
                'description' => "Salary Advance",
                'sub_total' => $validated['amount'],
                'gst_percentage' => 0,
                'gst_amount' => 0,
                'grand_total' => $validated['amount'],
                'paid_amount' => $validated['amount'],
                'payment_status' => 'paid',
                'notes' => $validated['reason'],
            ]);

            DB::commit();

            return redirect()->route('admin.staff.salary-payments.index', $staff)
                ->with('success', 'Advance payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record advance: ' . $e->getMessage()])->withInput();
        }
    }

    public function showAdvance(Staff $staff, SalaryAdvance $advance)
    {
        $advance->load(['bank', 'deductions.expense']);

        return view('admin.staff.salary-payments.show-advance', compact('staff', 'advance'));
    }
}
