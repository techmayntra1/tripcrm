<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with('position');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $perPage = $request->input('per_page', 15);
        $staffMembers = $query->orderBy('joining_date', 'desc')->paginate($perPage)->withQueryString();

        $activeCount = Staff::count();
        $inactiveCount = Staff::onlyTrashed()->count();

        return view('admin.staff.index', compact(
            'staffMembers',
            'activeCount',
            'inactiveCount'
        ));
    }

    public function trashed(Request $request)
    {
        $query = Staff::with('position')->onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $perPage = $request->input('per_page', 15);
        $staffMembers = $query->orderBy('joining_date', 'desc')->paginate($perPage)->withQueryString();

        $activeCount = Staff::count();

        return view('admin.staff.trashed', compact(
            'staffMembers',
            'activeCount'
        ));
    }

    public function create()
    {
        $positions = StaffPosition::active()->ordered()->get();
        return view('admin.staff.create', compact('positions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'position_id' => 'required|exists:staff_positions,id',
            'joining_date' => 'required|date',
            'aadhar_number' => 'nullable|string|size:12',
            'pan_number' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'salary_amount' => 'required|numeric|min:1|max:1000000',
            'overtime_rate' => 'nullable|numeric|min:0|max:10000',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:20',
            'ifsc_code' => 'nullable|string|max:11',
            'pan_card' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'aadhar_front' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'aadhar_back' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'salary_amount.min' => 'Salary amount cannot be 0.',
            'salary_amount.max' => 'Salary amount is too high.',
            'overtime_rate.max' => 'Overtime rate is too high.',
            'aadhar_front.required' => 'Aadhar front image is required.',
            'aadhar_back.required' => 'Aadhar back image is required.',
            'pan_card.image' => 'PAN card must be an image.',
            'aadhar_front.image' => 'Aadhar front must be an image.',
            'aadhar_back.image' => 'Aadhar back must be an image.',
        ]);

        $validated['overtime_rate'] = $validated['overtime_rate'] ?? 0;

       
        if ($request->hasFile('pan_card')) {
            $validated['pan_card'] = $request->file('pan_card')->store('staff/documents', 'public');
        }
        if ($request->hasFile('aadhar_front')) {
            $validated['aadhar_front'] = $request->file('aadhar_front')->store('staff/documents', 'public');
        }
        if ($request->hasFile('aadhar_back')) {
            $validated['aadhar_back'] = $request->file('aadhar_back')->store('staff/documents', 'public');
        }

        Staff::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    public function show(Staff $staff)
    {
        $staff->load('position');
        $salaryRecords = Expense::where('expense_type', 'salary')
            ->where('staff_id', $staff->id)
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();

        $trips = \App\Models\Trip::where(function($q) use ($staff) {
                $q->where('assigned_staff_id', $staff->id)
                  ->orWhereJsonContains('assigned_staff_ids', $staff->id);
            })
            ->get();

        $tasks = Task::where('assignee_type', 'staff')
            ->where('assignee_id', $staff->id)
            ->whereNull('deleted_at')
            ->with(['trip', 'status'])
            ->orderBy('due_at')
            ->get();

        return view('admin.staff.show', compact('staff', 'salaryRecords', 'trips', 'tasks'));
    }

    public function edit(Staff $staff)
    {
        $positions = StaffPosition::active()->ordered()->get();
        return view('admin.staff.edit', compact('staff', 'positions'));
    }

    public function update(Request $request, Staff $staff)
    {
        $rules = [
            'name' => 'required|string|max:30',
            'mobile' => 'required|string|size:10',
            'email' => 'nullable|email|max:100',
            'position_id' => 'required|exists:staff_positions,id',
            'joining_date' => 'required|date',
            'aadhar_number' => 'nullable|string|size:12',
            'pan_number' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'salary_amount' => 'required|numeric|min:1|max:1000000',
            'overtime_rate' => 'nullable|numeric|min:0|max:10000',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:20',
            'ifsc_code' => 'nullable|string|max:11',
            'pan_card' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'aadhar_front' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'aadhar_back' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

       
        if (!$staff->aadhar_front) {
            $rules['aadhar_front'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        }
        if (!$staff->aadhar_back) {
            $rules['aadhar_back'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        }

        $validated = $request->validate($rules, [
            'salary_amount.min' => 'Salary amount cannot be 0.',
            'salary_amount.max' => 'Salary amount is too high.',
            'overtime_rate.max' => 'Overtime rate is too high.',
            'aadhar_front.required' => 'Aadhar front image is required.',
            'aadhar_back.required' => 'Aadhar back image is required.',
        ]);

       
        if ($request->hasFile('pan_card')) {
            if ($staff->pan_card) {
                Storage::disk('public')->delete($staff->pan_card);
            }
            $validated['pan_card'] = $request->file('pan_card')->store('staff/documents', 'public');
        }
        if ($request->hasFile('aadhar_front')) {
            if ($staff->aadhar_front) {
                Storage::disk('public')->delete($staff->aadhar_front);
            }
            $validated['aadhar_front'] = $request->file('aadhar_front')->store('staff/documents', 'public');
        }
        if ($request->hasFile('aadhar_back')) {
            if ($staff->aadhar_back) {
                Storage::disk('public')->delete($staff->aadhar_back);
            }
            $validated['aadhar_back'] = $request->file('aadhar_back')->store('staff/documents', 'public');
        }

        $staff->update($validated);

        return redirect()->route('admin.staff.show', $staff)
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    public function salary(Staff $staff)
    {
        return redirect()->route('admin.staff.salary-payments.index', $staff);
    }

    public function restore($id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);
        $staff->restore();

        return redirect()->route('admin.staff.trashed')
            ->with('success', 'Staff member restored successfully.');
    }
}
