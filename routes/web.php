<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MeetingController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SalaryPaymentController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TripAddonController;
use App\Http\Controllers\Admin\TripServiceController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\FollowUpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/set-financial-year', function () {
    $fy = request()->input('financial_year');
    if (in_array($fy, ['all', 'current', '2025-26', '2024-25', '2023-24'])) {
        session(['financial_year' => $fy]);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 400);
})->middleware('auth')->name('set.financial.year');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications/upcoming', [NotificationController::class, 'upcoming'])->name('notifications.upcoming');
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');


    Route::middleware(['permission:leads,view'])->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/trashed', [LeadController::class, 'trashed'])->name('leads.trashed');
        Route::get('/leads/trashed/{id}', [LeadController::class, 'showTrashed'])->name('leads.trashed.show');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show')->where('lead', '[0-9]+');
        Route::get('/leads/{lead}/follow-ups', [LeadController::class, 'followUps'])->name('leads.follow-ups');
    });
    Route::middleware(['permission:leads,create'])->group(function () {
        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}/convert', [LeadController::class, 'convertForm'])->name('leads.convert');
        Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert.store');
    });
    Route::middleware(['permission:leads,edit'])->group(function () {
        Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::patch('/leads/{lead}', [LeadController::class, 'update']);
        Route::post('/leads/{lead}/meetings', [LeadController::class, 'storeMeeting'])->name('leads.meetings.store');
        Route::post('/leads/{lead}/updates', [LeadController::class, 'storeUpdate'])->name('leads.updates.store');
        Route::delete('/leads/{lead}/updates/{update}', [LeadController::class, 'destroyUpdate'])->name('leads.updates.destroy');
        Route::post('/leads/{lead}/mark-won', [LeadController::class, 'markWon'])->name('leads.mark-won');
        Route::post('/leads/{lead}/mark-lost', [LeadController::class, 'markLost'])->name('leads.mark-lost');
    });
    Route::middleware(['permission:leads,delete'])->group(function () {
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
        Route::post('/leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
    });

   
    Route::middleware(['permission:customers,view'])->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
        Route::get('/customers/trashed/{id}', [CustomerController::class, 'showTrashed'])->name('customers.trashed.show');
        Route::get('/customers/{customer}/income/export', [CustomerController::class, 'exportIncome'])->name('customers.income.export')->where('customer', '[0-9]+');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show')->where('customer', '[0-9]+');
    });
    Route::middleware(['permission:customers,create'])->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    });
    Route::middleware(['permission:customers,edit'])->group(function () {
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
        Route::post('/customers/{customer}/meetings', [CustomerController::class, 'storeMeeting'])->name('customers.meetings.store');
        Route::post('/customers/{customer}/updates', [CustomerController::class, 'storeUpdate'])->name('customers.updates.store');
        Route::post('/customers/{customer}/trips/link', [CustomerController::class, 'linkTrip'])->name('customers.trips.link');
    });
    Route::middleware(['permission:customers,delete'])->group(function () {
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    });

   
    Route::middleware(['permission:meetings,view'])->group(function () {
        Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
        Route::get('/meetings/trashed/{id}', [MeetingController::class, 'showTrashed'])->name('meetings.trashed.show');
        Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show')->where('meeting', '[0-9]+');
    });
    Route::middleware(['permission:meetings,create'])->group(function () {
        Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    });
    Route::middleware(['permission:meetings,edit'])->group(function () {
        Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::post('/meetings/{meeting}/complete', [MeetingController::class, 'complete'])->name('meetings.complete');
        Route::post('/meetings/{meeting}/reschedule', [MeetingController::class, 'reschedule'])->name('meetings.reschedule');
        Route::post('/meetings/{meeting}/deactivate', [MeetingController::class, 'deactivate'])->name('meetings.deactivate');
        Route::post('/meetings/{meeting}/reactivate', [MeetingController::class, 'reactivate'])->name('meetings.reactivate');
        Route::post('/meetings/{meeting}/updates', [MeetingController::class, 'storeUpdate'])->name('meetings.updates.store');
    });
    Route::middleware(['permission:meetings,delete'])->group(function () {
        Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
    });

   
    Route::middleware(['permission:tasks,view'])->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/trashed/{id}', [TaskController::class, 'showTrashed'])->name('tasks.trashed.show');
        Route::get('/tasks/trip/{trip}/address', [TaskController::class, 'getTripAddress'])->name('tasks.trip.address');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show')->where('task', '[0-9]+');
    });
    Route::middleware(['permission:tasks,create'])->group(function () {
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    });
    Route::middleware(['permission:tasks,edit'])->group(function () {
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::post('/tasks/{task}/deactivate', [TaskController::class, 'deactivate'])->name('tasks.deactivate');
        Route::post('/tasks/{id}/reactivate', [TaskController::class, 'reactivate'])->name('tasks.reactivate');
        Route::post('/tasks/{task}/updates', [TaskController::class, 'storeUpdate'])->name('tasks.updates.store');
    });
    Route::middleware(['permission:tasks,delete'])->group(function () {
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });

   
    Route::middleware(['permission:quotations,view'])->group(function () {
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/trashed', [QuotationController::class, 'trashed'])->name('quotations.trashed');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show')->where('quotation', '[0-9]+');
        Route::get('/quotations/{quotation}/download', [QuotationController::class, 'downloadPdf'])->name('quotations.download');
    });
    Route::middleware(['permission:quotations,create'])->group(function () {
        Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    });
    Route::middleware(['permission:quotations,edit'])->group(function () {
        Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::patch('/quotations/{quotation}', [QuotationController::class, 'update']);
        Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');
    });
    Route::middleware(['permission:quotations,delete'])->group(function () {
        Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
        Route::post('/quotations/{id}/restore', [QuotationController::class, 'restore'])->name('quotations.restore');
    });

   
    Route::middleware(['permission:invoices,view'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/trashed', [InvoiceController::class, 'trashed'])->name('invoices.trashed');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show')->where('invoice', '[0-9]+');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    });
    Route::middleware(['permission:invoices,create'])->group(function () {
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    });
    Route::middleware(['permission:invoices,edit'])->group(function () {
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    });
    Route::middleware(['permission:invoices,delete'])->group(function () {
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('/invoices/{id}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
    });

   
    Route::middleware(['permission:vendors,view'])->group(function () {
        Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/trashed', [VendorController::class, 'trashed'])->name('vendors.trashed');
        Route::get('/vendors/trashed/{id}', [VendorController::class, 'showTrashed'])->name('vendors.trashed.show');
        Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show')->where('vendor', '[0-9]+');
    });
    Route::middleware(['permission:vendors,create'])->group(function () {
        Route::get('/vendors/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
    });
    Route::middleware(['permission:vendors,edit'])->group(function () {
        Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::patch('/vendors/{vendor}', [VendorController::class, 'update']);
        Route::post('/vendors/{vendor}/expenses/{expense}/payment', [VendorController::class, 'recordPayment'])->name('vendors.expenses.payment');
        Route::post('/vendors/{vendor}/expenses/{expense}/mark-paid', [VendorController::class, 'markFullyPaid'])->name('vendors.expenses.mark-paid');
    });
    Route::middleware(['permission:vendors,delete'])->group(function () {
        Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
        Route::post('/vendors/{id}/restore', [VendorController::class, 'restore'])->name('vendors.restore');
    });


    Route::middleware(['permission:trips,view'])->group(function () {
        Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
        Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show')->where('trip', '[0-9]+');
        Route::get('/trips/{trip}/export', [TripController::class, 'export'])->name('trips.export')->where('trip', '[0-9]+');
    });
    Route::middleware(['permission:trips,create'])->group(function () {
        Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
        Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    });
    Route::middleware(['permission:trips,edit'])->group(function () {
        Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
        Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
        Route::patch('/trips/{trip}', [TripController::class, 'update']);
        // Trip Files
        Route::delete('/trip-files/{tripFile}', [TripController::class, 'deleteFile'])->name('trips.files.destroy');
        // Trip Addons
        Route::post('/trips/{trip}/addons', [TripAddonController::class, 'store'])->name('trips.addons.store');
        Route::put('/trips/{trip}/addons/{addon}', [TripAddonController::class, 'update'])->name('trips.addons.update');
        Route::delete('/trips/{trip}/addons/{addon}', [TripAddonController::class, 'destroy'])->name('trips.addons.destroy');
        // Trip Services
        Route::post('/trips/{trip}/services', [TripServiceController::class, 'store'])->name('trips.services.store');
        Route::put('/trips/{trip}/services/{service}', [TripServiceController::class, 'update'])->name('trips.services.update');
        Route::delete('/trips/{trip}/services/{service}', [TripServiceController::class, 'destroy'])->name('trips.services.destroy');
        Route::post('/trips/{trip}/services/{service}/payment', [TripServiceController::class, 'addPayment'])->name('trips.services.payment');
        Route::post('/trips/{trip}/expenses/{expense}/payment', [TripController::class, 'recordExpensePayment'])->name('trips.expenses.payment');
        Route::post('/trips/{trip}/services/{service}/addon', [TripServiceController::class, 'addAddon'])->name('trips.services.addon');
        Route::delete('/trips/{trip}/services/{service}/addon/{addon}', [TripServiceController::class, 'deleteAddon'])->name('trips.services.addon.destroy');
    });
    Route::middleware(['permission:trips,delete'])->group(function () {
        Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
        Route::post('/trips/{id}/restore', [TripController::class, 'restore'])->name('trips.restore');
    });

   
    Route::middleware(['permission:staff,view'])->group(function () {
        Route::get('/salary', [SalaryPaymentController::class, 'all'])->name('salary.index');
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/trashed', [StaffController::class, 'trashed'])->name('staff.trashed');
        Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show')->where('staff', '[0-9]+');
        Route::get('/staff/{staff}/salary', [StaffController::class, 'salary'])->name('staff.salary');
        Route::get('/staff/{staff}/salary-payments', [SalaryPaymentController::class, 'index'])->name('staff.salary-payments.index');
        Route::get('/staff/{staff}/salary-payments/{payment}', [SalaryPaymentController::class, 'show'])->name('staff.salary-payments.show')->where('payment', '[0-9]+');
        Route::get('/staff/{staff}/advances/{advance}', [SalaryPaymentController::class, 'showAdvance'])->name('staff.advances.show')->where('advance', '[0-9]+');
    });
    Route::middleware(['permission:staff,create'])->group(function () {
        Route::get('/salary/create', [SalaryPaymentController::class, 'createGlobal'])->name('salary.create');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/salary-payments/create', [SalaryPaymentController::class, 'create'])->name('staff.salary-payments.create');
        Route::post('/staff/{staff}/salary-payments', [SalaryPaymentController::class, 'store'])->name('staff.salary-payments.store');
        Route::get('/staff/{staff}/advances/create', [SalaryPaymentController::class, 'createAdvance'])->name('staff.advances.create');
        Route::post('/staff/{staff}/advances', [SalaryPaymentController::class, 'storeAdvance'])->name('staff.advances.store');
    });
    Route::middleware(['permission:staff,edit'])->group(function () {
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::patch('/staff/{staff}', [StaffController::class, 'update']);
        Route::get('/staff/{staff}/salary-payments/{payment}/edit', [SalaryPaymentController::class, 'edit'])->name('staff.salary-payments.edit');
        Route::put('/staff/{staff}/salary-payments/{payment}', [SalaryPaymentController::class, 'update'])->name('staff.salary-payments.update');
    });
    Route::middleware(['permission:staff,delete'])->group(function () {
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])->name('staff.restore');
        Route::delete('/staff/{staff}/salary-payments/{payment}', [SalaryPaymentController::class, 'destroy'])->name('staff.salary-payments.destroy');
    });

   
    Route::middleware(['permission:expenses,view'])->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
        Route::get('/expenses/trashed', [ExpenseController::class, 'trashed'])->name('expenses.trashed');
        Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show')->where('expense', '[0-9]+');
    });
    Route::middleware(['permission:expenses,create'])->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses/check-duplicate', [ExpenseController::class, 'checkDuplicate'])->name('expenses.check-duplicate');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });
    Route::middleware(['permission:expenses,edit'])->group(function () {
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::patch('/expenses/{expense}', [ExpenseController::class, 'update']);
    });
    Route::middleware(['permission:expenses,delete'])->group(function () {
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::post('/expenses/{id}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore');
    });

   
    Route::middleware(['permission:income,view'])->group(function () {
        Route::get('/income', [IncomeController::class, 'index'])->name('income.index');
        Route::get('/income/export', [IncomeController::class, 'export'])->name('income.export');
        Route::get('/income/trashed', [IncomeController::class, 'trashed'])->name('income.trashed');
        Route::get('/income/{income}/receipt', [IncomeController::class, 'downloadReceipt'])->name('income.receipt')->where('income', '[0-9]+');
        Route::get('/income/{income}', [IncomeController::class, 'show'])->name('income.show')->where('income', '[0-9]+');
    });
    Route::middleware(['permission:income,create'])->group(function () {
        Route::get('/income/create', [IncomeController::class, 'create'])->name('income.create');
        Route::post('/income', [IncomeController::class, 'store'])->name('income.store');
    });
    Route::middleware(['permission:income,edit'])->group(function () {
        Route::get('/income/{income}/edit', [IncomeController::class, 'edit'])->name('income.edit');
        Route::put('/income/{income}', [IncomeController::class, 'update'])->name('income.update');
        Route::patch('/income/{income}', [IncomeController::class, 'update']);
    });
    Route::middleware(['permission:income,delete'])->group(function () {
        Route::delete('/income/{income}', [IncomeController::class, 'destroy'])->name('income.destroy');
        Route::post('/income/{id}/restore', [IncomeController::class, 'restore'])->name('income.restore');
    });

   
    Route::middleware(['permission:masters,view'])->group(function () {
        Route::get('/masters/work-types', [MasterController::class, 'workTypes'])->name('masters.work-types');
        Route::get('/masters/work-types/trashed', [MasterController::class, 'workTypesTrashed'])->name('masters.work-types.trashed');
        Route::get('/masters/work-leads', [MasterController::class, 'workLeads'])->name('masters.work-leads');
        Route::get('/masters/work-leads/trashed', [MasterController::class, 'workLeadsTrashed'])->name('masters.work-leads.trashed');
        Route::get('/masters/vendor-categories', [MasterController::class, 'vendorCategories'])->name('masters.vendor-categories');
        Route::get('/masters/vendor-categories/trashed', [MasterController::class, 'vendorCategoriesTrashed'])->name('masters.vendor-categories.trashed');
        Route::get('/masters/expense-categories', [MasterController::class, 'expenseCategories'])->name('masters.expense-categories');
        Route::get('/masters/expense-categories/trashed', [MasterController::class, 'expenseCategoriesTrashed'])->name('masters.expense-categories.trashed');
        Route::get('/masters/meeting-purposes', [MasterController::class, 'meetingPurposes'])->name('masters.meeting-purposes');
        Route::get('/masters/meeting-purposes/trashed', [MasterController::class, 'meetingPurposesTrashed'])->name('masters.meeting-purposes.trashed');
        Route::get('/masters/update-types', [MasterController::class, 'updateTypes'])->name('masters.update-types');
        Route::get('/masters/update-types/trashed', [MasterController::class, 'updateTypesTrashed'])->name('masters.update-types.trashed');
        Route::get('/masters/lead-statuses', [MasterController::class, 'leadStatuses'])->name('masters.lead-statuses');
        Route::get('/masters/lead-statuses/trashed', [MasterController::class, 'leadStatusesTrashed'])->name('masters.lead-statuses.trashed');
        Route::get('/masters/units', [MasterController::class, 'units'])->name('masters.units');
        Route::get('/masters/units/trashed', [MasterController::class, 'unitsTrashed'])->name('masters.units.trashed');
        Route::get('/masters/payment-modes', [MasterController::class, 'paymentModes'])->name('masters.payment-modes');
        Route::get('/masters/payment-modes/trashed', [MasterController::class, 'paymentModesTrashed'])->name('masters.payment-modes.trashed');
        Route::get('/masters/gst-rates', [MasterController::class, 'gstRates'])->name('masters.gst-rates');
        Route::get('/masters/gst-rates/trashed', [MasterController::class, 'gstRatesTrashed'])->name('masters.gst-rates.trashed');
        Route::get('/masters/trip-statuses', [MasterController::class, 'tripStatuses'])->name('masters.trip-statuses');
        Route::get('/masters/trip-statuses/trashed', [MasterController::class, 'tripStatusesTrashed'])->name('masters.trip-statuses.trashed');
        Route::get('/masters/expense-types', [MasterController::class, 'expenseTypes'])->name('masters.expense-types');
        Route::get('/masters/expense-types/trashed', [MasterController::class, 'expenseTypesTrashed'])->name('masters.expense-types.trashed');
        Route::get('/masters/task-statuses', [MasterController::class, 'taskStatuses'])->name('masters.task-statuses');
        Route::get('/masters/task-statuses/trashed', [MasterController::class, 'taskStatusesTrashed'])->name('masters.task-statuses.trashed');
        Route::get('/masters/staff-positions', [MasterController::class, 'staffPositions'])->name('masters.staff-positions');
        Route::get('/masters/staff-positions/trashed', [MasterController::class, 'staffPositionsTrashed'])->name('masters.staff-positions.trashed');
        Route::get('/masters/services', [MasterController::class, 'services'])->name('masters.services');
        Route::get('/masters/services/trashed', [MasterController::class, 'servicesTrashed'])->name('masters.services.trashed');
        Route::get('/masters/passenger-types', [MasterController::class, 'passengerTypes'])->name('masters.passenger-types');
        Route::get('/masters/passenger-types/trashed', [MasterController::class, 'passengerTypesTrashed'])->name('masters.passenger-types.trashed');
    });
    Route::middleware(['permission:masters,create'])->group(function () {
        Route::post('/masters/work-types', [MasterController::class, 'storeWorkType'])->name('masters.work-types.store');
        Route::post('/masters/work-leads', [MasterController::class, 'storeWorkLead'])->name('masters.work-leads.store');
        Route::post('/masters/vendor-categories', [MasterController::class, 'storeVendorCategory'])->name('masters.vendor-categories.store');
        Route::post('/masters/expense-categories', [MasterController::class, 'storeExpenseCategory'])->name('masters.expense-categories.store');
        Route::post('/masters/meeting-purposes', [MasterController::class, 'storeMeetingPurpose'])->name('masters.meeting-purposes.store');
        Route::post('/masters/update-types', [MasterController::class, 'storeUpdateType'])->name('masters.update-types.store');
        Route::post('/masters/lead-statuses', [MasterController::class, 'storeLeadStatus'])->name('masters.lead-statuses.store');
        Route::post('/masters/units', [MasterController::class, 'storeUnit'])->name('masters.units.store');
        Route::post('/masters/payment-modes', [MasterController::class, 'storePaymentMode'])->name('masters.payment-modes.store');
        Route::post('/masters/gst-rates', [MasterController::class, 'storeGstRate'])->name('masters.gst-rates.store');
        Route::post('/masters/trip-statuses', [MasterController::class, 'storeTripStatus'])->name('masters.trip-statuses.store');
        Route::post('/masters/expense-types', [MasterController::class, 'storeExpenseType'])->name('masters.expense-types.store');
        Route::post('/masters/task-statuses', [MasterController::class, 'storeTaskStatus'])->name('masters.task-statuses.store');
        Route::post('/masters/staff-positions', [MasterController::class, 'storeStaffPosition'])->name('masters.staff-positions.store');
        Route::post('/masters/services', [MasterController::class, 'storeService'])->name('masters.services.store');
        Route::post('/masters/passenger-types', [MasterController::class, 'storePassengerType'])->name('masters.passenger-types.store');
    });
    Route::middleware(['permission:masters,edit'])->group(function () {
        Route::put('/masters/work-types/{workType}', [MasterController::class, 'updateWorkType'])->name('masters.work-types.update');
        Route::post('/masters/work-types/{workType}/toggle', [MasterController::class, 'toggleWorkType'])->name('masters.work-types.toggle');
        Route::put('/masters/work-leads/{workLead}', [MasterController::class, 'updateWorkLead'])->name('masters.work-leads.update');
        Route::post('/masters/work-leads/{workLead}/toggle', [MasterController::class, 'toggleWorkLead'])->name('masters.work-leads.toggle');
        Route::put('/masters/vendor-categories/{vendorCategory}', [MasterController::class, 'updateVendorCategory'])->name('masters.vendor-categories.update');
        Route::post('/masters/vendor-categories/{vendorCategory}/toggle', [MasterController::class, 'toggleVendorCategory'])->name('masters.vendor-categories.toggle');
        Route::put('/masters/expense-categories/{expenseCategory}', [MasterController::class, 'updateExpenseCategory'])->name('masters.expense-categories.update');
        Route::post('/masters/expense-categories/{expenseCategory}/toggle', [MasterController::class, 'toggleExpenseCategory'])->name('masters.expense-categories.toggle');
        Route::put('/masters/meeting-purposes/{meetingPurpose}', [MasterController::class, 'updateMeetingPurpose'])->name('masters.meeting-purposes.update');
        Route::post('/masters/meeting-purposes/{meetingPurpose}/toggle', [MasterController::class, 'toggleMeetingPurpose'])->name('masters.meeting-purposes.toggle');
        Route::put('/masters/update-types/{updateType}', [MasterController::class, 'updateUpdateType'])->name('masters.update-types.update');
        Route::post('/masters/update-types/{updateType}/toggle', [MasterController::class, 'toggleUpdateType'])->name('masters.update-types.toggle');
        Route::put('/masters/lead-statuses/{leadStatus}', [MasterController::class, 'updateLeadStatus'])->name('masters.lead-statuses.update');
        Route::post('/masters/lead-statuses/{leadStatus}/toggle', [MasterController::class, 'toggleLeadStatus'])->name('masters.lead-statuses.toggle');
        Route::put('/masters/units/{unit}', [MasterController::class, 'updateUnit'])->name('masters.units.update');
        Route::post('/masters/units/{unit}/toggle', [MasterController::class, 'toggleUnit'])->name('masters.units.toggle');
        Route::put('/masters/payment-modes/{paymentMode}', [MasterController::class, 'updatePaymentMode'])->name('masters.payment-modes.update');
        Route::post('/masters/payment-modes/{paymentMode}/toggle', [MasterController::class, 'togglePaymentMode'])->name('masters.payment-modes.toggle');
        Route::put('/masters/gst-rates/{gstRate}', [MasterController::class, 'updateGstRate'])->name('masters.gst-rates.update');
        Route::post('/masters/gst-rates/{gstRate}/toggle', [MasterController::class, 'toggleGstRate'])->name('masters.gst-rates.toggle');
        Route::put('/masters/trip-statuses/{tripStatus}', [MasterController::class, 'updateTripStatus'])->name('masters.trip-statuses.update');
        Route::post('/masters/trip-statuses/{tripStatus}/toggle', [MasterController::class, 'toggleTripStatus'])->name('masters.trip-statuses.toggle');
        Route::put('/masters/expense-types/{expenseType}', [MasterController::class, 'updateExpenseType'])->name('masters.expense-types.update');
        Route::post('/masters/expense-types/{expenseType}/toggle', [MasterController::class, 'toggleExpenseType'])->name('masters.expense-types.toggle');
        Route::put('/masters/task-statuses/{taskStatus}', [MasterController::class, 'updateTaskStatus'])->name('masters.task-statuses.update');
        Route::post('/masters/task-statuses/{taskStatus}/toggle', [MasterController::class, 'toggleTaskStatus'])->name('masters.task-statuses.toggle');
        Route::put('/masters/staff-positions/{staffPosition}', [MasterController::class, 'updateStaffPosition'])->name('masters.staff-positions.update');
        Route::post('/masters/staff-positions/{staffPosition}/toggle', [MasterController::class, 'toggleStaffPosition'])->name('masters.staff-positions.toggle');
        Route::put('/masters/services/{service}', [MasterController::class, 'updateService'])->name('masters.services.update');
        Route::post('/masters/services/{service}/toggle', [MasterController::class, 'toggleService'])->name('masters.services.toggle');
        Route::put('/masters/passenger-types/{passengerType}', [MasterController::class, 'updatePassengerType'])->name('masters.passenger-types.update');
        Route::post('/masters/passenger-types/{passengerType}/toggle', [MasterController::class, 'togglePassengerType'])->name('masters.passenger-types.toggle');
    });


    Route::middleware(['permission:banks,view'])->group(function () {
        Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
        Route::get('/banks/trashed', [BankController::class, 'trashed'])->name('banks.trashed');
        Route::get('/banks/{bank}', [BankController::class, 'show'])->name('banks.show');
    });
    Route::middleware(['permission:banks,create'])->group(function () {
        Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
        Route::post('/banks/{bank}/transactions', [BankController::class, 'storeTransaction'])->name('banks.transactions.store');
    });
    Route::middleware(['permission:banks,edit'])->group(function () {
        Route::put('/banks/{bank}', [BankController::class, 'update'])->name('banks.update');
    });
    Route::middleware(['permission:banks,delete'])->group(function () {
        Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');
        Route::post('/banks/{id}/restore', [BankController::class, 'restore'])->name('banks.restore');
    });


    Route::get('/settings/account', [SettingsController::class, 'account'])->name('settings.account');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/photo', [SettingsController::class, 'uploadPhoto'])->name('settings.photo.upload');
    Route::delete('/settings/photo', [SettingsController::class, 'removePhoto'])->name('settings.photo.remove');
    Route::put('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password.update');

   
    Route::middleware(['permission:roles,view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/trashed', [RoleController::class, 'trashed'])->name('roles.trashed');
    });
    Route::middleware(['permission:roles,create'])->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware(['permission:roles,edit'])->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::patch('/roles/{role}', [RoleController::class, 'update']);
    });
    Route::middleware(['permission:roles,delete'])->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
    });

   
    Route::middleware(['permission:users,view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
    });
    Route::middleware(['permission:users,create'])->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware(['permission:users,edit'])->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}', [UserController::class, 'update']);
    });
    Route::middleware(['permission:users,delete'])->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });
});
