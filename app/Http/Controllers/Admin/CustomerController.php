<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\LeadStatus;
use App\Models\Meeting;
use App\Models\MeetingPurpose;
use App\Models\UpdateType;
use App\Models\WorkType;
use App\Models\WorkLead;
use App\Models\Project;
use App\Models\Task;
use App\Models\CustomerUpdate;
use App\Models\Income;
use App\Exports\IncomeExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['city', 'projects:id,customer_id,name'])->withCount('projects');

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city_other', 'like', "%{$search}%")
                    ->orWhere('work_lead', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%")
                    ->orWhereJsonContains('work_type', $search);
            });
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $perPage = $request->input('per_page', 15);
        $customers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $trashedQuery = Customer::onlyTrashed();
        if ($fyDates) {
            $trashedQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }
        $trashedCount = $trashedQuery->count();

        return view('admin.customers.index', compact('customers', 'trashedCount'));
    }

    public function trashed(Request $request)
    {
        $query = Customer::onlyTrashed()->with(['city', 'projects:id,customer_id,name'])->withCount('projects');

        $fyDates = getFinancialYearDates();
        if ($fyDates) {
            $query->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city_other', 'like', "%{$search}%")
                    ->orWhere('work_lead', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%")
                    ->orWhereJsonContains('work_type', $search);
            });
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $perPage = $request->input('per_page', 15);
        $customers = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();

        $activeQuery = Customer::query();
        if ($fyDates) {
            $activeQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        return view('admin.customers.trashed', compact('customers', 'activeCount'));
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();
        return redirect()->route('admin.customers.trashed')->with('success', 'Customer restored successfully.');
    }

    public function showTrashed($id)
    {
        $customer = Customer::onlyTrashed()->with(['meetings.purpose', 'projects', 'updates.updateType', 'updates.user'])->findOrFail($id);

        $upcomingMeetings = collect();
        $updateTypes = collect();
        $leadStatuses = collect();
        $meetingPurposes = collect();
        $availableProjects = collect();

        $activities = collect();

        foreach ($customer->updates as $update) {
            $activities->push([
                'type' => 'update',
                'data' => $update,
                'date' => $update->created_at,
            ]);
        }

        foreach ($customer->meetings->filter(function($meeting) {
            return in_array($meeting->status, ['completed', 'cancelled', 'rescheduled']) || $meeting->trashed();
        }) as $meeting) {
            $activities->push([
                'type' => 'meeting',
                'data' => $meeting,
                'date' => $meeting->updated_at,
            ]);
        }

        $activities = $activities->sortByDesc('date');
        $isTrashed = true;

        $projectIds = $customer->projects->pluck('id')->toArray();
        $tasks = Task::whereIn('project_id', $projectIds)
            ->whereNull('deleted_at')
            ->with(['project', 'status'])
            ->orderBy('due_at')
            ->get();

        return view('admin.customers.show', compact(
            'customer',
            'upcomingMeetings',
            'activities',
            'updateTypes',
            'leadStatuses',
            'meetingPurposes',
            'availableProjects',
            'isTrashed',
            'tasks'
        ));
    }

    public function create()
    {
        $cities = City::active()->orderBy('name')->get();
        $workTypes = WorkType::active()->ordered()->get();
        $workLeads = WorkLead::active()->ordered()->get();
        $projects = Project::orderBy('project_number', 'desc')->get();
        return view('admin.customers.create', compact('cities', 'workTypes', 'workLeads', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'work_type' => 'nullable|array',
            'work_type.*' => 'string|max:100',
            'work_lead' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0|max:999999999',
            'payment_type' => 'required|string|max:50',
            'gst_number' => 'nullable|string|max:15',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'payment_type.required' => 'Payment type is required.',
            'gst_number.max' => 'GST number must not exceed 15 characters.',
            'email.email' => 'Please enter a valid email address.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
        ]);

        if (!empty($validated['city'])) {
            $validated['city_other'] = $validated['city'];
            unset($validated['city']);
        }

        $customer = Customer::create($validated);

        if ($request->filled('project_ids')) {
            Project::whereIn('id', $request->project_ids)->update(['customer_id' => $customer->id]);
        }

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['meetings.purpose', 'projects', 'quotations', 'invoices', 'updates.updateType', 'updates.user']);

        $upcomingMeetings = $customer->upcomingMeetings()->with('purpose')->get();
        $updateTypes = UpdateType::active()->get();
        $leadStatuses = LeadStatus::active()->get();
        $meetingPurposes = MeetingPurpose::active()->ordered()->get();

        $availableProjects = Project::whereNull('customer_id')
            ->orWhere('customer_id', '!=', $customer->id)
            ->orderBy('project_number', 'desc')
            ->get();

        $activities = collect();

        foreach ($customer->updates as $update) {
            $activities->push([
                'type' => 'update',
                'data' => $update,
                'date' => $update->created_at,
            ]);
        }

        foreach ($customer->meetings->filter(function($meeting) {
            return in_array($meeting->status, ['completed', 'cancelled', 'rescheduled']) || $meeting->trashed();
        }) as $meeting) {
            $activities->push([
                'type' => 'meeting',
                'data' => $meeting,
                'date' => $meeting->updated_at,
            ]);
        }

        $activities = $activities->sortByDesc('date');

        $projectIds = $customer->projects->pluck('id')->toArray();
        $tasks = Task::whereIn('project_id', $projectIds)
            ->whereNull('deleted_at')
            ->with(['project', 'status'])
            ->orderBy('due_at')
            ->get();

        // Lifetime income for this customer (no financial-year filter) so the
        // ledger total matches the lifetime "Total Income" summary card above it.
        $incomes = Income::with(['project', 'paymentMode', 'invoice'])
            ->where(function ($q) use ($customer, $projectIds) {
                $q->where('customer_id', $customer->id)
                    ->orWhereIn('project_id', $projectIds);
            })
            ->orderBy('income_date', 'desc')->orderBy('id', 'desc')->get();

        return view('admin.customers.show', compact(
            'customer',
            'upcomingMeetings',
            'activities',
            'updateTypes',
            'leadStatuses',
            'meetingPurposes',
            'availableProjects',
            'tasks',
            'incomes'
        ));
    }

    public function exportIncome(Request $request, Customer $customer)
    {
        $request->merge(['customer_id' => $customer->id]);

        $filename = 'income-' . str()->slug($customer->name) . '-' . now()->format('Y-m-d') . '.xlsx';

        return (new IncomeExport($request))->download($filename);
    }

    public function edit(Customer $customer)
    {
        $customer->load('projects');
        $cities = City::active()->orderBy('name')->get();
        $workTypes = WorkType::active()->ordered()->get();
        $workLeads = WorkLead::active()->ordered()->get();
        $projects = Project::orderBy('project_number', 'desc')->get();
        return view('admin.customers.edit', compact('customer', 'cities', 'workTypes', 'workLeads', 'projects'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'work_type' => 'nullable|array',
            'work_type.*' => 'string|max:100',
            'work_lead' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0|max:999999999',
            'payment_type' => 'required|string|max:50',
            'gst_number' => 'nullable|string|max:15',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'payment_type.required' => 'Payment type is required.',
            'gst_number.max' => 'GST number must not exceed 15 characters.',
            'email.email' => 'Please enter a valid email address.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
        ]);

        if (isset($validated['city'])) {
            $validated['city_other'] = $validated['city'] ?: null;
            unset($validated['city']);
        }

        $customer->update($validated);

        Project::where('customer_id', $customer->id)
            ->whereNotIn('id', $request->project_ids ?? [])
            ->update(['customer_id' => null]);

        if ($request->filled('project_ids')) {
            Project::whereIn('id', $request->project_ids)->update(['customer_id' => $customer->id]);
        }

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function storeMeeting(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:40',
            'meeting_at' => 'required|date|after_or_equal:today',
            'purpose_id' => 'nullable|exists:meeting_purposes,id',
            'location' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:150',
        ], [
            'meeting_at.after_or_equal' => 'Meeting date cannot be in the past.',
        ]);

        $conflictingMeeting = Meeting::getTimeConflict($validated['meeting_at']);
        if ($conflictingMeeting) {
            $conflictDateTime = \Carbon\Carbon::parse($conflictingMeeting->meeting_at)->format('d-m-Y h:i A');
            return redirect()->back()
                ->withInput()
                ->with('error', "Time conflict with \"{$conflictingMeeting->title}\" on {$conflictDateTime}. There must be at least 1 hour gap between meetings.");
        }

        $validated['customer_id'] = $customer->id;
        $validated['status'] = 'scheduled';
        $validated['assigned_to'] = Auth::id();

        Meeting::create($validated);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Meeting scheduled successfully.');
    }

    public function storeUpdate(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'update_type_id' => 'required|exists:update_types,id',
            'notes' => 'required|string|max:150',
            'follow_up_date' => 'nullable|date',
        ]);

        $validated['customer_id'] = $customer->id;
        $validated['user_id'] = Auth::id();

        CustomerUpdate::create($validated);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Update added successfully.');
    }

    public function linkProject(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $project->update(['customer_id' => $customer->id]);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Project linked successfully.');
    }
}
