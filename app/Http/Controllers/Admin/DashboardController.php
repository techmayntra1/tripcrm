<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Trip;
use App\Models\Vendor;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\LeadUpdate;
use App\Models\TripService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return $this->subadminDashboard();
        }

        $today = Carbon::today();
        $fyDates = getFinancialYearDates();

        // Optional company filter: INR and AED amounts can't be mixed, so the headline
        // numbers (and their currency symbol) are scoped to one company when selected.
        $allCompanies = Company::orderBy('name')->get();
        $dashboardCompany = request('company') ? $allCompanies->firstWhere('id', (int) request('company')) : null;
        $companyBankIds = $dashboardCompany ? $dashboardCompany->banks()->pluck('id')->toArray() : null;

        $scopeByBank = function ($query) use ($companyBankIds) {
            return $companyBankIds === null ? $query : $query->whereIn('bank_id', $companyBankIds);
        };
        $scopeByCompany = function ($query) use ($dashboardCompany) {
            return $dashboardCompany ? $query->where('company_id', $dashboardCompany->id) : $query;
        };

        $incomeQuery = $scopeByBank(Income::query());
        $expenseQuery = $scopeByBank(Expense::query());
        $invoiceQuery = $scopeByCompany(Invoice::query());
        $tripQuery = $scopeByCompany(Trip::query());

        if ($fyDates) {
            $incomeQuery->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
            $expenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
            $invoiceQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
            $tripQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }

        $totalIncome = (clone $incomeQuery)->sum('amount');
        $totalExpenses = (clone $expenseQuery)->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');
        $netProfit = $totalIncome - $totalExpenses;
        $receivable = (clone $invoiceQuery)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance_due');

        $activeTrips = (clone $tripQuery)->count();
        $totalCustomers = Customer::count();
        $staffSalary = (clone $expenseQuery)->where('expense_type', 'salary')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');

        $vendorExpenseQuery = $scopeByBank(Expense::query());
        if ($fyDates) {
            $vendorExpenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }
        $vendorPayable = (clone $vendorExpenseQuery)->where('expense_type', 'vendor')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('grand_total') - (clone $vendorExpenseQuery)->where('expense_type', 'vendor')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('paid_amount');

        $serviceProviderPayable = 0;
        $spDueCount = 0;
        $tripServicesWithBalance = TripService::with(['addons', 'serviceExpenses'])
            ->when($dashboardCompany, fn($q) => $q->whereHas('trip', fn($t) => $t->where('company_id', $dashboardCompany->id)))
            ->get();
        foreach ($tripServicesWithBalance as $ps) {
            $balance = $ps->balance;
            if ($balance > 0) {
                $serviceProviderPayable += $balance;
                $spDueCount++;
            }
        }

        $upcomingMeetings = Meeting::with(['customer', 'purpose'])
            ->where('meeting_at', '>=', $today)
            ->where('meeting_at', '<=', $today->copy()->addDays(7))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('meeting_at')
            ->limit(5)
            ->get();

       
        $pendingTaskStatus = TaskStatus::where('name', 'Pending')->first();
        $inProgressTaskStatus = TaskStatus::where('name', 'In Progress')->first();
        $activeTaskStatusIds = collect([$pendingTaskStatus?->id, $inProgressTaskStatus?->id])->filter()->toArray();

        $upcomingTasks = Task::with(['trip', 'status'])
            ->whereIn('status_id', $activeTaskStatusIds)
            ->where('start_at', '>=', $today)
            ->where('start_at', '<=', $today->copy()->addDays(7))
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        $upcomingFollowUps = LeadUpdate::with(['lead', 'updateType'])
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '>=', $today)
            ->where('follow_up_date', '<=', $today->copy()->addDays(7))
            ->orderBy('follow_up_date')
            ->limit(5)
            ->get();

        $tripDeadlines = Trip::with('customer')
            ->whereNotNull('expected_end_date')
            ->where('expected_end_date', '>=', $today)
            ->orderBy('expected_end_date')
            ->limit(5)
            ->get();

        $companies = Company::with('banks')->get()->map(function ($company) use ($fyDates) {
            $bankIds = $company->banks->pluck('id')->toArray();

            $incomeQ = Income::whereIn('bank_id', $bankIds);
            $expenseQ = Expense::whereIn('bank_id', $bankIds);

            if ($fyDates) {
                $incomeQ->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
                $expenseQ->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
            }

            $income = $incomeQ->sum('amount');
            $expense = $expenseQ->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');

            $company->total_income = $income;
            $company->total_expense = $expense;
            $company->total_profit = $income - $expense;

            return $company;
        });

        $banks = Bank::when($companyBankIds !== null, fn($q) => $q->whereIn('id', $companyBankIds))
            ->orderByDesc('opening_balance')->limit(3)->get();

        $incomeByType = [
            'trip' => (clone $incomeQuery)->where('income_type', 'trip')->sum('amount'),
            'advance' => (clone $incomeQuery)->where('income_type', 'advance')->sum('amount'),
            'other' => (clone $incomeQuery)->where('income_type', 'other')->sum('amount'),
        ];

        $expenseByTypeQuery = $scopeByBank(Expense::query());
        if ($fyDates) {
            $expenseByTypeQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }

        $expenseByType = [
            'trip' => (clone $expenseByTypeQuery)->where('expense_type', 'trip')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'salary' => (clone $expenseByTypeQuery)->where('expense_type', 'salary')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'vendor' => (clone $expenseByTypeQuery)->where('expense_type', 'vendor')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'general' => (clone $expenseByTypeQuery)->where('expense_type', 'general')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'service' => (clone $expenseByTypeQuery)->where('expense_type', 'service')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
        ];

        // Trip Budget vs Expense chart — with trip filter + received income (money in).
        $chartTrips = $scopeByCompany(Trip::whereNotNull('budget')->where('budget', '>', 0))
            ->orderByDesc('created_at')
            ->get(['id', 'trip_number', 'name']);

        $selectedChartTrip = request('chart_trip');

        $tripsChartQuery = $scopeByCompany(Trip::whereNotNull('budget')->where('budget', '>', 0));
        if ($selectedChartTrip) {
            $tripsChartQuery->where('id', $selectedChartTrip);
        } else {
            if ($fyDates) {
                $tripsChartQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            }
            $tripsChartQuery->limit(5);
        }

        $tripsForChart = $tripsChartQuery->get()->map(function ($trip) {
            // Match the trip detail page exactly — use the model accessors with no
            // financial-year or payment-status filtering, so the numbers are identical:
            //   budget  = raw trip budget
            //   spent   = expenses()->sum('paid_amount')  (all trip expenses)
            //   income  = incomes()->sum('amount')        (all received payments)
            return [
                'name' => $trip->trip_number . ' - ' . \Str::limit($trip->name, 20),
                'budget' => round($trip->budget / 100000, 2),
                'spent' => round($trip->total_spent / 100000, 2),
                'income' => round($trip->total_income / 100000, 2),
            ];
        });

        $recentTripExpenses = $scopeByBank(Expense::with(['trip', 'vendor']))
            ->where('expense_type', 'trip')
            ->when($fyDates, fn($q) => $q->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $recentSalaryExpenses = $scopeByBank(Expense::with('staff'))
            ->where('expense_type', 'salary')
            ->when($fyDates, fn($q) => $q->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $recentGeneralExpenses = $scopeByBank(Expense::with('category'))
            ->whereIn('expense_type', ['general', 'vendor', 'service'])
            ->when($fyDates, fn($q) => $q->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'allCompanies',
            'dashboardCompany',
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'receivable',
            'activeTrips',
            'totalCustomers',
            'staffSalary',
            'vendorPayable',
            'serviceProviderPayable',
            'spDueCount',
            'upcomingMeetings',
            'upcomingTasks',
            'upcomingFollowUps',
            'tripDeadlines',
            'companies',
            'banks',
            'incomeByType',
            'expenseByType',
            'tripsForChart',
            'chartTrips',
            'selectedChartTrip',
            'recentTripExpenses',
            'recentSalaryExpenses',
            'recentGeneralExpenses'
        ));
    }

    protected function subadminDashboard()
    {
        $today = Carbon::today();
        $fyDates = getFinancialYearDates();

        $expenseQuery = Expense::query();
        $tripQuery = Trip::query();
        $invoiceQuery = Invoice::query();

        if ($fyDates) {
            $expenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
            $tripQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            $invoiceQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }

        $totalExpenses = (clone $expenseQuery)->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');
        $totalTrips = (clone $tripQuery)->count();
        $totalCustomers = Customer::count();
        $staffSalary = (clone $expenseQuery)->where('expense_type', 'salary')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');
        $clientReceivables = (clone $invoiceQuery)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance_due');

        $vendorExpenseQuery = Expense::query();
        if ($fyDates) {
            $vendorExpenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }
        $vendorPayables = (clone $vendorExpenseQuery)->where('expense_type', 'vendor')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('grand_total') - (clone $vendorExpenseQuery)->where('expense_type', 'vendor')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('paid_amount');

        $serviceProviderPayables = 0;
        $tripServicesWithBalance2 = TripService::with(['addons', 'serviceExpenses'])->get();
        foreach ($tripServicesWithBalance2 as $ps) {
            $balance = $ps->balance;
            if ($balance > 0) {
                $serviceProviderPayables += $balance;
            }
        }

        $upcomingMeetings = Meeting::with(['customer', 'lead', 'purpose'])
            ->where('meeting_at', '>=', $today)
            ->where('meeting_at', '<=', $today->copy()->addDays(7))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('meeting_at')
            ->limit(5)
            ->get();

        $tripDeadlines = Trip::with('customer')
            ->whereNotNull('expected_end_date')
            ->where('expected_end_date', '>=', $today)
            ->orderBy('expected_end_date')
            ->limit(5)
            ->get();

        $pendingTaskStatus = TaskStatus::where('name', 'Pending')->first();
        $inProgressTaskStatus = TaskStatus::where('name', 'In Progress')->first();
        $activeTaskStatusIds = collect([$pendingTaskStatus?->id, $inProgressTaskStatus?->id])->filter()->toArray();

        $upcomingTasks = Task::with(['trip', 'status'])
            ->whereIn('status_id', $activeTaskStatusIds)
            ->where('start_at', '>=', $today)
            ->where('start_at', '<=', $today->copy()->addDays(7))
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        $upcomingFollowUps = LeadUpdate::with(['lead', 'updateType'])
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '>=', $today)
            ->where('follow_up_date', '<=', $today->copy()->addDays(7))
            ->orderBy('follow_up_date')
            ->limit(5)
            ->get();

        // Trip Budget vs Expense chart — with trip filter + received income (money in).
        $chartTrips = Trip::whereNotNull('budget')->where('budget', '>', 0)
            ->orderByDesc('created_at')
            ->get(['id', 'trip_number', 'name']);

        $selectedChartTrip = request('chart_trip');

        $tripsChartQuery = Trip::whereNotNull('budget')->where('budget', '>', 0);
        if ($selectedChartTrip) {
            $tripsChartQuery->where('id', $selectedChartTrip);
        } else {
            if ($fyDates) {
                $tripsChartQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            }
            $tripsChartQuery->limit(5);
        }

        $tripsForChart = $tripsChartQuery->get()->map(function ($trip) {
            // Match the trip detail page exactly — use the model accessors with no
            // financial-year or payment-status filtering, so the numbers are identical:
            //   budget  = raw trip budget
            //   spent   = expenses()->sum('paid_amount')  (all trip expenses)
            //   income  = incomes()->sum('amount')        (all received payments)
            return [
                'name' => $trip->trip_number . ' - ' . \Str::limit($trip->name, 20),
                'budget' => round($trip->budget / 100000, 2),
                'spent' => round($trip->total_spent / 100000, 2),
                'income' => round($trip->total_income / 100000, 2),
            ];
        });

        return view('admin.subadmin-dashboard', compact(
            'totalExpenses',
            'totalTrips',
            'totalCustomers',
            'staffSalary',
            'clientReceivables',
            'vendorPayables',
            'serviceProviderPayables',
            'upcomingMeetings',
            'upcomingTasks',
            'upcomingFollowUps',
            'tripDeadlines',
            'tripsForChart',
            'chartTrips',
            'selectedChartTrip'
        ));
    }
}
