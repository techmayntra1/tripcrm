<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingUpdate;
use App\Models\MeetingPurpose;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::with(['lead', 'customer', 'vendor', 'purpose', 'assignedUser']);
        $today = now()->toDateString();

        $fyDates = getFinancialYearDates();
        if ($fyDates && $request->tab !== 'deleted') {
            $query->whereBetween('meeting_at', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->tab === 'deleted') {
            $query->onlyTrashed();
            if ($fyDates) {
                $query->whereBetween('meeting_at', [$fyDates['start'], $fyDates['end']]);
            }
        } elseif ($request->tab === 'today') {
            $query->whereIn('status', ['scheduled', 'rescheduled'])
                ->whereDate('meeting_at', $today);
        } elseif ($request->tab === 'upcoming') {
            $query->whereIn('status', ['scheduled', 'rescheduled'])
                ->whereDate('meeting_at', '>', $today);
        } elseif ($request->tab === 'past') {
            $query->whereIn('status', ['scheduled', 'rescheduled'])
                ->whereDate('meeting_at', '<', $today);
        } elseif ($request->tab === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($request->tab === 'completed') {
            $query->where('status', 'completed');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('per_page', 15);
        $meetings = $query->orderBy('meeting_at', 'desc')->paginate($perPage)->withQueryString();

        $baseQuery = Meeting::query();
        if ($fyDates) {
            $baseQuery->whereBetween('meeting_at', [$fyDates['start'], $fyDates['end']]);
        }

        $allCount = (clone $baseQuery)->count();
        $todayCount = (clone $baseQuery)->whereIn('status', ['scheduled', 'rescheduled'])
            ->whereDate('meeting_at', $today)->count();
        $upcomingCount = (clone $baseQuery)->whereIn('status', ['scheduled', 'rescheduled'])
            ->whereDate('meeting_at', '>', $today)->count();
        $pastCount = (clone $baseQuery)->whereIn('status', ['scheduled', 'rescheduled'])
            ->whereDate('meeting_at', '<', $today)->count();
        $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

        $deletedQuery = Meeting::onlyTrashed();
        if ($fyDates) {
            $deletedQuery->whereBetween('meeting_at', [$fyDates['start'], $fyDates['end']]);
        }
        $deletedCount = $deletedQuery->count();

        $meetingPurposes = MeetingPurpose::active()->ordered()->get();
        $leads = Lead::whereNull('deleted_at')->orderBy('name')->get();
        $customers = Customer::whereNull('deleted_at')->orderBy('name')->get();
        $vendors = Vendor::whereNull('deleted_at')->orderBy('name')->get();

        return view('admin.meetings.index', compact(
            'meetings',
            'allCount',
            'todayCount',
            'upcomingCount',
            'pastCount',
            'completedCount',
            'cancelledCount',
            'deletedCount',
            'meetingPurposes',
            'leads',
            'customers',
            'vendors'
        ));
    }

    public function show(Meeting $meeting)
    {
        $meeting->load(['lead', 'customer', 'vendor', 'project', 'purpose', 'assignedUser', 'updates.user']);

        $meetingPurposes = MeetingPurpose::active()->ordered()->get();
        $leads = Lead::whereNull('deleted_at')->orderBy('name')->get();
        $customers = Customer::whereNull('deleted_at')->orderBy('name')->get();
        $vendors = Vendor::whereNull('deleted_at')->orderBy('name')->get();
        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();

        $previousUrl = url()->previous();
        if ($meeting->customer_id && str_contains($previousUrl, '/customers/' . $meeting->customer_id)) {
            $backUrl = route('admin.customers.show', $meeting->customer_id);
        } elseif ($meeting->lead_id && str_contains($previousUrl, '/leads/' . $meeting->lead_id)) {
            $backUrl = route('admin.leads.show', $meeting->lead_id);
        } else {
            $backUrl = route('admin.meetings.index');
        }

        return view('admin.meetings.show', compact(
            'meeting',
            'meetingPurposes',
            'leads',
            'customers',
            'vendors',
            'backUrl',
            'projects'
        ));
    }

    public function showTrashed($id)
    {
        $meeting = Meeting::onlyTrashed()->with(['lead', 'customer', 'vendor', 'project', 'purpose', 'assignedUser', 'updates.user'])->findOrFail($id);
        $isTrashed = true;

        $meetingPurposes = MeetingPurpose::active()->ordered()->get();
        $leads = Lead::whereNull('deleted_at')->orderBy('name')->get();
        $customers = Customer::whereNull('deleted_at')->orderBy('name')->get();
        $vendors = Vendor::whereNull('deleted_at')->orderBy('name')->get();
        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();

        $previousUrl = url()->previous();
        if ($meeting->customer_id && str_contains($previousUrl, '/customers/' . $meeting->customer_id)) {
            $backUrl = route('admin.customers.show', $meeting->customer_id);
        } elseif ($meeting->lead_id && str_contains($previousUrl, '/leads/' . $meeting->lead_id)) {
            $backUrl = route('admin.leads.show', $meeting->lead_id);
        } else {
            $backUrl = route('admin.meetings.index');
        }

        return view('admin.meetings.show', compact(
            'meeting',
            'isTrashed',
            'meetingPurposes',
            'leads',
            'customers',
            'vendors',
            'projects',
            'backUrl'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:40',
            'meeting_at' => 'required|date|after_or_equal:today',
            'purpose_id' => 'nullable|exists:meeting_purposes,id',
            'location' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:150',
            'meeting_with' => 'required|in:lead,customer,vendor,other',
            'lead_id' => 'nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'other_attendee' => 'nullable|string|max:200',
        ], [
            'meeting_at.after_or_equal' => 'Meeting date cannot be in the past.',
        ]);

        $conflictingMeeting = Meeting::getTimeConflict($validated['meeting_at']);
        if ($conflictingMeeting) {
            $conflictDateTime = Carbon::parse($conflictingMeeting->meeting_at)->format('d-m-Y h:i A');
            return redirect()->back()
                ->withInput()
                ->with('error', "Time conflict with \"{$conflictingMeeting->title}\" on {$conflictDateTime}. There must be at least 1 hour gap between meetings.");
        }

        if ($validated['meeting_with'] === 'lead') {
            $validated['customer_id'] = null;
            $validated['vendor_id'] = null;
            $validated['other_attendee'] = null;
        } elseif ($validated['meeting_with'] === 'customer') {
            $validated['lead_id'] = null;
            $validated['vendor_id'] = null;
            $validated['other_attendee'] = null;
        } elseif ($validated['meeting_with'] === 'vendor') {
            $validated['lead_id'] = null;
            $validated['customer_id'] = null;
            $validated['other_attendee'] = null;
        } else {
            $validated['lead_id'] = null;
            $validated['customer_id'] = null;
            $validated['vendor_id'] = null;
        }

        unset($validated['meeting_with']);
        $validated['status'] = 'scheduled';

        $meeting = Meeting::create($validated);

        MeetingUpdate::create([
            'meeting_id' => $meeting->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'notes' => 'Meeting scheduled',
        ]);

        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting created successfully.');
    }

    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:40',
            'meeting_at' => 'required|date|after_or_equal:today',
            'purpose_id' => 'nullable|exists:meeting_purposes,id',
            'location' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:150',
            'meeting_with' => 'required|in:lead,customer,vendor,other',
            'lead_id' => 'nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'other_attendee' => 'nullable|string|max:200',
        ], [
            'meeting_at.after_or_equal' => 'Meeting date cannot be in the past.',
        ]);

        $conflictingMeeting = Meeting::getTimeConflict($validated['meeting_at'], $meeting->id);
        if ($conflictingMeeting) {
            $conflictDateTime = Carbon::parse($conflictingMeeting->meeting_at)->format('d-m-Y h:i A');
            return redirect()->back()
                ->withInput()
                ->with('error', "Time conflict with \"{$conflictingMeeting->title}\" on {$conflictDateTime}. There must be at least 1 hour gap between meetings.");
        }

        if ($validated['meeting_with'] === 'lead') {
            $validated['customer_id'] = null;
            $validated['vendor_id'] = null;
            $validated['other_attendee'] = null;
        } elseif ($validated['meeting_with'] === 'customer') {
            $validated['lead_id'] = null;
            $validated['vendor_id'] = null;
            $validated['other_attendee'] = null;
        } elseif ($validated['meeting_with'] === 'vendor') {
            $validated['lead_id'] = null;
            $validated['customer_id'] = null;
            $validated['other_attendee'] = null;
        } else {
            $validated['lead_id'] = null;
            $validated['customer_id'] = null;
            $validated['vendor_id'] = null;
        }

        unset($validated['meeting_with']);

        $oldDate = $meeting->meeting_at ? $meeting->meeting_at->format('d-m-Y h:i A') : null;
        $newDate = Carbon::parse($validated['meeting_at'])->format('d-m-Y h:i A');
        $dateChanged = $oldDate !== $newDate;

        $otherChanges = [];
        if ($meeting->title !== $validated['title']) {
            $otherChanges[] = "Title changed";
        }
        if ($meeting->location !== ($validated['location'] ?? null)) {
            $otherChanges[] = "Location updated";
        }

        $meeting->update($validated);

        if ($dateChanged) {
            MeetingUpdate::create([
                'meeting_id' => $meeting->id,
                'user_id' => auth()->id(),
                'type' => 'date_change',
                'notes' => "Date changed from {$oldDate} to {$newDate}",
                'old_value' => $oldDate,
                'new_value' => $newDate,
            ]);
        }

        if (!empty($otherChanges)) {
            MeetingUpdate::create([
                'meeting_id' => $meeting->id,
                'user_id' => auth()->id(),
                'type' => 'note',
                'notes' => 'Meeting updated: ' . implode(', ', $otherChanges),
            ]);
        }

        $previousUrl = url()->previous();
        if (str_contains($previousUrl, '/leads/')) {
            return redirect()->back()->with('success', 'Meeting updated successfully.');
        }
        if (str_contains($previousUrl, '/customers/')) {
            return redirect()->back()->with('success', 'Meeting updated successfully.');
        }
        if (str_contains($previousUrl, '/vendors/')) {
            return redirect()->back()->with('success', 'Meeting updated successfully.');
        }
        if (str_contains($previousUrl, '/meetings/' . $meeting->id)) {
            return redirect()->route('admin.meetings.show', $meeting)
                ->with('success', 'Meeting updated successfully.');
        }

        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting updated successfully.');
    }

    public function complete(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'outcome' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $meeting->status;
        $meeting->update([
            'status' => 'completed',
            'outcome' => $validated['outcome'] ?? null,
        ]);

        MeetingUpdate::create([
            'meeting_id' => $meeting->id,
            'user_id' => auth()->id(),
            'type' => 'status_change',
            'notes' => $validated['outcome'] ? "Meeting completed. Outcome: {$validated['outcome']}" : 'Meeting marked as completed',
            'old_value' => $oldStatus,
            'new_value' => 'completed',
        ]);

        $previousUrl = url()->previous();
        if (str_contains($previousUrl, '/leads/')) {
            return redirect()->back()->with('success', 'Meeting marked as completed.');
        }
        if (str_contains($previousUrl, '/customers/')) {
            return redirect()->back()->with('success', 'Meeting marked as completed.');
        }
        if (str_contains($previousUrl, '/vendors/')) {
            return redirect()->back()->with('success', 'Meeting marked as completed.');
        }
        if (str_contains($previousUrl, '/meetings/' . $meeting->id)) {
            return redirect()->route('admin.meetings.show', $meeting)
                ->with('success', 'Meeting marked as completed.');
        }

        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting marked as completed.');
    }

    public function reschedule(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'meeting_at' => 'required|date|after_or_equal:today',
        ], [
            'meeting_at.after_or_equal' => 'Meeting date cannot be in the past.',
        ]);

        $conflictingMeeting = Meeting::getTimeConflict($validated['meeting_at'], $meeting->id);
        if ($conflictingMeeting) {
            $conflictDateTime = Carbon::parse($conflictingMeeting->meeting_at)->format('d-m-Y h:i A');
            return redirect()->back()
                ->withInput()
                ->with('error', "Time conflict with \"{$conflictingMeeting->title}\" on {$conflictDateTime}. There must be at least 1 hour gap between meetings.");
        }

        $oldDate = $meeting->meeting_at->format('d-m-Y h:i A');
        $meeting->update([
            'meeting_at' => $validated['meeting_at'],
            'status' => 'rescheduled',
        ]);
        $newDate = $meeting->fresh()->meeting_at->format('d-m-Y h:i A');

        MeetingUpdate::create([
            'meeting_id' => $meeting->id,
            'user_id' => auth()->id(),
            'type' => 'date_change',
            'notes' => "Meeting rescheduled from {$oldDate} to {$newDate}",
            'old_value' => $oldDate,
            'new_value' => $newDate,
        ]);
        
        $previousUrl = url()->previous();
        if (str_contains($previousUrl, '/leads/')) {
            return redirect()->back()->with('success', 'Meeting rescheduled successfully.');
        }
        if (str_contains($previousUrl, '/customers/')) {
            return redirect()->back()->with('success', 'Meeting rescheduled successfully.');
        }
        if (str_contains($previousUrl, '/vendors/')) {
            return redirect()->back()->with('success', 'Meeting rescheduled successfully.');
        }
        if (str_contains($previousUrl, '/meetings/' . $meeting->id)) {
            return redirect()->route('admin.meetings.show', $meeting)
                ->with('success', 'Meeting rescheduled successfully.');
        }

        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting rescheduled successfully.');
    }

    public function storeUpdate(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
            'follow_up_date' => 'nullable|date',
        ]);

        MeetingUpdate::create([
            'meeting_id' => $meeting->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'notes' => $validated['notes'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        return redirect()->route('admin.meetings.show', $meeting)
            ->with('success', 'Update added successfully.');
    }

    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting deleted successfully.');
    }

    public function deactivate(Meeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting deleted successfully.');
    }

    public function reactivate($id)
    {
        $meeting = Meeting::onlyTrashed()->findOrFail($id);
        $meeting->restore();
        return redirect()->route('admin.meetings.index')
            ->with('success', 'Meeting restored successfully.');
    }
}
