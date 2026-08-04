<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\GstRate;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectService;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Task;
use App\Models\Vendor;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['customer', 'company']);

        $fyDates = getFinancialYearDates();
        if ($fyDates && $request->tab !== 'deleted') {
            $query->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }

        if ($request->tab === 'deleted') {
            $query->onlyTrashed();
            if ($fyDates) {
                $query->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
            }
        } elseif ($request->tab === 'planning') {
            $query->where('status', 'planning');
        } elseif ($request->tab === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($request->tab === 'on_hold') {
            $query->where('status', 'on_hold');
        } elseif ($request->tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($request->tab === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('site_address', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $request->input('per_page', 15);
        $projects = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $baseQuery = Project::query();
        if ($fyDates) {
            $baseQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }

        $allCount = (clone $baseQuery)->count();
        $planningCount = (clone $baseQuery)->where('status', 'planning')->count();
        $inProgressCount = (clone $baseQuery)->where('status', 'in_progress')->count();
        $onHoldCount = (clone $baseQuery)->where('status', 'on_hold')->count();
        $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

        $deletedQuery = Project::onlyTrashed();
        if ($fyDates) {
            $deletedQuery->whereBetween('created_at', [$fyDates['start'], $fyDates['end']]);
        }
        $deletedCount = $deletedQuery->count();

        $stats = [
            'total' => $allCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount,
            'total_value' => (clone $baseQuery)->sum('budget'),
        ];

        return view('admin.projects.index', compact(
            'projects',
            'stats',
            'allCount',
            'planningCount',
            'inProgressCount',
            'onHoldCount',
            'completedCount',
            'cancelledCount',
            'deletedCount'
        ));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $workTypes = WorkType::active()->ordered()->get();
        $quotations = Quotation::where('status', 'accepted')->orderBy('created_at', 'desc')->get();
        $staff = Staff::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $services = Service::active()->ordered()->get();
        $banks = Bank::orderBy('bank_name')->get();
        $gstRates = GstRate::active()->get();

        return view('admin.projects.create', compact('companies', 'customers', 'workTypes', 'quotations', 'staff', 'vendors', 'services', 'banks', 'gstRates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:40',
            'customer_id' => 'required|exists:customers,id',
            'company_id' => 'nullable|exists:companies,id',
            'work_type' => 'nullable|array',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:planning,in_progress,on_hold,completed,cancelled',
            'site_address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'budget' => 'required|numeric|min:0|max:99999999.99',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'advance_received' => 'nullable|numeric|min:0|max:99999999.99',
            'quotation_id' => 'nullable|exists:quotations,id',
            'assigned_staff_ids' => 'nullable|array',
            'assigned_staff_ids.*' => 'exists:staff,id',
            'assigned_vendor_ids' => 'nullable|array',
            'assigned_vendor_ids.*' => 'exists:vendors,id',
            'notes' => 'nullable|string|max:150',
            'services' => 'nullable|array',
            'services.*.service_ids' => 'required|array|min:1',
            'services.*.service_ids.*' => 'exists:services,id',
            'services.*.amount' => 'required|numeric|min:1',
            'services.*.advance' => 'nullable|numeric|min:0',
            'services.*.advance_bank_id' => 'nullable|exists:banks,id',
            'services.*.due_date' => 'nullable|date',
            'services.*.note' => 'nullable|string|max:500',
        ]);

        foreach ($request->input('services', []) as $i => $svc) {
            $advance = (float)($svc['advance'] ?? 0);
            if ($advance > 0 && empty($svc['advance_bank_id'])) {
                return back()->withInput()->withErrors([
                    "services.$i.advance_bank_id" => 'Bank is required when advance is greater than 0.',
                ]);
            }
        }

        $validated['status'] = $validated['status'] ?? 'planning';
        $validated['advance_received'] = $validated['advance_received'] ?? 0;
        $validated['assigned_staff_ids'] = $request->assigned_staff_ids ?? [];
        $validated['assigned_vendor_ids'] = $request->assigned_vendor_ids ?? [];
        $validated = $this->computeGstFields($request, $validated);

        $servicesPayload = $validated['services'] ?? [];
        unset($validated['services']);

        if ($validated['status'] === 'completed' && !empty($servicesPayload)) {
            $servicePending = 0;
            foreach ($servicesPayload as $svc) {
                $servicePending += max(0, (float)($svc['amount'] ?? 0) - (float)($svc['advance'] ?? 0));
            }
            if ($servicePending > 0) {
                return back()->withInput()->with('error',
                    'Cannot create project as completed. Pending amount to pay for services: ₹' . number_format($servicePending, 2));
            }
        }

        $project = DB::transaction(function () use ($validated, $servicesPayload) {
            $project = Project::create($validated);
            foreach ($servicesPayload as $svc) {
                $this->createProjectService($project, $svc);
            }
            return $project;
        });

        if ($request->hasFile('project_files')) {
            foreach ($request->file('project_files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('projects/' . $project->id, $fileName, 'public');

                ProjectFile::create([
                    'project_id' => $project->id,
                    'file_name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load(['customer', 'company', 'quotation', 'expenses.category', 'expenses.vendor', 'incomes', 'invoices', 'addons', 'files', 'projectServices.addons', 'projectServices.serviceExpenses.bank']);

        $tasks = Task::where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->with(['status'])
            ->orderBy('due_at')
            ->get();

        $banks = Bank::orderBy('bank_name')->get();
        $services = Service::active()->ordered()->get();

        return view('admin.projects.show', compact('project', 'tasks', 'banks', 'services'));
    }

    public function edit(Project $project)
    {
        $project->load(['files', 'projectServices.addons', 'projectServices.serviceExpenses.bank']);
        $companies = Company::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $workTypes = WorkType::active()->ordered()->get();
        $quotations = Quotation::orderBy('created_at', 'desc')->get();
        $staff = Staff::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $services = Service::active()->ordered()->get();
        $banks = Bank::orderBy('bank_name')->get();
        $gstRates = GstRate::active()->get();

        return view('admin.projects.edit', compact('project', 'companies', 'customers', 'workTypes', 'quotations', 'staff', 'vendors', 'services', 'banks', 'gstRates'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:40',
            'customer_id' => 'required|exists:customers,id',
            'company_id' => 'nullable|exists:companies,id',
            'work_type' => 'nullable|array',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date',
            'status' => 'nullable|string|in:planning,in_progress,on_hold,completed,cancelled',
            'site_address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'budget' => 'required|numeric|min:0|max:99999999.99',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'advance_received' => 'nullable|numeric|min:0|max:99999999.99',
            'quotation_id' => 'nullable|exists:quotations,id',
            'assigned_staff_ids' => 'nullable|array',
            'assigned_staff_ids.*' => 'exists:staff,id',
            'assigned_vendor_ids' => 'nullable|array',
            'assigned_vendor_ids.*' => 'exists:vendors,id',
            'notes' => 'nullable|string|max:150',
            'services' => 'nullable|array',
            'services.*.id' => 'nullable|exists:project_services,id',
            'services.*.service_ids' => 'required|array|min:1',
            'services.*.service_ids.*' => 'exists:services,id',
            'services.*.amount' => 'required|numeric|min:1',
            'services.*.advance' => 'nullable|numeric|min:0',
            'services.*.advance_bank_id' => 'nullable|exists:banks,id',
            'services.*.due_date' => 'nullable|date',
            'services.*.note' => 'nullable|string|max:500',
        ]);

        foreach ($request->input('services', []) as $i => $svc) {
            $advance = (float)($svc['advance'] ?? 0);
            if ($advance > 0 && empty($svc['advance_bank_id'])) {
                return back()->withInput()->withErrors([
                    "services.$i.advance_bank_id" => 'Bank is required when advance is greater than 0.',
                ]);
            }
        }

        $validated['assigned_staff_ids'] = $request->assigned_staff_ids ?? [];
        $validated['assigned_vendor_ids'] = $request->assigned_vendor_ids ?? [];
        $validated = $this->computeGstFields($request, $validated);

        $servicesPayload = $validated['services'] ?? [];
        unset($validated['services']);

        $markingCompleted = ($validated['status'] ?? null) === 'completed';

        try {
            DB::transaction(function () use ($project, $validated, $servicesPayload, $markingCompleted) {
                $project->update($validated);
                $this->syncProjectServices($project, $servicesPayload);

                if ($markingCompleted) {
                    $project->refresh()->load('projectServices');
                    if ($project->hasPendingPayments()) {
                        $summary = $project->getPendingPaymentsSummary();
                        $errors = [];
                        if ($summary['pending_to_receive'] > 0) {
                            $errors[] = 'Pending amount to receive from customer: ₹' . number_format($summary['pending_to_receive'], 2);
                        }
                        if ($summary['pending_to_give'] > 0) {
                            $errors[] = 'Pending amount to pay to vendors: ₹' . number_format($summary['pending_to_give'], 2);
                        }
                        if ($summary['service_pending'] > 0) {
                            $errors[] = 'Pending amount to pay for services: ₹' . number_format($summary['service_pending'], 2);
                        }
                        throw new \RuntimeException('Cannot mark project as completed. ' . implode(' | ', $errors));
                    }
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->hasFile('project_files')) {
            foreach ($request->file('project_files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('projects/' . $project->id, $fileName, 'public');

                ProjectFile::create([
                    'project_id' => $project->id,
                    'file_name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('admin.projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    public function restore($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->restore();
        return redirect()->route('admin.projects.index')->with('success', 'Project restored successfully.');
    }

    public function deleteFile(ProjectFile $projectFile)
    {
        Storage::disk('public')->delete($projectFile->file_path);
        $projectFile->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Compute and normalise the GST fields. GST only applies when a company that
     * has a GST number is assigned; otherwise all GST values are zeroed.
     */
    protected function computeGstFields(Request $request, array $validated): array
    {
        $companyHasGst = false;
        if (!empty($validated['company_id'])) {
            $company = Company::find($validated['company_id']);
            $companyHasGst = $company && !empty($company->gst_number);
        }

        $percent = $companyHasGst ? (float) $request->input('gst_percent', 0) : 0;
        $inclusive = $companyHasGst && $request->has('gst_inclusive');
        $split = $companyHasGst && $request->has('gst_split');
        $budget = (float) ($validated['budget'] ?? 0);

        if ($percent > 0) {
            $gstAmount = $inclusive
                ? $budget * $percent / (100 + $percent)
                : $budget * $percent / 100;
        } else {
            $gstAmount = 0;
        }

        $validated['gst_percent'] = $percent;
        $validated['gst_inclusive'] = $inclusive;
        $validated['gst_split'] = $split;
        $validated['gst_amount'] = round($gstAmount, 2);

        return $validated;
    }

    protected function createProjectService(Project $project, array $svc): ProjectService
    {
        $serviceIds = array_values(array_unique(array_map('intval', $svc['service_ids'] ?? [])));
        $advance = (float)($svc['advance'] ?? 0);
        $bankId = $advance > 0 ? ($svc['advance_bank_id'] ?? null) : null;

        $row = ProjectService::create([
            'project_id' => $project->id,
            'service_ids' => $serviceIds,
            'amount' => $svc['amount'],
            'advance' => $advance,
            'advance_bank_id' => $bankId,
            'due_date' => $svc['due_date'] ?? null,
            'note' => $svc['note'] ?? null,
        ]);

        if ($advance > 0 && $bankId) {
            Expense::create([
                'expense_type' => 'service',
                'expense_date' => now()->toDateString(),
                'project_id' => $project->id,
                'project_service_id' => $row->id,
                'bank_id' => $bankId,
                'sub_total' => $advance,
                'gst_percentage' => 0,
                'gst_amount' => 0,
                'grand_total' => $advance,
                'paid_amount' => $advance,
                'payment_status' => 'paid',
                'description' => 'Service Advance',
            ]);
        }

        return $row;
    }

    protected function syncProjectServices(Project $project, array $servicesPayload): void
    {
        $submittedIds = [];
        foreach ($servicesPayload as $svc) {
            if (!empty($svc['id'])) {
                $row = ProjectService::where('project_id', $project->id)->find($svc['id']);
                if ($row) {
                    $serviceIds = array_values(array_unique(array_map('intval', $svc['service_ids'] ?? [])));
                    $row->update([
                        'service_ids' => $serviceIds,
                        'amount' => $svc['amount'],
                        'due_date' => $svc['due_date'] ?? null,
                        'note' => $svc['note'] ?? null,
                    ]);
                    $submittedIds[] = $row->id;
                }
            } else {
                $created = $this->createProjectService($project, $svc);
                $submittedIds[] = $created->id;
            }
        }

        $toDelete = $project->projectServices()->whereNotIn('id', $submittedIds)->get();
        foreach ($toDelete as $svc) {
            $svc->serviceExpenses()->delete();
            $svc->addons()->delete();
            $svc->delete();
        }
    }

    public function export(Project $project)
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '_', trim($project->name ?: $project->project_number));
        $slug = trim($slug, '_');
        $filename = $slug . '_' . now()->format('d_m_Y_His') . '.xlsx';

        return (new \App\Exports\ProjectExport($project))->download($filename);
    }

    public function recordExpensePayment(Request $request, Project $project, Expense $expense)
    {
        if ($expense->project_id !== $project->id) {
            return back()->with('error', 'Invalid expense.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $expense->balance,
            'bank_id' => 'required|exists:banks,id',
            'payment_date' => 'nullable|date',
        ]);

        $paymentAmount = $validated['amount'];
        $paymentDate = $validated['payment_date'] ?? now()->toDateString();

        $expense->paid_amount += $paymentAmount;
        $expense->bank_id = $validated['bank_id'];
        $expense->updatePaymentStatus();

        return redirect()->route('admin.projects.show', $project)->with('success', 'Payment of ' . formatMoney($paymentAmount) . ' recorded successfully.');
    }
}
