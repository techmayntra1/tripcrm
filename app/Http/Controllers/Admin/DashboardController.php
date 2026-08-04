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
use App\Models\Project;
use App\Models\Vendor;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\LeadUpdate;
use App\Models\ProjectService;
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

        $incomeQuery = Income::query();
        $expenseQuery = Expense::query();
        $invoiceQuery = Invoice::query();
        $projectQuery = Project::query();

        if ($fyDates) {
            $incomeQuery->whereBetween('income_date', [$fyDates['start'], $fyDates['end']]);
            $expenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
            $invoiceQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
            $projectQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }

        $totalIncome = (clone $incomeQuery)->sum('amount');
        $totalExpenses = (clone $expenseQuery)->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');
        $netProfit = $totalIncome - $totalExpenses;
        $receivable = (clone $invoiceQuery)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance_due');

        $activeProjects = (clone $projectQuery)->count();
        $totalCustomers = Customer::count();
        $staffSalary = (clone $expenseQuery)->where('expense_type', 'salary')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');

        $vendorExpenseQuery = Expense::query();
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
        $projectServicesWithBalance = ProjectService::with(['addons', 'serviceExpenses'])->get();
        foreach ($projectServicesWithBalance as $ps) {
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

        $upcomingTasks = Task::with(['project', 'status'])
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

        $projectDeadlines = Project::with('customer')
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

        $banks = Bank::orderByDesc('opening_balance')->limit(3)->get();

        $incomeByType = [
            'project' => (clone $incomeQuery)->where('income_type', 'project')->sum('amount'),
            'advance' => (clone $incomeQuery)->where('income_type', 'advance')->sum('amount'),
            'other' => (clone $incomeQuery)->where('income_type', 'other')->sum('amount'),
        ];

        $expenseByTypeQuery = Expense::query();
        if ($fyDates) {
            $expenseByTypeQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
        }

        $expenseByType = [
            'project' => (clone $expenseByTypeQuery)->where('expense_type', 'project')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'salary' => (clone $expenseByTypeQuery)->where('expense_type', 'salary')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'vendor' => (clone $expenseByTypeQuery)->where('expense_type', 'vendor')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'general' => (clone $expenseByTypeQuery)->where('expense_type', 'general')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
            'service' => (clone $expenseByTypeQuery)->where('expense_type', 'service')->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount'),
        ];

        // Project Budget vs Expense chart — with project filter + received income (money in).
        $chartProjects = Project::whereNotNull('budget')->where('budget', '>', 0)
            ->orderByDesc('created_at')
            ->get(['id', 'project_number', 'name']);

        $selectedChartProject = request('chart_project');

        $projectsChartQuery = Project::whereNotNull('budget')->where('budget', '>', 0);
        if ($selectedChartProject) {
            $projectsChartQuery->where('id', $selectedChartProject);
        } else {
            if ($fyDates) {
                $projectsChartQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            }
            $projectsChartQuery->limit(5);
        }

        $projectsForChart = $projectsChartQuery->get()->map(function ($project) {
            // Match the project detail page exactly — use the model accessors with no
            // financial-year or payment-status filtering, so the numbers are identical:
            //   budget  = raw project budget
            //   spent   = expenses()->sum('paid_amount')  (all project expenses)
            //   income  = incomes()->sum('amount')        (all received payments)
            return [
                'name' => $project->project_number . ' - ' . \Str::limit($project->name, 20),
                'budget' => round($project->budget / 100000, 2),
                'spent' => round($project->total_spent / 100000, 2),
                'income' => round($project->total_income / 100000, 2),
            ];
        });

        $recentProjectExpenses = Expense::with(['project', 'vendor'])
            ->where('expense_type', 'project')
            ->when($fyDates, fn($q) => $q->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $recentSalaryExpenses = Expense::with('staff')
            ->where('expense_type', 'salary')
            ->when($fyDates, fn($q) => $q->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $recentGeneralExpenses = Expense::with('category')
            ->whereIn('expense_type', ['general', 'vendor', 'service'])
            ->when($fyDates, fn($q) => $q->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'receivable',
            'activeProjects',
            'totalCustomers',
            'staffSalary',
            'vendorPayable',
            'serviceProviderPayable',
            'spDueCount',
            'upcomingMeetings',
            'upcomingTasks',
            'upcomingFollowUps',
            'projectDeadlines',
            'companies',
            'banks',
            'incomeByType',
            'expenseByType',
            'projectsForChart',
            'chartProjects',
            'selectedChartProject',
            'recentProjectExpenses',
            'recentSalaryExpenses',
            'recentGeneralExpenses'
        ));
    }

    protected function subadminDashboard()
    {
        $today = Carbon::today();
        $fyDates = getFinancialYearDates();

        $expenseQuery = Expense::query();
        $projectQuery = Project::query();
        $invoiceQuery = Invoice::query();

        if ($fyDates) {
            $expenseQuery->whereBetween('expense_date', [$fyDates['start'], $fyDates['end']]);
            $projectQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            $invoiceQuery->whereBetween('date', [$fyDates['start'], $fyDates['end']]);
        }

        $totalExpenses = (clone $expenseQuery)->whereIn('payment_status', ['paid', 'partial'])->sum('paid_amount');
        $totalProjects = (clone $projectQuery)->count();
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
        $projectServicesWithBalance2 = ProjectService::with(['addons', 'serviceExpenses'])->get();
        foreach ($projectServicesWithBalance2 as $ps) {
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

        $projectDeadlines = Project::with('customer')
            ->whereNotNull('expected_end_date')
            ->where('expected_end_date', '>=', $today)
            ->orderBy('expected_end_date')
            ->limit(5)
            ->get();

        $pendingTaskStatus = TaskStatus::where('name', 'Pending')->first();
        $inProgressTaskStatus = TaskStatus::where('name', 'In Progress')->first();
        $activeTaskStatusIds = collect([$pendingTaskStatus?->id, $inProgressTaskStatus?->id])->filter()->toArray();

        $upcomingTasks = Task::with(['project', 'status'])
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

        // Project Budget vs Expense chart — with project filter + received income (money in).
        $chartProjects = Project::whereNotNull('budget')->where('budget', '>', 0)
            ->orderByDesc('created_at')
            ->get(['id', 'project_number', 'name']);

        $selectedChartProject = request('chart_project');

        $projectsChartQuery = Project::whereNotNull('budget')->where('budget', '>', 0);
        if ($selectedChartProject) {
            $projectsChartQuery->where('id', $selectedChartProject);
        } else {
            if ($fyDates) {
                $projectsChartQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            }
            $projectsChartQuery->limit(5);
        }

        $projectsForChart = $projectsChartQuery->get()->map(function ($project) {
            // Match the project detail page exactly — use the model accessors with no
            // financial-year or payment-status filtering, so the numbers are identical:
            //   budget  = raw project budget
            //   spent   = expenses()->sum('paid_amount')  (all project expenses)
            //   income  = incomes()->sum('amount')        (all received payments)
            return [
                'name' => $project->project_number . ' - ' . \Str::limit($project->name, 20),
                'budget' => round($project->budget / 100000, 2),
                'spent' => round($project->total_spent / 100000, 2),
                'income' => round($project->total_income / 100000, 2),
            ];
        });

        return view('admin.subadmin-dashboard', compact(
            'totalExpenses',
            'totalProjects',
            'totalCustomers',
            'staffSalary',
            'clientReceivables',
            'vendorPayables',
            'serviceProviderPayables',
            'upcomingMeetings',
            'upcomingTasks',
            'upcomingFollowUps',
            'projectDeadlines',
            'projectsForChart',
            'chartProjects',
            'selectedChartProject'
        ));
    }
}
