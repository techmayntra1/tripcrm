<?php

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unified-ledger data migration.
 *
 * Converts every legacy money-movement into a single Income (money in) or
 * Expense (money out) row so the bank balance can be derived purely from those
 * two tables:
 *   - trip_services.advance          -> service Expense (paid)
 *   - trip_service_payments rows     -> service Expense (paid)
 *   - bank_transactions (reference_type='manual') credit -> Income (other)
 *   - bank_transactions (reference_type='manual') debit  -> Expense (general, paid)
 *
 * BankTransaction rows for income/expense/trip_service are NOT migrated:
 * they were mirrors of records that already exist, and the whole
 * bank_transactions table is dropped in the following migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Service advances -> service Expense, then zero the column so
            //    TripService::paid_amount has a single source of truth.
            if (Schema::hasTable('trip_services')) {
                $advances = DB::table('trip_services')
                    ->whereNull('deleted_at')
                    ->where('advance', '>', 0)
                    ->get();

                foreach ($advances as $ps) {
                    Expense::create([
                        'expense_type' => 'service',
                        'expense_date' => $ps->created_at ? substr($ps->created_at, 0, 10) : now()->toDateString(),
                        'trip_id' => $ps->trip_id,
                        'trip_service_id' => $ps->id,
                        'bank_id' => $ps->advance_bank_id,
                        'sub_total' => $ps->advance,
                        'gst_percentage' => 0,
                        'gst_amount' => 0,
                        'grand_total' => $ps->advance,
                        'paid_amount' => $ps->advance,
                        'payment_status' => 'paid',
                        'description' => 'Service Advance',
                    ]);
                }

                DB::table('trip_services')
                    ->where('advance', '>', 0)
                    ->update(['advance' => 0, 'advance_bank_id' => null]);
            }

            // 2. Service payments -> service Expense.
            if (Schema::hasTable('trip_service_payments')) {
                $payments = DB::table('trip_service_payments as p')
                    ->join('trip_services as s', 's.id', '=', 'p.trip_service_id')
                    ->select('p.*', 's.trip_id')
                    ->get();

                foreach ($payments as $pay) {
                    Expense::create([
                        'expense_type' => 'service',
                        'expense_date' => $pay->payment_date ?: now()->toDateString(),
                        'trip_id' => $pay->trip_id,
                        'trip_service_id' => $pay->trip_service_id,
                        'bank_id' => $pay->bank_id,
                        'sub_total' => $pay->amount,
                        'gst_percentage' => 0,
                        'gst_amount' => 0,
                        'grand_total' => $pay->amount,
                        'paid_amount' => $pay->amount,
                        'payment_status' => 'paid',
                        'description' => 'Service Payment' . ($pay->note ? ' - ' . $pay->note : ''),
                    ]);
                }
            }

            // 3. Manual bank transactions -> Income / Expense.
            if (Schema::hasTable('bank_transactions')) {
                $manual = DB::table('bank_transactions')
                    ->where('reference_type', 'manual')
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($manual as $txn) {
                    if ($txn->type === 'credit') {
                        Income::create([
                            'income_type' => 'other',
                            'income_date' => $txn->transaction_date ?: now()->toDateString(),
                            'bank_id' => $txn->bank_id,
                            'amount' => $txn->amount,
                            'description' => $txn->description ?: 'Manual adjustment',
                        ]);
                    } else {
                        Expense::create([
                            'expense_type' => 'general',
                            'expense_date' => $txn->transaction_date ?: now()->toDateString(),
                            'bank_id' => $txn->bank_id,
                            'sub_total' => $txn->amount,
                            'gst_percentage' => 0,
                            'gst_amount' => 0,
                            'grand_total' => $txn->amount,
                            'paid_amount' => $txn->amount,
                            'payment_status' => 'paid',
                            'description' => $txn->description ?: 'Manual adjustment',
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Financial data migration: not automatically reversible. Restore from a
        // database backup taken before running this migration if you must roll back.
    }
};
