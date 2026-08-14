@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-speedometer2 icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Dashboard
            </div>
        </div>
    </div>
</div>

<div class="summary-row mb-3">
    <div class="summary-item bg-success text-white" data-bs-toggle="tooltip" title="{{ formatMoney($totalIncome) }}">
        <span class="summary-label">Income</span>
        <span class="summary-value">{{ formatMoney($totalIncome) }}</span>
    </div>
    <div class="summary-item bg-danger text-white" data-bs-toggle="tooltip" title="{{ formatMoney($totalExpenses) }}">
        <span class="summary-label">Expenses</span>
        <span class="summary-value">{{ formatMoney($totalExpenses) }}</span>
    </div>
    <div class="summary-item bg-success text-white" data-bs-toggle="tooltip" title="{{ formatMoney($netProfit) }}">
        <span class="summary-label">Net Profit</span>
        <span class="summary-value">{{ formatMoney($netProfit) }}</span>
    </div>
    <div class="summary-item bg-dark text-white" data-bs-toggle="tooltip" title="{{ formatMoney($receivable) }}">
        <span class="summary-label">Receivable</span>
        <span class="summary-value">{{ formatMoney($receivable) }}</span>
    </div>
</div>
<div class="summary-row mb-3">
    <div class="summary-item bg-midnight-bloom text-white" data-bs-toggle="tooltip" title="{{ $activeTrips }}">
        <span class="summary-label">Trips</span>
        <span class="summary-value">{{ $activeTrips }}</span>
    </div>
    <div class="summary-item bg-info text-white" data-bs-toggle="tooltip" title="{{ $totalCustomers }}">
        <span class="summary-label">Customers</span>
        <span class="summary-value">{{ $totalCustomers }}</span>
    </div>
    <div class="summary-item bg-secondary text-white" data-bs-toggle="tooltip" title="{{ formatMoney($staffSalary) }}">
        <span class="summary-label">Salary</span>
        <span class="summary-value">{{ formatMoney($staffSalary) }}</span>
    </div>
    <div class="summary-item bg-danger text-white" data-bs-toggle="tooltip" title="{{ formatMoney($vendorPayable) }}">
        <span class="summary-label">Vendor Due</span>
        <span class="summary-value">{{ formatMoney($vendorPayable) }}</span>
    </div>
    <div class="summary-item bg-purple text-white" data-bs-toggle="tooltip" title="{{ formatMoney($serviceProviderPayable) }}">
        <span class="summary-label">Service Due</span>
        <span class="summary-value">{{ formatMoney($serviceProviderPayable) }}</span>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2"></i> Upcoming Meetings</span>
                <a href="{{ route('admin.meetings.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: #fff !important;"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($upcomingMeetings as $meeting)
                <a href="{{ route('admin.meetings.show', $meeting) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-truncate" style="max-width: 150px;">
                            <strong>
                                {{
                                    $meeting->customer->name
                                    ?? $meeting->lead->name
                                    ?? $meeting->other_attendee
                                    ?? 'N/A'
                                }}
                            </strong>
                        </span>
                        @php
                            $meetingAt = $meeting->meeting_at;
                            $isToday = $meetingAt->isToday();
                            $isTomorrow = $meetingAt->isTomorrow();
                            $badgeClass = $isToday ? 'bg-warning' : ($isTomorrow ? 'bg-info' : 'bg-secondary');
                            $dateLabel = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $meetingAt->format('d-m'));
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $dateLabel }} {{ $meetingAt->format('h:i A') }}</span>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-center text-muted py-3">
                    <i class="bi bi-calendar-x fs-4 d-block mb-2"></i>
                    No upcoming meetings
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning">
                <span><i class="bi bi-list-task me-2" style="color: #fff !important;"></i> Upcoming Tasks</span>
                <a href="{{ route('admin.tasks.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: #fff !important;"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($upcomingTasks as $task)
                <a href="{{ route('admin.tasks.show', $task) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate" style="max-width: 150px;">
                            <strong>{{ $task->title }}</strong>
                            <br><small class="text-muted">{{ $task->assignee_name }}</small>
                        </div>
                        <div class="text-end">
                            @php
                                $startAt = $task->start_at;
                                $isToday = $startAt->isToday();
                                $isTomorrow = $startAt->isTomorrow();
                                $badgeClass = $isToday ? 'bg-warning' : ($isTomorrow ? 'bg-info' : 'bg-secondary');
                                $dateLabel = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $startAt->format('d-m'));
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $dateLabel }}</span>
                            <br><small class="text-muted">{{ $startAt->format('h:i A') }}</small>
                        </div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-center text-muted py-3">
                    <i class="bi bi-list-task fs-4 d-block mb-2"></i>
                    No upcoming tasks
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                <span><i class="bi bi-telephone-forward me-2"></i> Upcoming Follow-ups</span>
                <a href="{{ route('admin.follow-ups.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: #fff !important;"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($upcomingFollowUps as $followUp)
                <a href="{{ route('admin.leads.show', $followUp->lead_id) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate" style="max-width: 120px;">
                            <strong>{{ $followUp->lead->name ?? 'N/A' }}</strong>
                            <br><small class="text-muted">{{ $followUp->updateType->name ?? 'Follow-up' }}</small>
                        </div>
                        <div class="text-end">
                            @php
                                $followUpAt = $followUp->follow_up_date;
                                $isToday = $followUpAt->isToday();
                                $isTomorrow = $followUpAt->isTomorrow();
                                $badgeClass = $isToday ? 'bg-warning' : ($isTomorrow ? 'bg-info' : 'bg-secondary');
                                $dateLabel = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $followUpAt->format('d-m'));
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $dateLabel }}</span>
                            <br><small class="text-muted">{{ $followUpAt->format('h:i A') }}</small>
                        </div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-center text-muted py-3">
                    <i class="bi bi-telephone-forward fs-4 d-block mb-2"></i>
                    No upcoming follow-ups
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i> Trip Deadlines</span>
                <a href="{{ route('admin.trips.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: #fff !important;"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($tripDeadlines as $trip)
                <a href="{{ route('admin.trips.show', $trip) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate" style="max-width: 120px;">
                            <strong>{{ $trip->trip_number }}</strong>
                            <br><small class="text-muted">{{ $trip->customer->name ?? 'N/A' }}</small>
                        </div>
                        <div class="text-end">
                            @php
                                $daysLeft = \Carbon\Carbon::today()->diffInDays($trip->expected_end_date, false);
                                $badgeClass = $daysLeft <= 3 ? 'bg-danger' : ($daysLeft <= 7 ? 'bg-warning text-dark' : ($daysLeft <= 15 ? 'bg-info' : 'bg-secondary'));
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $daysLeft }} Days</span>
                            <br><small class="text-muted">{{ $trip->expected_end_date->format('d-m') }}</small>
                        </div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-center text-muted py-3">
                    <i class="bi bi-check-circle fs-4 d-block mb-2"></i>
                    No upcoming deadlines
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>


