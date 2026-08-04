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

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card widget-content bg-danger">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Total Expense</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($totalExpenses) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card widget-content bg-midnight-bloom">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Total Trips</div>
                    <div class="widget-subheading">All Trips</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ $totalProjects }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card widget-content bg-grow-early">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Total Customers</div>
                    <div class="widget-subheading">Active</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ $totalCustomers }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card widget-content bg-sunny-morning">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Staff Salary</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($staffSalary) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card widget-content bg-warning">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Client Receivables</div>
                    <div class="widget-subheading">Outstanding</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($clientReceivables) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card widget-content bg-happy-green">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Vendor Payables</div>
                    <div class="widget-subheading">Outstanding</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($vendorPayables) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card widget-content" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Service Payables</div>
                    <div class="widget-subheading">Outstanding</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($serviceProviderPayables) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span> <i class="bi bi-calendar-event me-2"></i> Scheduled Meetings</span>
                <a href="{{ route('admin.meetings.index') }}" class="text-warning"><i class="bi bi-arrow-right-circle-fill fs-5"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($upcomingMeetings as $meeting)
                <a href="{{ route('admin.meetings.show', $meeting) }}" class="list-group-item list-group-item-action py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <strong>
                                {{ $meeting->customer->name ?? $meeting->lead->name ?? $meeting->other_attendee ?? 'N/A' }}
                            </strong>
                            - {{ $meeting->purpose->name ?? $meeting->title }}
                        </span>
                        @php
                            $meetingAt = $meeting->meeting_at;
                            $isToday = $meetingAt->isToday();
                            $isTomorrow = $meetingAt->isTomorrow();
                            $badgeClass = $isToday ? 'bg-warning text-dark' : ($isTomorrow ? 'bg-info' : 'bg-secondary');
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
                <a href="{{ route('admin.tasks.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: rgba(247, 185, 36) !important;"></i></a>
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
                <a href="{{ route('admin.leads.index') }}"><i class="bi bi-arrow-right-circle-fill fs-5" style="color: rgba(247, 185, 36) !important;"></i></a>
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
            <div class="card-header">
                <i class="bi bi-calendar-check me-2"></i> Trip Deadlines
            </div>
            <div class="list-group list-group-flush">
                @forelse($projectDeadlines as $project)
                <div class="list-group-item py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $project->project_number }}</strong> - {{ \Str::limit($project->name, 25) }}
                            <br><small class="text-muted">{{ $project->customer->name ?? 'N/A' }}</small>
                        </div>
                        <div class="text-end">
                            @php
                                $daysLeft = \Carbon\Carbon::today()->diffInDays($project->expected_end_date, false);
                                $badgeClass = $daysLeft <= 3 ? 'bg-danger' : ($daysLeft <= 7 ? 'bg-warning text-dark' : ($daysLeft <= 15 ? 'bg-info' : 'bg-secondary'));
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $daysLeft }} Days Left</span>
                            <br><small class="text-muted">{{ $project->expected_end_date->format('d-m-Y') }}</small>
                        </div>
                    </div>
                </div>
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

<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card" id="projectChartCard" style="scroll-margin-top: 90px;">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-bar-chart-fill me-2"></i> Trip Budget vs Expense</span>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-0">
                    <select name="chart_project" class="form-select form-select-sm" style="min-width: 240px;" onchange="window.location.href = this.form.action + (this.value ? '?chart_project=' + encodeURIComponent(this.value) : '') + '#projectChartCard';">
                        <option value="">Top 5 Trips</option>
                        @foreach($chartProjects as $cp)
                            <option value="{{ $cp->id }}" {{ (string) $selectedChartProject === (string) $cp->id ? 'selected' : '' }}>
                                {{ $cp->project_number }} - {{ \Str::limit($cp->name, 30) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="projectExpenseChart"></canvas>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ asset('assets/scripts/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('projectExpenseChart').getContext('2d');
    var projects = @json($projectsForChart->pluck('name'));
    var budgets = @json($projectsForChart->pluck('budget'));
    var expenses = @json($projectsForChart->pluck('spent'));
    var incomes = @json($projectsForChart->pluck('income'));

    if (projects.length === 0) {
        document.getElementById('projectExpenseChart').parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"><div class="text-center"><i class="bi bi-bar-chart fs-1 d-block mb-2"></i>No trip data available</div></div>';
        return;
    }

    var budgetGradient = ctx.createLinearGradient(0, 0, 0, 350);
    budgetGradient.addColorStop(0, '#667eea');
    budgetGradient.addColorStop(1, '#764ba2');

    var expenseColors = budgets.map(function(budget, i) {
        var pct = budget > 0 ? (expenses[i] / budget) * 100 : 0;
        return pct > 75 ? '#dc3545' : '#28a745';
    });

    var shortLabels = projects.map(function(name) {
        var s = String(name).replace(/^PRJ-\d{4}-\d+\s*-\s*/, '').trim();
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
                            return projects[context[0].dataIndex];
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
