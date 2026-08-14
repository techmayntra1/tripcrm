<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Trip;
use App\Models\TripService;
use App\Models\TripServiceAddon;
use Illuminate\Http\Request;

class TripServiceController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'amount' => 'required|numeric|min:1',
            'advance' => 'nullable|numeric|min:0',
            'bank_id' => 'nullable|exists:banks,id',
            'due_date' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ], [
            'service_ids.required' => 'Please select at least one service.',
            'amount.required' => 'Please enter the amount.',
            'amount.min' => 'Amount must be at least 1.',
        ]);

        $advance = (float)($validated['advance'] ?? 0);
        if ($advance > 0 && empty($validated['bank_id'])) {
            return back()->withInput()->withErrors(['bank_id' => 'Bank is required when advance is greater than 0.']);
        }

        $serviceIds = array_values(array_unique(array_map('intval', $validated['service_ids'])));
        $bankId = $advance > 0 ? $validated['bank_id'] : null;

        $tripService = TripService::create([
            'trip_id' => $trip->id,
            'service_ids' => $serviceIds,
            'amount' => $validated['amount'],
            'advance' => $advance,
            'advance_bank_id' => $bankId,
            'due_date' => $validated['due_date'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        if ($advance > 0 && $bankId) {
            self::createServiceExpense($tripService, $bankId, $advance, now()->toDateString(), 'Service Advance');
        }

        return redirect()->route('admin.trips.show', ['trip' => $trip, 'tab' => 'services'])
            ->with('success', 'Service added successfully.');
    }

    public function update(Request $request, Trip $trip, TripService $service)
    {
        $validated = $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ]);

        $validated['service_ids'] = array_map('intval', $validated['service_ids']);

        $service->update([
            'service_ids' => $validated['service_ids'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('admin.trips.show', ['trip' => $trip, 'tab' => 'services'])
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Trip $trip, TripService $service)
    {
        // Every service payment is now a plain Expense row; removing the service
        // removes its expenses (money-out) alongside its add-ons.
        $service->serviceExpenses()->delete();
        $service->addons()->delete();
        $service->delete();

        return redirect()->route('admin.trips.show', ['trip' => $trip, 'tab' => 'services'])
            ->with('success', 'Service removed successfully.');
    }

    public function addPayment(Request $request, Trip $trip, TripService $service)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $service->balance,
            'bank_id' => 'required|exists:banks,id',
            'payment_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        $paymentDate = $validated['payment_date'] ?? now()->toDateString();

        $description = 'Service Payment' . (!empty($validated['note']) ? ' - ' . $validated['note'] : '');
        self::createServiceExpense($service, $validated['bank_id'], $validated['amount'], $paymentDate, $description);

        return redirect()->route('admin.trips.show', ['trip' => $trip, 'tab' => 'services'])
            ->with('success', 'Payment of ' . formatMoney($validated['amount']) . ' recorded successfully.');
    }

    /**
     * Record a service payment as a single fully-paid Expense (money-out).
     * The expense carries both trip_id and trip_service_id so it counts
     * once toward trip spend and toward the service's paid amount.
     */
    protected static function createServiceExpense(TripService $service, $bankId, float $amount, string $date, string $description): Expense
    {
        return Expense::create([
            'expense_type' => 'service',
            'expense_date' => $date,
            'trip_id' => $service->trip_id,
            'trip_service_id' => $service->id,
            'bank_id' => $bankId,
            'sub_total' => $amount,
            'gst_percentage' => 0,
            'gst_amount' => 0,
            'grand_total' => $amount,
            'paid_amount' => $amount,
            'payment_status' => 'paid',
            'description' => $description,
        ]);
    }

    public function addAddon(Request $request, Trip $trip, TripService $service)
    {
        $validated = $request->validate([
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $serviceIds = isset($validated['service_ids']) ? array_map('intval', $validated['service_ids']) : null;

        TripServiceAddon::create([
            'trip_service_id' => $service->id,
            'service_ids' => $serviceIds,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
        ]);

        return redirect()->route('admin.trips.show', ['trip' => $trip, 'tab' => 'services'])
            ->with('success', 'Add-on added successfully.');
    }

    public function deleteAddon(Trip $trip, TripService $service, TripServiceAddon $addon)
    {
        $addon->delete();

        return redirect()->route('admin.trips.show', ['trip' => $trip, 'tab' => 'services'])
            ->with('success', 'Add-on removed successfully.');
    }
}
