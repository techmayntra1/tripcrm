<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadUpdate;
use App\Models\CustomerUpdate;
use App\Models\UpdateType;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $fyDates = getFinancialYearDates();

        // Build queries for both lead and customer updates that have follow_up_date
        $leadQuery = LeadUpdate::with(['lead', 'updateType', 'user'])
            ->whereNotNull('follow_up_date');

        $customerQuery = CustomerUpdate::with(['customer', 'updateType', 'user'])
            ->whereNotNull('follow_up_date');

        // Apply financial year filter
        if ($fyDates) {
            $leadQuery->whereBetween('follow_up_date', [$fyDates['start'], $fyDates['end']]);
            $customerQuery->whereBetween('follow_up_date', [$fyDates['start'], $fyDates['end']]);
        }

        // Get base counts for tabs
        $baseLeadQuery = LeadUpdate::whereNotNull('follow_up_date');
        $baseCustomerQuery = CustomerUpdate::whereNotNull('follow_up_date');

        if ($fyDates) {
            $baseLeadQuery->whereBetween('follow_up_date', [$fyDates['start'], $fyDates['end']]);
            $baseCustomerQuery->whereBetween('follow_up_date', [$fyDates['start'], $fyDates['end']]);
        }

        $allCount = (clone $baseLeadQuery)->count() + (clone $baseCustomerQuery)->count();
        $todayCount = (clone $baseLeadQuery)->whereDate('follow_up_date', $today)->count()
            + (clone $baseCustomerQuery)->whereDate('follow_up_date', $today)->count();
        $upcomingCount = (clone $baseLeadQuery)->whereDate('follow_up_date', '>', $today)->count()
            + (clone $baseCustomerQuery)->whereDate('follow_up_date', '>', $today)->count();
        $pastCount = (clone $baseLeadQuery)->whereDate('follow_up_date', '<', $today)->count()
            + (clone $baseCustomerQuery)->whereDate('follow_up_date', '<', $today)->count();

        // Apply tab filters
        if ($request->tab === 'today') {
            $leadQuery->whereDate('follow_up_date', $today);
            $customerQuery->whereDate('follow_up_date', $today);
        } elseif ($request->tab === 'upcoming') {
            $leadQuery->whereDate('follow_up_date', '>', $today);
            $customerQuery->whereDate('follow_up_date', '>', $today);
        } elseif ($request->tab === 'past') {
            $leadQuery->whereDate('follow_up_date', '<', $today);
            $customerQuery->whereDate('follow_up_date', '<', $today);
        }

        // Apply source filter
        if ($request->filled('source')) {
            if ($request->source === 'leads') {
                $customerQuery->whereRaw('1 = 0');
            } elseif ($request->source === 'customers') {
                $leadQuery->whereRaw('1 = 0');
            }
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $leadQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
            $customerQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Get results and merge them
        $leadFollowUps = $leadQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'lead',
                'entity_id' => $item->lead_id,
                'entity_name' => $item->lead->name ?? 'N/A',
                'entity_phone' => $item->lead->phone ?? null,
                'update_type' => $item->updateType->name ?? 'N/A',
                'update_type_color' => $item->updateType->color ?? 'secondary',
                'notes' => $item->notes,
                'follow_up_date' => $item->follow_up_date,
                'created_by' => $item->user->name ?? 'N/A',
                'created_at' => $item->created_at,
            ];
        });

        $customerFollowUps = $customerQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'customer',
                'entity_id' => $item->customer_id,
                'entity_name' => $item->customer->name ?? 'N/A',
                'entity_phone' => $item->customer->phone ?? null,
                'update_type' => $item->updateType->name ?? 'N/A',
                'update_type_color' => $item->updateType->color ?? 'secondary',
                'notes' => $item->notes,
                'follow_up_date' => $item->follow_up_date,
                'created_by' => $item->user->name ?? 'N/A',
                'created_at' => $item->created_at,
            ];
        });

        // Merge and sort by follow_up_date
        $allFollowUps = $leadFollowUps->merge($customerFollowUps)
            ->sortBy('follow_up_date')
            ->values();

        // Manual pagination
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        $total = $allFollowUps->count();
        $items = $allFollowUps->slice(($page - 1) * $perPage, $perPage)->values();

        $followUps = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.follow-ups.index', compact(
            'followUps',
            'allCount',
            'todayCount',
            'upcomingCount',
            'pastCount'
        ));
    }
}
