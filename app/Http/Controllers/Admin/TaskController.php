<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskUpdate;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Vendor;
use App\Models\TaskStatus;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project', 'status']);
        $today = now()->toDateString();

       
        $pendingStatus = TaskStatus::where('name', 'Pending')->first();
        $inProgressStatus = TaskStatus::where('name', 'In Progress')->first();
        $completedStatus = TaskStatus::where('name', 'Completed')->first();

        $activeStatusIds = collect([$pendingStatus?->id, $inProgressStatus?->id])->filter()->toArray();

        $fyDates = getFinancialYearDates();
        if ($fyDates && $request->tab !== 'deleted') {
            $query->whereBetween('start_at', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->tab === 'deleted') {
            $query->onlyTrashed();
            if ($fyDates) {
                $query->whereBetween('start_at', [$fyDates['start'], $fyDates['end']]);
            }
        } elseif ($request->tab === 'today') {
            $query->whereIn('status_id', $activeStatusIds)
                ->whereDate('start_at', $today);
        } elseif ($request->tab === 'upcoming') {
            $query->whereIn('status_id', $activeStatusIds)
                ->whereDate('start_at', '>', $today);
        } elseif ($request->tab === 'past') {
            $query->whereIn('status_id', $activeStatusIds)
                ->whereDate('start_at', '<', $today);
        } elseif ($request->tab === 'completed') {
            $query->where('status_id', $completedStatus?->id);
        } elseif ($request->tab === 'in_progress') {
            $query->where('status_id', $inProgressStatus?->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('project', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('per_page', 15);
        $tasks = $query->orderBy('start_at', 'desc')->paginate($perPage)->withQueryString();

        $baseQuery = Task::query();
        if ($fyDates) {
            $baseQuery->whereBetween('start_at', [$fyDates['start'], $fyDates['end']]);
        }

        $allCount = (clone $baseQuery)->count();
        $todayCount = (clone $baseQuery)->whereIn('status_id', $activeStatusIds)
            ->whereDate('start_at', $today)->count();
        $upcomingCount = (clone $baseQuery)->whereIn('status_id', $activeStatusIds)
            ->whereDate('start_at', '>', $today)->count();
        $pastCount = (clone $baseQuery)->whereIn('status_id', $activeStatusIds)
            ->whereDate('start_at', '<', $today)->count();
        $inProgressCount = (clone $baseQuery)->where('status_id', $inProgressStatus?->id)->count();
        $completedCount = (clone $baseQuery)->where('status_id', $completedStatus?->id)->count();

        $deletedQuery = Task::onlyTrashed();
        if ($fyDates) {
            $deletedQuery->whereBetween('start_at', [$fyDates['start'], $fyDates['end']]);
        }
        $deletedCount = $deletedQuery->count();

        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();
        $staffMembers = Staff::whereNull('deleted_at')->orderBy('name')->get();
        $vendors = Vendor::whereNull('deleted_at')->orderBy('name')->get();
        $taskStatuses = TaskStatus::active()->ordered()->get();

        return view('admin.tasks.index', compact(
            'tasks',
            'allCount',
            'todayCount',
            'upcomingCount',
            'pastCount',
            'inProgressCount',
            'completedCount',
            'deletedCount',
            'projects',
            'staffMembers',
            'vendors',
            'taskStatuses'
        ));
    }

    public function show(Task $task)
    {
        $task->load(['project', 'status', 'updates.user']);

        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();
        $staffMembers = Staff::whereNull('deleted_at')->orderBy('name')->get();
        $vendors = Vendor::whereNull('deleted_at')->orderBy('name')->get();
        $taskStatuses = TaskStatus::active()->ordered()->get();

        $previousUrl = url()->previous();
        if ($task->project_id && str_contains($previousUrl, '/projects/' . $task->project_id)) {
            $backUrl = route('admin.projects.show', $task->project_id);
        } else {
            $backUrl = route('admin.tasks.index');
        }

        return view('admin.tasks.show', compact(
            'task',
            'projects',
            'staffMembers',
            'vendors',
            'taskStatuses',
            'backUrl'
        ));
    }

    public function showTrashed($id)
    {
        $task = Task::onlyTrashed()->with(['project', 'status', 'updates.user'])->findOrFail($id);
        $isTrashed = true;

        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();
        $staffMembers = Staff::whereNull('deleted_at')->orderBy('name')->get();
        $vendors = Vendor::whereNull('deleted_at')->orderBy('name')->get();
        $taskStatuses = TaskStatus::active()->ordered()->get();

        $previousUrl = url()->previous();
        if ($task->project_id && str_contains($previousUrl, '/projects/' . $task->project_id)) {
            $backUrl = route('admin.projects.show', $task->project_id);
        } else {
            $backUrl = route('admin.tasks.index');
        }

        return view('admin.tasks.show', compact(
            'task',
            'isTrashed',
            'projects',
            'staffMembers',
            'vendors',
            'taskStatuses',
            'backUrl'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'project_id' => 'nullable|exists:projects,id',
            'assignee_type' => 'required|in:staff,vendor',
            'staff_id' => 'nullable|exists:staff,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'start_at' => 'required|date',
            'due_at' => 'nullable|date|after_or_equal:start_at',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validated['assignee_type'] === 'staff') {
            if (empty($request->staff_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['staff_id' => 'Please select a staff member.']);
            }
            $validated['assignee_id'] = $request->staff_id;
        } else {
            if (empty($request->vendor_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['vendor_id' => 'Please select a vendor.']);
            }
            $validated['assignee_id'] = $request->vendor_id;
        }

        unset($validated['staff_id'], $validated['vendor_id']);

       
        $pendingStatus = TaskStatus::where('name', 'Pending')->first();
        $validated['status_id'] = $pendingStatus?->id ?? 1;

        $task = Task::create($validated);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'notes' => 'Task created',
        ]);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'project_id' => 'nullable|exists:projects,id',
            'assignee_type' => 'required|in:staff,vendor',
            'staff_id' => 'nullable|exists:staff,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'start_at' => 'required|date',
            'due_at' => 'nullable|date|after_or_equal:start_at',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validated['assignee_type'] === 'staff') {
            if (empty($request->staff_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['staff_id' => 'Please select a staff member.']);
            }
            $validated['assignee_id'] = $request->staff_id;
        } else {
            if (empty($request->vendor_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['vendor_id' => 'Please select a vendor.']);
            }
            $validated['assignee_id'] = $request->vendor_id;
        }

        unset($validated['staff_id'], $validated['vendor_id']);

        $changes = [];
        if ($task->title !== $validated['title']) {
            $changes[] = "Title changed";
        }
        $oldStart = $task->start_at->format('d-m-Y h:i A');
        $newStart = \Carbon\Carbon::parse($validated['start_at'])->format('d-m-Y h:i A');
        if ($oldStart !== $newStart) {
            $changes[] = "Start date changed";
        }
        $oldDue = $task->due_at ? $task->due_at->format('d-m-Y') : null;
        $newDue = !empty($validated['due_at']) ? \Carbon\Carbon::parse($validated['due_at'])->format('d-m-Y') : null;
        if ($oldDue !== $newDue) {
            $changes[] = "Due date changed";
        }
        if ($task->assignee_id !== $validated['assignee_id'] || $task->assignee_type !== $validated['assignee_type']) {
            $changes[] = "Assignee changed";
        }

        $task->update($validated);

        if (!empty($changes)) {
            TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'note',
                'notes' => 'Task updated: ' . implode(', ', $changes),
            ]);
        }
        
        if (str_contains(url()->previous(), '/tasks/' . $task->id)) {
            return redirect()->route('admin.tasks.show', $task)
                ->with('success', 'Task updated successfully.');
        }

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:task_statuses,id',
            'due_at' => 'nullable|date',
        ]);

        $oldStatus = $task->status_name;
        $task->update($validated);
        $newStatus = $task->fresh()->status_name;

        if ($oldStatus !== $newStatus) {
            TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'notes' => "Status changed from {$oldStatus} to {$newStatus}",
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);
        }

        if (str_contains(url()->previous(), '/tasks/' . $task->id)) {
            return redirect()->route('admin.tasks.show', $task)
                ->with('success', 'Task status updated successfully.');
        }

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task status updated successfully.');
    }

    public function storeUpdate(Request $request, Task $task)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
            'follow_up_date' => 'nullable|date',
            'status_id' => 'nullable|exists:task_statuses,id',
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'notes' => $validated['notes'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        if (!empty($validated['status_id']) && $validated['status_id'] != $task->status_id) {
            $oldStatus = $task->status_name;
            $task->update(['status_id' => $validated['status_id']]);
            $newStatus = $task->fresh()->status_name;

            TaskUpdate::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'notes' => "Status changed from {$oldStatus} to {$newStatus}",
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);
        }

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Update added successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function deactivate(Task $task)
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function reactivate($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task restored successfully.');
    }

    public function getProjectAddress(Project $project)
    {
        return response()->json([
            'address' => $project->site_address
        ]);
    }
}
