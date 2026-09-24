<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkType;
use App\Models\WorkLead;
use App\Models\VendorCategory;
use App\Models\ExpenseCategory;
use App\Models\MeetingPurpose;
use App\Models\UpdateType;
use App\Models\LeadStatus;
use App\Models\Unit;
use App\Models\PaymentMode;
use App\Models\GstRate;
use App\Models\TripStatus;
use App\Models\ExpenseType;
use App\Models\TaskStatus;
use App\Models\StaffPosition;
use App\Models\Service;
use App\Models\PassengerType;
use App\Models\TermTemplate;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function workTypes(Request $request)
    {
        $query = WorkType::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = WorkType::onlyTrashed()->count();
        return view('admin.masters.work-types', compact('items', 'inactiveCount'));
    }

    public function workTypesTrashed(Request $request)
    {
        $query = WorkType::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = WorkType::count();
        return view('admin.masters.work-types-trashed', compact('items', 'activeCount'));
    }

    public function storeWorkType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:work_types,name']);
        WorkType::create(['name' => $request->name, 'sort_order' => WorkType::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.work-types')->with('success', 'Work type added successfully.');
    }

    public function updateWorkType(Request $request, WorkType $workType)
    {
        $request->validate(['name' => 'required|string|max:20|unique:work_types,name,' . $workType->id]);
        $workType->update(['name' => $request->name]);
        return redirect()->route('admin.masters.work-types')->with('success', 'Work type updated successfully.');
    }

    public function toggleWorkType($id)
    {
        $workType = WorkType::withTrashed()->findOrFail($id);
        if ($workType->trashed()) {
            $workType->restore();
            return redirect()->back()->with('success', 'Work type restored.');
        } else {
            $workType->delete();
            return redirect()->back()->with('success', 'Work type deleted.');
        }
    }

    public function workLeads(Request $request)
    {
        $query = WorkLead::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = WorkLead::onlyTrashed()->count();
        return view('admin.masters.work-leads', compact('items', 'inactiveCount'));
    }

    public function workLeadsTrashed(Request $request)
    {
        $query = WorkLead::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = WorkLead::count();
        return view('admin.masters.work-leads-trashed', compact('items', 'activeCount'));
    }

    public function storeWorkLead(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:work_leads,name']);
        WorkLead::create(['name' => $request->name, 'sort_order' => WorkLead::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.work-leads')->with('success', 'Lead source added successfully.');
    }

    public function updateWorkLead(Request $request, WorkLead $workLead)
    {
        $request->validate(['name' => 'required|string|max:20|unique:work_leads,name,' . $workLead->id]);
        $workLead->update(['name' => $request->name]);
        return redirect()->route('admin.masters.work-leads')->with('success', 'Lead source updated successfully.');
    }

    public function toggleWorkLead($id)
    {
        $workLead = WorkLead::withTrashed()->findOrFail($id);
        if ($workLead->trashed()) {
            $workLead->restore();
            return redirect()->back()->with('success', 'Lead source restored.');
        } else {
            $workLead->delete();
            return redirect()->back()->with('success', 'Lead source deleted.');
        }
    }

    public function vendorCategories(Request $request)
    {
        $query = VendorCategory::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = VendorCategory::onlyTrashed()->count();
        return view('admin.masters.vendor-categories', compact('items', 'inactiveCount'));
    }

    public function vendorCategoriesTrashed(Request $request)
    {
        $query = VendorCategory::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = VendorCategory::count();
        return view('admin.masters.vendor-categories-trashed', compact('items', 'activeCount'));
    }

    public function storeVendorCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:vendor_categories,name']);
        VendorCategory::create(['name' => $request->name, 'sort_order' => VendorCategory::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.vendor-categories')->with('success', 'Vendor category added successfully.');
    }

    public function updateVendorCategory(Request $request, VendorCategory $vendorCategory)
    {
        $request->validate(['name' => 'required|string|max:20|unique:vendor_categories,name,' . $vendorCategory->id]);
        $vendorCategory->update(['name' => $request->name]);
        return redirect()->route('admin.masters.vendor-categories')->with('success', 'Vendor category updated successfully.');
    }

    public function toggleVendorCategory($id)
    {
        $vendorCategory = VendorCategory::withTrashed()->findOrFail($id);
        if ($vendorCategory->trashed()) {
            $vendorCategory->restore();
            return redirect()->back()->with('success', 'Vendor category restored.');
        } else {
            $vendorCategory->delete();
            return redirect()->back()->with('success', 'Vendor category deleted.');
        }
    }

    public function expenseCategories(Request $request)
    {
        $query = ExpenseCategory::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = ExpenseCategory::onlyTrashed()->count();
        return view('admin.masters.expense-categories', compact('items', 'inactiveCount'));
    }

    public function expenseCategoriesTrashed(Request $request)
    {
        $query = ExpenseCategory::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = ExpenseCategory::count();
        return view('admin.masters.expense-categories-trashed', compact('items', 'activeCount'));
    }

    public function storeExpenseCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:expense_categories,name']);
        ExpenseCategory::create(['name' => $request->name, 'sort_order' => ExpenseCategory::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.expense-categories')->with('success', 'Expense category added successfully.');
    }

    public function updateExpenseCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate(['name' => 'required|string|max:20|unique:expense_categories,name,' . $expenseCategory->id]);
        $expenseCategory->update(['name' => $request->name]);
        return redirect()->route('admin.masters.expense-categories')->with('success', 'Expense category updated successfully.');
    }

    public function toggleExpenseCategory($id)
    {
        $expenseCategory = ExpenseCategory::withTrashed()->findOrFail($id);
        if ($expenseCategory->trashed()) {
            $expenseCategory->restore();
            return redirect()->back()->with('success', 'Expense category restored.');
        } else {
            $expenseCategory->delete();
            return redirect()->back()->with('success', 'Expense category deleted.');
        }
    }

    public function meetingPurposes(Request $request)
    {
        $query = MeetingPurpose::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = MeetingPurpose::onlyTrashed()->count();
        return view('admin.masters.meeting-purposes', compact('items', 'inactiveCount'));
    }

    public function meetingPurposesTrashed(Request $request)
    {
        $query = MeetingPurpose::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = MeetingPurpose::count();
        return view('admin.masters.meeting-purposes-trashed', compact('items', 'activeCount'));
    }

    public function storeMeetingPurpose(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:meeting_purposes,name']);
        MeetingPurpose::create(['name' => $request->name, 'sort_order' => MeetingPurpose::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.meeting-purposes')->with('success', 'Meeting purpose added successfully.');
    }

    public function updateMeetingPurpose(Request $request, MeetingPurpose $meetingPurpose)
    {
        $request->validate(['name' => 'required|string|max:20|unique:meeting_purposes,name,' . $meetingPurpose->id]);
        $meetingPurpose->update(['name' => $request->name]);
        return redirect()->route('admin.masters.meeting-purposes')->with('success', 'Meeting purpose updated successfully.');
    }

    public function toggleMeetingPurpose($id)
    {
        $meetingPurpose = MeetingPurpose::withTrashed()->findOrFail($id);
        if ($meetingPurpose->trashed()) {
            $meetingPurpose->restore();
            return redirect()->back()->with('success', 'Meeting purpose restored.');
        } else {
            $meetingPurpose->delete();
            return redirect()->back()->with('success', 'Meeting purpose deleted.');
        }
    }

    public function updateTypes(Request $request)
    {
        $query = UpdateType::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = UpdateType::onlyTrashed()->count();
        return view('admin.masters.update-types', compact('items', 'inactiveCount'));
    }

    public function updateTypesTrashed(Request $request)
    {
        $query = UpdateType::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = UpdateType::count();
        return view('admin.masters.update-types-trashed', compact('items', 'activeCount'));
    }

    public function storeUpdateType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:update_types,name', 'icon' => 'nullable|string|max:50', 'color' => 'nullable|string|max:20']);
        UpdateType::create(['name' => $request->name, 'icon' => $request->icon ?? 'bi-sticky', 'color' => $request->color ?? 'secondary', 'sort_order' => UpdateType::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.update-types')->with('success', 'Update type added successfully.');
    }

    public function updateUpdateType(Request $request, UpdateType $updateType)
    {
        $request->validate(['name' => 'required|string|max:20|unique:update_types,name,' . $updateType->id, 'icon' => 'nullable|string|max:50', 'color' => 'nullable|string|max:20']);
        $updateType->update(['name' => $request->name, 'icon' => $request->icon ?? 'bi-sticky', 'color' => $request->color ?? 'secondary']);
        return redirect()->route('admin.masters.update-types')->with('success', 'Update type updated successfully.');
    }

    public function toggleUpdateType($id)
    {
        $updateType = UpdateType::withTrashed()->findOrFail($id);
        if ($updateType->trashed()) {
            $updateType->restore();
            return redirect()->back()->with('success', 'Update type restored.');
        } else {
            $updateType->delete();
            return redirect()->back()->with('success', 'Update type deleted.');
        }
    }

    public function leadStatuses(Request $request)
    {
        $query = LeadStatus::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = LeadStatus::onlyTrashed()->count();
        return view('admin.masters.lead-statuses', compact('items', 'inactiveCount'));
    }

    public function leadStatusesTrashed(Request $request)
    {
        $query = LeadStatus::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = LeadStatus::count();
        return view('admin.masters.lead-statuses-trashed', compact('items', 'activeCount'));
    }

    public function storeLeadStatus(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:lead_statuses,name', 'color' => 'nullable|string|max:20']);
        LeadStatus::create(['name' => $request->name, 'color' => $request->color ?? 'secondary', 'sort_order' => LeadStatus::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.lead-statuses')->with('success', 'Lead status added successfully.');
    }

    public function updateLeadStatus(Request $request, LeadStatus $leadStatus)
    {
        $request->validate(['name' => 'required|string|max:20|unique:lead_statuses,name,' . $leadStatus->id, 'color' => 'nullable|string|max:20']);
        $leadStatus->update(['name' => $request->name, 'color' => $request->color ?? 'secondary']);
        return redirect()->route('admin.masters.lead-statuses')->with('success', 'Lead status updated successfully.');
    }

    public function toggleLeadStatus($id)
    {
        $leadStatus = LeadStatus::withTrashed()->findOrFail($id);
        if ($leadStatus->trashed()) {
            $leadStatus->restore();
            return redirect()->back()->with('success', 'Lead status restored.');
        } else {
            $leadStatus->delete();
            return redirect()->back()->with('success', 'Lead status deleted.');
        }
    }

    public function units(Request $request)
    {
        $query = Unit::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('short_name', 'like', '%' . $request->search . '%');
            });
        }
        $items = $query->get();
        $inactiveCount = Unit::onlyTrashed()->count();
        return view('admin.masters.units', compact('items', 'inactiveCount'));
    }

    public function unitsTrashed(Request $request)
    {
        $query = Unit::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('short_name', 'like', '%' . $request->search . '%');
            });
        }
        $items = $query->get();
        $activeCount = Unit::count();
        return view('admin.masters.units-trashed', compact('items', 'activeCount'));
    }

    public function storeUnit(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:units,name', 'short_name' => 'nullable|string|max:10']);
        Unit::create(['name' => $request->name, 'short_name' => $request->short_name, 'sort_order' => Unit::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.units')->with('success', 'Unit added successfully.');
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $request->validate(['name' => 'required|string|max:20|unique:units,name,' . $unit->id, 'short_name' => 'nullable|string|max:10']);
        $unit->update(['name' => $request->name, 'short_name' => $request->short_name]);
        return redirect()->route('admin.masters.units')->with('success', 'Unit updated successfully.');
    }

    public function toggleUnit($id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        if ($unit->trashed()) {
            $unit->restore();
            return redirect()->back()->with('success', 'Unit restored.');
        } else {
            $unit->delete();
            return redirect()->back()->with('success', 'Unit deleted.');
        }
    }

    public function paymentModes(Request $request)
    {
        $query = PaymentMode::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = PaymentMode::onlyTrashed()->count();
        return view('admin.masters.payment-modes', compact('items', 'inactiveCount'));
    }

    public function paymentModesTrashed(Request $request)
    {
        $query = PaymentMode::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = PaymentMode::count();
        return view('admin.masters.payment-modes-trashed', compact('items', 'activeCount'));
    }

    public function storePaymentMode(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:payment_modes,name', 'slug' => 'nullable|string|max:50']);
        $slug = $request->slug ?: \Str::slug($request->name, '_');
        PaymentMode::create(['name' => $request->name, 'slug' => $slug, 'sort_order' => PaymentMode::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.payment-modes')->with('success', 'Payment mode added successfully.');
    }

    public function updatePaymentMode(Request $request, PaymentMode $paymentMode)
    {
        $request->validate(['name' => 'required|string|max:20|unique:payment_modes,name,' . $paymentMode->id, 'slug' => 'nullable|string|max:50']);
        $paymentMode->update(['name' => $request->name, 'slug' => $request->slug ?: $paymentMode->slug]);
        return redirect()->route('admin.masters.payment-modes')->with('success', 'Payment mode updated successfully.');
    }

    public function togglePaymentMode($id)
    {
        $paymentMode = PaymentMode::withTrashed()->findOrFail($id);
        if ($paymentMode->trashed()) {
            $paymentMode->restore();
            return redirect()->back()->with('success', 'Payment mode restored.');
        } else {
            $paymentMode->delete();
            return redirect()->back()->with('success', 'Payment mode deleted.');
        }
    }

    public function gstRates(Request $request)
    {
        $query = GstRate::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = GstRate::onlyTrashed()->count();
        return view('admin.masters.gst-rates', compact('items', 'inactiveCount'));
    }

    public function gstRatesTrashed(Request $request)
    {
        $query = GstRate::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = GstRate::count();
        return view('admin.masters.gst-rates-trashed', compact('items', 'activeCount'));
    }

    public function storeGstRate(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:gst_rates,name', 'percentage' => 'required|numeric|min:0|max:100']);
        GstRate::create(['name' => $request->name, 'percentage' => $request->percentage, 'sort_order' => GstRate::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.gst-rates')->with('success', 'GST rate added successfully.');
    }

    public function updateGstRate(Request $request, GstRate $gstRate)
    {
        $request->validate(['name' => 'required|string|max:20|unique:gst_rates,name,' . $gstRate->id, 'percentage' => 'required|numeric|min:0|max:100']);
        $gstRate->update(['name' => $request->name, 'percentage' => $request->percentage]);
        return redirect()->route('admin.masters.gst-rates')->with('success', 'GST rate updated successfully.');
    }

    public function toggleGstRate($id)
    {
        $gstRate = GstRate::withTrashed()->findOrFail($id);
        if ($gstRate->trashed()) {
            $gstRate->restore();
            return redirect()->back()->with('success', 'GST rate restored.');
        } else {
            $gstRate->delete();
            return redirect()->back()->with('success', 'GST rate deleted.');
        }
    }

    public function tripStatuses(Request $request)
    {
        $query = TripStatus::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = TripStatus::onlyTrashed()->count();
        return view('admin.masters.trip-statuses', compact('items', 'inactiveCount'));
    }

    public function tripStatusesTrashed(Request $request)
    {
        $query = TripStatus::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = TripStatus::count();
        return view('admin.masters.trip-statuses-trashed', compact('items', 'activeCount'));
    }

    public function storeTripStatus(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:trip_statuses,name', 'slug' => 'nullable|string|max:30', 'color' => 'nullable|string|max:20']);
        $slug = $request->slug ?: \Str::slug($request->name, '_');
        TripStatus::create(['name' => $request->name, 'slug' => $slug, 'color' => $request->color ?? 'secondary', 'sort_order' => TripStatus::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.trip-statuses')->with('success', 'Trip status added successfully.');
    }

    public function updateTripStatus(Request $request, TripStatus $tripStatus)
    {
        $request->validate(['name' => 'required|string|max:20|unique:trip_statuses,name,' . $tripStatus->id, 'slug' => 'nullable|string|max:30', 'color' => 'nullable|string|max:20']);
        $tripStatus->update(['name' => $request->name, 'slug' => $request->slug ?: $tripStatus->slug, 'color' => $request->color ?? 'secondary']);
        return redirect()->route('admin.masters.trip-statuses')->with('success', 'Trip status updated successfully.');
    }

    public function toggleTripStatus($id)
    {
        $tripStatus = TripStatus::withTrashed()->findOrFail($id);
        if ($tripStatus->trashed()) {
            $tripStatus->restore();
            return redirect()->back()->with('success', 'Trip status restored.');
        } else {
            $tripStatus->delete();
            return redirect()->back()->with('success', 'Trip status deleted.');
        }
    }

    public function expenseTypes(Request $request)
    {
        $query = ExpenseType::orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = ExpenseType::onlyTrashed()->count();
        return view('admin.masters.expense-types', compact('items', 'inactiveCount'));
    }

    public function expenseTypesTrashed(Request $request)
    {
        $query = ExpenseType::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = ExpenseType::count();
        return view('admin.masters.expense-types-trashed', compact('items', 'activeCount'));
    }

    public function storeExpenseType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:expense_types,name', 'slug' => 'nullable|string|max:30']);
        $slug = $request->slug ?: \Str::slug($request->name, '_');
        ExpenseType::create(['name' => $request->name, 'slug' => $slug, 'sort_order' => ExpenseType::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.expense-types')->with('success', 'Expense type added successfully.');
    }

    public function updateExpenseType(Request $request, ExpenseType $expenseType)
    {
        $request->validate(['name' => 'required|string|max:20|unique:expense_types,name,' . $expenseType->id, 'slug' => 'nullable|string|max:30']);
        $expenseType->update(['name' => $request->name, 'slug' => $request->slug ?: $expenseType->slug]);
        return redirect()->route('admin.masters.expense-types')->with('success', 'Expense type updated successfully.');
    }

    public function toggleExpenseType($id)
    {
        $expenseType = ExpenseType::withTrashed()->findOrFail($id);
        if ($expenseType->trashed()) {
            $expenseType->restore();
            return redirect()->back()->with('success', 'Expense type restored.');
        } else {
            $expenseType->delete();
            return redirect()->back()->with('success', 'Expense type deleted.');
        }
    }

    public function taskStatuses(Request $request)
    {
        $query = TaskStatus::orderBy('sort_order')->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = TaskStatus::onlyTrashed()->count();
        return view('admin.masters.task-statuses', compact('items', 'inactiveCount'));
    }

    public function taskStatusesTrashed(Request $request)
    {
        $query = TaskStatus::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = TaskStatus::count();
        return view('admin.masters.task-statuses-trashed', compact('items', 'activeCount'));
    }

    public function storeTaskStatus(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:task_statuses,name', 'color' => 'nullable|string|max:20']);
        TaskStatus::create(['name' => $request->name, 'color' => $request->color ?? 'secondary', 'sort_order' => TaskStatus::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.task-statuses')->with('success', 'Task status added successfully.');
    }

    public function updateTaskStatus(Request $request, TaskStatus $taskStatus)
    {
        $request->validate(['name' => 'required|string|max:20|unique:task_statuses,name,' . $taskStatus->id, 'color' => 'nullable|string|max:20']);
        $taskStatus->update(['name' => $request->name, 'color' => $request->color ?? 'secondary']);
        return redirect()->route('admin.masters.task-statuses')->with('success', 'Task status updated successfully.');
    }

    public function toggleTaskStatus($id)
    {
        $taskStatus = TaskStatus::withTrashed()->findOrFail($id);
        if ($taskStatus->trashed()) {
            $taskStatus->restore();
            return redirect()->back()->with('success', 'Task status restored.');
        } else {
            $taskStatus->delete();
            return redirect()->back()->with('success', 'Task status deleted.');
        }
    }

    public function staffPositions(Request $request)
    {
        $query = StaffPosition::orderBy('sort_order')->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = StaffPosition::onlyTrashed()->count();
        return view('admin.masters.staff-positions', compact('items', 'inactiveCount'));
    }

    public function staffPositionsTrashed(Request $request)
    {
        $query = StaffPosition::onlyTrashed()->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = StaffPosition::count();
        return view('admin.masters.staff-positions-trashed', compact('items', 'activeCount'));
    }

    public function storeStaffPosition(Request $request)
    {
        $request->validate(['name' => 'required|string|max:30|unique:staff_positions,name']);
        StaffPosition::create(['name' => $request->name, 'sort_order' => StaffPosition::withTrashed()->max('sort_order') + 1]);
        return redirect()->route('admin.masters.staff-positions')->with('success', 'Staff position added successfully.');
    }

    public function updateStaffPosition(Request $request, StaffPosition $staffPosition)
    {
        $request->validate(['name' => 'required|string|max:30|unique:staff_positions,name,' . $staffPosition->id]);
        $staffPosition->update(['name' => $request->name]);
        return redirect()->route('admin.masters.staff-positions')->with('success', 'Staff position updated successfully.');
    }

    public function toggleStaffPosition($id)
    {
        $staffPosition = StaffPosition::withTrashed()->findOrFail($id);
        if ($staffPosition->trashed()) {
            $staffPosition->restore();
            return redirect()->back()->with('success', 'Staff position restored.');
        } else {
            $staffPosition->delete();
            return redirect()->back()->with('success', 'Staff position deleted.');
        }
    }

    public function services(Request $request)
    {
        $query = Service::orderBy('sort_order')->orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = Service::where('is_active', false)->count();
        return view('admin.masters.services', compact('items', 'inactiveCount'));
    }

    public function servicesTrashed(Request $request)
    {
        $query = Service::where('is_active', false)->orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = Service::where('is_active', true)->count();
        return view('admin.masters.services-trashed', compact('items', 'activeCount'));
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:services,name',
            'price' => 'nullable|numeric|min:0',
            'admin_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $validated['sort_order'] = Service::max('sort_order') + 1;
        Service::create($validated);
        return redirect()->route('admin.masters.services')->with('success', 'Service added successfully.');
    }

    public function updateService(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:services,name,' . $service->id,
            'price' => 'nullable|numeric|min:0',
            'admin_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $service->update($validated);
        return redirect()->route('admin.masters.services')->with('success', 'Service updated successfully.');
    }

    public function toggleService(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        $message = $service->is_active ? 'Service activated.' : 'Service deactivated.';
        return redirect()->back()->with('success', $message);
    }

    public function passengerTypes(Request $request)
    {
        $query = PassengerType::orderBy('sort_order')->orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = PassengerType::where('is_active', false)->count();
        return view('admin.masters.passenger-types', compact('items', 'inactiveCount'));
    }

    public function passengerTypesTrashed(Request $request)
    {
        $query = PassengerType::where('is_active', false)->orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = PassengerType::where('is_active', true)->count();
        return view('admin.masters.passenger-types-trashed', compact('items', 'activeCount'));
    }

    public function storePassengerType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:passenger_types,name']);
        PassengerType::create(['name' => $request->name, 'sort_order' => PassengerType::max('sort_order') + 1]);
        return redirect()->route('admin.masters.passenger-types')->with('success', 'Passenger type added successfully.');
    }

    public function updatePassengerType(Request $request, PassengerType $passengerType)
    {
        $request->validate(['name' => 'required|string|max:100|unique:passenger_types,name,' . $passengerType->id]);
        $passengerType->update(['name' => $request->name]);
        return redirect()->route('admin.masters.passenger-types')->with('success', 'Passenger type updated successfully.');
    }

    public function togglePassengerType(PassengerType $passengerType)
    {
        $passengerType->update(['is_active' => !$passengerType->is_active]);
        $message = $passengerType->is_active ? 'Passenger type activated.' : 'Passenger type deactivated.';
        return redirect()->back()->with('success', $message);
    }

    /*
     * Terms & Conditions / Payment Terms share one table (term_templates),
     * routed as /masters/terms-conditions and /masters/payment-terms.
     */
    private const TERM_TEMPLATE_TYPES = [
        'terms-conditions' => ['type' => TermTemplate::TYPE_TERMS, 'label' => 'Terms & Conditions', 'singular' => 'Terms & Conditions', 'icon' => 'bi-file-text'],
        'payment-terms'    => ['type' => TermTemplate::TYPE_PAYMENT, 'label' => 'Payment Terms', 'singular' => 'Payment Terms', 'icon' => 'bi-cash-coin'],
    ];

    private function termTemplateConfig(string $slug): array
    {
        abort_unless(isset(self::TERM_TEMPLATE_TYPES[$slug]), 404);
        return self::TERM_TEMPLATE_TYPES[$slug] + ['slug' => $slug];
    }

    public function termTemplates(Request $request, string $type)
    {
        $config = $this->termTemplateConfig($type);
        $query = TermTemplate::ofType($config['type'])->orderByDesc('is_default')->ordered();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $inactiveCount = TermTemplate::ofType($config['type'])->onlyTrashed()->count();
        return view('admin.masters.term-templates', compact('items', 'inactiveCount', 'config'));
    }

    public function termTemplatesTrashed(Request $request, string $type)
    {
        $config = $this->termTemplateConfig($type);
        $query = TermTemplate::ofType($config['type'])->onlyTrashed()->orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $items = $query->get();
        $activeCount = TermTemplate::ofType($config['type'])->count();
        return view('admin.masters.term-templates-trashed', compact('items', 'activeCount', 'config'));
    }

    public function storeTermTemplate(Request $request, string $type)
    {
        $config = $this->termTemplateConfig($type);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'content' => 'required|string|max:5000',
        ]);

        $template = TermTemplate::create([
            'type' => $config['type'],
            'name' => $validated['name'],
            'content' => $validated['content'],
            'sort_order' => TermTemplate::withTrashed()->ofType($config['type'])->max('sort_order') + 1,
        ]);
        $this->syncDefaultTermTemplate($template, $request->boolean('is_default'));

        return redirect()->route('admin.masters.term-templates', $type)->with('success', $config['singular'] . ' added successfully.');
    }

    public function updateTermTemplate(Request $request, string $type, TermTemplate $termTemplate)
    {
        $config = $this->termTemplateConfig($type);
        abort_unless($termTemplate->type === $config['type'], 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'content' => 'required|string|max:5000',
        ]);

        $termTemplate->update($validated);
        $this->syncDefaultTermTemplate($termTemplate, $request->boolean('is_default'));

        return redirect()->route('admin.masters.term-templates', $type)->with('success', $config['singular'] . ' updated successfully.');
    }

    public function toggleTermTemplate(string $type, $id)
    {
        $config = $this->termTemplateConfig($type);
        $template = TermTemplate::withTrashed()->ofType($config['type'])->findOrFail($id);
        if ($template->trashed()) {
            $template->restore();
            return redirect()->back()->with('success', $config['singular'] . ' restored.');
        }

        $template->update(['is_default' => false]);
        $template->delete();
        return redirect()->back()->with('success', $config['singular'] . ' deleted.');
    }

    /**
     * Only one default per type.
     */
    private function syncDefaultTermTemplate(TermTemplate $template, bool $isDefault): void
    {
        if ($isDefault) {
            TermTemplate::withTrashed()->ofType($template->type)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }
        $template->update(['is_default' => $isDefault]);
    }
}
