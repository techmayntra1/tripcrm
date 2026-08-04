<?php

namespace App\Console\Commands;

use App\Models\Bank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verifies the unified-ledger refactor: the new balance (derived purely from
 * Income and Expense) should equal the legacy balance (opening + bank_transactions
 * credits/debits). Run AFTER the data migration but BEFORE bank_transactions is
 * dropped to get a real comparison.
 */
class ReconcileBankBalances extends Command
{
    protected $signature = 'bank:reconcile';

    protected $description = 'Compare legacy bank_transactions balances with the new Income/Expense-derived balances';

    public function handle(): int
    {
        $hasLegacy = Schema::hasTable('bank_transactions');
        $rows = [];
        $mismatches = 0;

        foreach (Bank::withTrashed()->get() as $bank) {
            $opening = (float) ($bank->opening_balance ?? 0);
            $newBalance = $opening + $bank->total_credit - $bank->total_debit;

            if ($hasLegacy) {
                $credit = (float) DB::table('bank_transactions')
                    ->where('bank_id', $bank->id)->where('type', 'credit')->whereNull('deleted_at')->sum('amount');
                $debit = (float) DB::table('bank_transactions')
                    ->where('bank_id', $bank->id)->where('type', 'debit')->whereNull('deleted_at')->sum('amount');
                $oldBalance = $opening + $credit - $debit;
                $diff = round($newBalance - $oldBalance, 2);
                if (abs($diff) >= 0.01) {
                    $mismatches++;
                }
                $rows[] = [$bank->bank_name, number_format($oldBalance, 2), number_format($newBalance, 2), number_format($diff, 2)];
            } else {
                $rows[] = [$bank->bank_name, 'n/a (dropped)', number_format($newBalance, 2), '-'];
            }
        }

        if ($hasLegacy) {
            $this->table(['Bank', 'Legacy Balance', 'New Balance', 'Diff'], $rows);
            if ($mismatches === 0) {
                $this->info('All banks reconcile. Legacy and new balances match.');
            } else {
                $this->error("{$mismatches} bank(s) do not reconcile. Investigate before dropping bank_transactions.");
                return self::FAILURE;
            }
        } else {
            $this->table(['Bank', 'Legacy Balance', 'New Balance', 'Diff'], $rows);
            $this->warn('Legacy bank_transactions table no longer exists; showing new balances only.');
        }

        return self::SUCCESS;
    }
}
