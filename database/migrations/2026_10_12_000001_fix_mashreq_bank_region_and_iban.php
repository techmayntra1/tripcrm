<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // The Mashreq account was saved under the India region, so its IBAN went into the
    // 11-char IFSC column (truncated) and invoices printed IFSC / INR.
    public function up(): void
    {
        DB::table('banks')
            ->where('account_number', '019101893013')
            ->update([
                'bank_name' => 'Mashreq Bank',
                'account_holder' => 'TRIPMANTRA TRAVEL TOURISM FZ LLC',
                'country' => 'uae',
                'iban' => 'AE620330000019101893013',
                'ifsc_code' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