<div class="row mb-3">
    @foreach($companies->take(2) as $index => $company)
    <div class="col-md-4">
        <div class="main-card card">
            <div class="card-header {{ $index == 0 ? 'bg-info text-white' : 'bg-warning text-dark' }} py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-building me-2" style="color: #fff !important;"></i> {{ $company->name }}</span>
            </div>
            <div class="card-body py-2">
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted d-block">Income</small>
                        <strong class="text-success">{{ formatMoney($company->total_income) }}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Expense</small>
                        <strong class="text-danger">{{ formatMoney($company->total_expense) }}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Profit</small>
                        <strong class="text-primary">{{ formatMoney($company->total_profit) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="col-md-4">
        <div class="main-card card">
            <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bank me-2"></i> Bank Balances</span>
                <a href="{{ route('admin.banks.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: #fff !important;"></i></a>
            </div>
            <div class="card-body py-2">
                <div class="row text-center">
                    @foreach($banks as $bank)
                    <div class="col-4">
                        <small class="text-muted d-block">{{ \Str::limit($bank->bank_name, 8) }}</small>
                        <strong class="text-primary">{{ formatMoney($bank->balance) }}</strong>
                    </div>
                    @endforeach
                    @if($banks->count() == 0)
                    <div class="col-12 text-muted">No bank accounts</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="main-card card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2"></i> Profit & Loss Overview</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success mb-3"><i class="bi bi-arrow-down-circle me-1"></i> Income</h6>
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td>Trip Income</td>
                                    <td class="text-end text-success">{{ formatMoney($incomeByType['trip']) }}</td>
                                </tr>
                                <tr>
                                    <td>Advance Payments</td>
                                    <td class="text-end text-success">{{ formatMoney($incomeByType['advance']) }}</td>
                                </tr>
                                <tr>
                                    <td>Other Income</td>
                                    <td class="text-end text-success">{{ formatMoney($incomeByType['other']) }}</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Total Income</strong></td>
                                    <td class="text-end"><strong>{{ formatMoney($totalIncome) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger mb-3"><i class="bi bi-arrow-up-circle me-1"></i> Expenses</h6>
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dashExpenseModal1">
                                    <td>Material & Trip</td>
                                    <td class="text-end text-danger">{{ formatMoney($expenseByType['trip']) }}</td>
                                </tr>
                                <tr style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dashExpenseModal3">
                                    <td>Staff Salary</td>
                                    <td class="text-end text-danger">{{ formatMoney($expenseByType['salary']) }}</td>
                                </tr>
                                <tr style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dashExpenseModal4">
                                    <td>Vendor, Service & General</td>
                                    <td class="text-end text-danger">{{ formatMoney($expenseByType['vendor'] + $expenseByType['general'] + $expenseByType['service']) }}</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>Total Expenses</strong></td>
                                    <td class="text-end"><strong>{{ formatMoney($totalExpenses) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <div>
                                <h5 class="mb-0">Net Profit</h5>
                                <small class="text-muted">Total Income - Total Expenses</small>
                            </div>
                            <div class="text-end">
                                <h3 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">{{ formatMoney($netProfit) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card" id="tripChartCard" style="scroll-margin-top: 90px;">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-bar-chart-fill me-2"></i> Trip Budget vs Expense</span>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-0">
                    <select name="chart_trip" class="form-select form-select-sm" style="min-width: 240px;" onchange="window.location.href = this.form.action + (this.value ? '?chart_trip=' + encodeURIComponent(this.value) : '') + '#tripChartCard';">
                        <option value="">Top 5 Trips</option>
                        @foreach($chartTrips as $cp)
                            <option value="{{ $cp->id }}" {{ (string) $selectedChartTrip === (string) $cp->id ? 'selected' : '' }}>
                                {{ $cp->trip_number }} - {{ \Str::limit($cp->name, 30) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="tripExpenseChart"></canvas>
            </div>
        </div>
    </div>
</div>
@push('modals')
<div class="modal fade" id="dashExpenseModal1" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Material & Trip Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Trip/Vendor</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTripExpenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d-m') }}</td>
                            <td>{{ \Str::limit($expense->description ?? 'Trip Expense', 50) }}</td>
                            <td>{{ $expense->trip->trip_number ?? ($expense->vendor->name ?? '-') }}</td>
                            <td class="text-end">{{ formatMoney($expense->grand_total) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No expenses found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-danger">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($expenseByType['trip']) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.expenses.index') }}?expense_type=trip" class="btn btn-outline-primary">View All Expenses</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="dashExpenseModal3" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Staff Salary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Staff Name</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSalaryExpenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d-m') }}</td>
                            <td>{{ $expense->staff->name ?? 'N/A' }}</td>
                            <td>{{ \Str::limit($expense->description ?? 'Salary', 30) }}</td>
                            <td class="text-end">{{ formatMoney($expense->grand_total) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No salary expenses found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-danger">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($expenseByType['salary']) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-primary">View Staff</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="dashExpenseModal4" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Vendor, Service & General Expenses</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category/Vendor</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentGeneralExpenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d-m') }}</td>
                            <td>{{ \Str::limit($expense->description ?? 'Expense', 40) }}</td>
                            <td>{{ $expense->category->name ?? ($expense->vendor->name ?? '-') }}</td>
                            <td class="text-end">{{ formatMoney($expense->grand_total) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No expenses found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-danger">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($expenseByType['vendor'] + $expenseByType['general'] + $expenseByType['service']) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-primary">View All Expenses</a>
            </div>
        </div>
    </div>
</div>
@endpush
@push('styles')
<style>
.summary-row {
    display: flex;
    gap: 0.5rem;
}
.summary-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    cursor: pointer;
    flex: 1;
    border-radius: 6px;
    color: #fff;
    text-align: center;
}
.summary-item:hover {
    opacity: 0.9;
}
.summary-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #fff !important;
}
.summary-value {
    font-size: 1.1rem;
    font-weight: 700;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #fff !important;
}
.bg-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
@media (max-width: 768px) {
    .summary-row { flex-wrap: wrap; }
    .summary-item { min-width: 45%; }
    .summary-value { font-size: 0.95rem; }
}
</style>
@endpush
@push('scripts')
<script src="{{ asset('assets/scripts/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    var ctx = document.getElementById('tripExpenseChart').getContext('2d');
    var trips = @json($tripsForChart->pluck('name'));
    var budgets = @json($tripsForChart->pluck('budget'));
    var expenses = @json($tripsForChart->pluck('spent'));
    var incomes = @json($tripsForChart->pluck('income'));

    if (trips.length === 0) {
        document.getElementById('tripExpenseChart').parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"><div class="text-center"><i class="bi bi-bar-chart fs-1 d-block mb-2"></i>No trip data available</div></div>';
        return;
    }

    var budgetGradient = ctx.createLinearGradient(0, 0, 0, 350);
    budgetGradient.addColorStop(0, '#667eea');
    budgetGradient.addColorStop(1, '#764ba2');
    var expenseColors = budgets.map(function(budget, i) {
        var pct = budget > 0 ? (expenses[i] / budget) * 100 : 0;
        return pct > 75 ? '#dc3545' : '#28a745';
    });
    var shortLabels = trips.map(function(name) {
        var s = String(name).replace(/^TRP-\d{4}-\d+\s*-\s*/, '').trim();
        return s.length > 18 ? s.substring(0, 18) + '…' : s;
    });
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: shortLabels,
            datasets: [
                {
                    label: 'Budget',
                    data: budgets,
                    backgroundColor: budgetGradient,
                    borderRadius: 4,
                    categoryPercentage: 0.6,
                    barPercentage: 0.9
                },
                {
                    label: 'Spent',
                    data: expenses,
                    backgroundColor: expenseColors,
                    borderRadius: 4,
                    categoryPercentage: 0.6,
                    barPercentage: 0.9
                },
                {
                    label: 'Income',
                    data: incomes,
                    backgroundColor: '#17a2b8',
                    borderRadius: 4,
                    categoryPercentage: 0.6,
                    barPercentage: 0.9
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 20 } },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return trips[context[0].dataIndex];
                        },
                        label: function(context) {
                            return context.dataset.label + ': ₹' + context.raw + 'L';
                        },
                        afterBody: function(context) {
                            var i = context[0].dataIndex;
                            var b = budgets[i], s = expenses[i];
                            if (!b) return '';
                            var pct = ((s / b) * 100).toFixed(1);
                            return 'Used: ' + pct + '%';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, maxRotation: 0, autoSkip: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        callback: function(value) { return '₹' + value + 'L'; }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
