<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\LeadUpdate;
use App\Models\Meeting;
use App\Models\MeetingPurpose;
use App\Models\UpdateType;
use App\Models\WorkType;
use App\Models\WorkLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::where('status', '!=', 'converted');

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
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('work_lead', 'like', "%{$search}%")
                    ->orWhereJsonContains('work_type', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $leads = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $trashedQuery = Lead::onlyTrashed()->where('status', '!=', 'converted');
        if ($fyDates) {
            $trashedQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }
        $trashedCount = $trashedQuery->count();

        $leadStatuses = LeadStatus::active()->get();

        return view('admin.leads.index', compact('leads', 'trashedCount', 'leadStatuses'));
    }

    public function trashed(Request $request)
    {
        $query = Lead::onlyTrashed()->where('status', '!=', 'converted');

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
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('work_lead', 'like', "%{$search}%")
                    ->orWhereJsonContains('work_type', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $leads = $query->orderBy('deleted_at', 'desc')->paginate($perPage)->withQueryString();

        $activeQuery = Lead::where('status', '!=', 'converted');
        if ($fyDates) {
            $activeQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }
        $activeCount = $activeQuery->count();

        $leadStatuses = LeadStatus::active()->get();

        return view('admin.leads.trashed', compact('leads', 'activeCount', 'leadStatuses'));
    }

    public function restore($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $lead->restore();
        return redirect()->route('admin.leads.trashed')->with('success', 'Lead restored successfully.');
    }

    public function showTrashed($id)
    {
        $lead = Lead::onlyTrashed()->with(['meetings.purpose', 'updates.updateType', 'updates.user'])->findOrFail($id);

        $upcomingMeetings = collect();
        $updateTypes = collect();
        $leadStatuses = collect();
        $meetingPurposes = collect();

        $activities = collect();

        foreach ($lead->updates as $update) {
            $activities->push([
                'type' => 'update',
                'data' => $update,
                'date' => $update->created_at,
            ]);
        }

        foreach ($lead->meetings->filter(function($meeting) {
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

        return view('admin.leads.show', compact(
            'lead',
            'upcomingMeetings',
            'activities',
            'updateTypes',
            'leadStatuses',
            'meetingPurposes',
            'isTrashed'
        ));
    }

    public function create()
    {
        $workTypes = WorkType::active()->ordered()->get();
        $workLeads = WorkLead::active()->ordered()->get();
        return view('admin.leads.create', compact('workTypes', 'workLeads'));
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
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'email.email' => 'Please enter a valid email address.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
        ]);

        Lead::create($validated);

        return redirect()->route('admin.leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['meetings.purpose', 'updates.updateType', 'updates.user']);

        $upcomingMeetings = $lead->upcomingMeetings()->with('purpose')->get();
        $updateTypes = UpdateType::active()->get();
        $leadStatuses = LeadStatus::active()->get();
        $meetingPurposes = MeetingPurpose::active()->ordered()->get();

        $activities = collect();

        foreach ($lead->updates as $update) {
            $activities->push([
                'type' => 'update',
                'data' => $update,
                'date' => $update->created_at,
            ]);
        }

        foreach ($lead->meetings->filter(function($meeting) {
            return in_array($meeting->status, ['completed', 'cancelled', 'rescheduled']) || $meeting->trashed();
        }) as $meeting) {
            $activities->push([
                'type' => 'meeting',
                'data' => $meeting,
                'date' => $meeting->updated_at,
            ]);
        }

        $activities = $activities->sortByDesc('date');

        return view('admin.leads.show', compact(
            'lead',
            'upcomingMeetings',
            'activities',
            'updateTypes',
            'leadStatuses',
            'meetingPurposes'
        ));
    }

    public function edit(Lead $lead)
    {
        $workTypes = WorkType::active()->ordered()->get();
        $workLeads = WorkLead::active()->ordered()->get();
        $leadStatuses = LeadStatus::active()->get();
        return view('admin.leads.edit', compact('lead', 'workTypes', 'workLeads', 'leadStatuses'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'work_type' => 'nullable|array',
            'work_type.*' => 'string|max:100',
            'work_lead' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0|max:999999999',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:150',
            'status' => 'nullable|string',
        ], [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'email.email' => 'Please enter a valid email address.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
        ]);

        if (!empty($validated['status']) && !Auth::user()->isAdmin()) {
            if (!LeadStatus::canProgressTo($lead->status, $validated['status'])) {
                return redirect()->back()->with('error', 'You cannot move the status backward.');
            }
        }

        $lead->update($validated);

        return redirect()->route('admin.leads.index')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function convertForm(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.index')->with('error', 'This lead is already converted.');
        }

        $cities = City::active()->orderBy('name')->get();
        $workTypes = WorkType::active()->ordered()->get();
        $workLeads = WorkLead::active()->ordered()->get();
        return view('admin.leads.convert', compact('lead', 'cities', 'workTypes', 'workLeads'));
    }

    public function convert(Request $request, Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('admin.leads.index')->with('error', 'This lead is already converted.');
        }

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'work_type' => 'nullable|array',
            'work_type.*' => 'string|max:100',
            'work_lead' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0|max:999999999',
            'payment_type' => 'required|string|max:50',
            'gst_number' => 'nullable|string|size:15',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.size' => 'Mobile number must be exactly 10 digits.',
            'payment_type.required' => 'Payment type is required.',
            'gst_number.size' => 'GST number must be exactly 15 characters.',
        ]);

        if (!empty($validated['city'])) {
            $validated['city_other'] = $validated['city'];
            unset($validated['city']);
        }

        $validated['lead_id'] = $lead->id;

        Customer::create($validated);

        $lead->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Lead converted to customer successfully.');
    }

    public function storeMeeting(Request $request, Lead $lead)
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

        $validated['lead_id'] = $lead->id;
        $validated['status'] = 'scheduled';
        $validated['assigned_to'] = Auth::id();

        Meeting::create($validated);

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Meeting scheduled successfully.');
    }

    public function storeUpdate(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'update_type_id' => 'required|exists:update_types,id',
            'notes' => 'required|string|max:150',
            'follow_up_date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        if (!empty($validated['status']) && !Auth::user()->isAdmin()) {
            if (!LeadStatus::canProgressTo($lead->status, $validated['status'])) {
                return redirect()->back()->with('error', 'You cannot move the status backward.');
            }
        }

        $validated['lead_id'] = $lead->id;
        $validated['user_id'] = Auth::id();

        LeadUpdate::create($validated);

        if (!empty($validated['status'])) {
            $lead->update(['status' => $validated['status']]);
        }

        return redirect()->back()->with('success', 'Update added successfully.');
    }

    public function followUps(Lead $lead)
    {
        $lead->load(['updates.updateType', 'updates.user']);
        $updates = $lead->updates()->with(['updateType', 'user'])->orderByDesc('created_at')->get();
        $updateTypes = UpdateType::active()->get();
        $leadStatuses = LeadStatus::active()->get();

        return view('admin.leads.follow-ups', compact('lead', 'updates', 'updateTypes', 'leadStatuses'));
    }

    public function destroyUpdate(Lead $lead, LeadUpdate $update)
    {
        if ($update->lead_id !== $lead->id) {
            return redirect()->back()->with('error', 'Invalid update.');
        }

        $update->delete();

        return redirect()->back()->with('success', 'Update deleted successfully.');
    }

    public function markWon(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'final_budget' => 'required|numeric|min:0|max:999999999',
        ], [
            'final_budget.required' => 'Final budget is required.',
            'final_budget.numeric' => 'Final budget must be a valid number.',
        ]);

        $lead->update([
            'status' => 'won',
            'final_budget' => $validated['final_budget'],
            'converted_at' => now(),
        ]);

        return redirect()->route('admin.leads.index')->with('success', 'Lead marked as Won successfully.');
    }

    public function markLost(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'lost_to' => 'required|string|max:30',
            'notes' => 'nullable|string|max:500',
        ], [
            'lost_to.required' => 'Please enter who you lost to.',
            'lost_to.max' => 'Lost to must not exceed 30 characters.',
        ]);

        $lead->update([
            'status' => 'lost',
            'lost_to' => $validated['lost_to'],
            'notes' => $validated['notes'] ?? $lead->notes,
        ]);

        return redirect()->route('admin.leads.index')->with('success', 'Lead marked as Lost.');
    }
}
