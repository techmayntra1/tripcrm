<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\LeadUpdate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function upcoming(): JsonResponse
    {
        $now = Carbon::now();
        $oneHourLater = $now->copy()->addHour();
        $twoHoursLater = $now->copy()->addHours(2);

        $notifications = collect();

        // Upcoming meetings in next 2 hours
        $meetings = Meeting::with(['customer', 'lead'])
            ->where('meeting_at', '>', $now)
            ->where('meeting_at', '<=', $twoHoursLater)
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->get();

        foreach ($meetings as $meeting) {
            $attendee = $meeting->customer->name
                ?? $meeting->lead->name
                ?? $meeting->other_attendee
                ?? 'N/A';

            // Check if meeting is within 1 hour (urgent) or 1-2 hours (normal)
            $isUrgent = $meeting->meeting_at->lte($oneHourLater);
            $minutesUntil = $now->diffInMinutes($meeting->meeting_at, false);

            $notifications->push([
                'id' => 'meeting_' . $meeting->id . '_' . $meeting->meeting_at->timestamp,
                'type' => 'meeting',
                'title' => $meeting->title ?? $attendee,
                'subtitle' => $attendee,
                'time' => $meeting->meeting_at->toIso8601String(),
                'time_formatted' => $meeting->meeting_at->format('h:i A'),
                'link' => route('admin.meetings.show', $meeting),
                'icon' => 'bi-calendar-event',
                'color' => $isUrgent ? 'danger' : 'primary',
                'urgent' => $isUrgent,
                'minutes_until' => $minutesUntil,
            ]);
        }

        // Upcoming tasks in next 2 hours (any active status — exclude Completed/Cancelled)
        $inactiveStatusIds = TaskStatus::whereIn('name', ['Completed', 'Cancelled'])->pluck('id')->toArray();

        $tasks = Task::with('trip')
            ->whereNotIn('status_id', $inactiveStatusIds)
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->where('start_at', '<=', $twoHoursLater)
            ->get();

        foreach ($tasks as $task) {
            $isUrgent = $task->start_at->lte($oneHourLater);
            $minutesUntil = $now->diffInMinutes($task->start_at, false);

            $notifications->push([
                'id' => 'task_' . $task->id . '_' . $task->start_at->timestamp,
                'type' => 'task',
                'title' => $task->title,
                'subtitle' => $task->trip->name ?? 'No Trip',
                'time' => $task->start_at->toIso8601String(),
                'time_formatted' => $task->start_at->format('h:i A'),
                'link' => route('admin.tasks.show', $task),
                'icon' => 'bi-list-task',
                'color' => $isUrgent ? 'danger' : 'warning',
                'urgent' => $isUrgent,
                'minutes_until' => $minutesUntil,
            ]);
        }

        // Upcoming follow-ups in next 2 hours
        $followUps = LeadUpdate::with('lead')
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '>', $now)
            ->where('follow_up_date', '<=', $twoHoursLater)
            ->whereHas('lead', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        foreach ($followUps as $followUp) {
            $isUrgent = $followUp->follow_up_date->lte($oneHourLater);
            $minutesUntil = $now->diffInMinutes($followUp->follow_up_date, false);

            $notifications->push([
                'id' => 'followup_' . $followUp->id . '_' . $followUp->follow_up_date->timestamp,
                'type' => 'followup',
                'title' => $followUp->lead->name ?? 'Unknown Lead',
                'subtitle' => 'Follow-up Reminder',
                'time' => $followUp->follow_up_date->toIso8601String(),
                'time_formatted' => $followUp->follow_up_date->format('h:i A'),
                'link' => route('admin.leads.show', $followUp->lead_id),
                'icon' => 'bi-telephone-forward',
                'color' => $isUrgent ? 'danger' : 'info',
                'urgent' => $isUrgent,
                'minutes_until' => $minutesUntil,
            ]);
        }

        // Sort by time
        $notifications = $notifications->sortBy('time')->values();

        return response()->json([
            'notifications' => $notifications,
            'server_time' => $now->toIso8601String(),
        ]);
    }
}
