<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAME = 'UAE Tourist Visa Terms';

    public function up(): void
    {
        // Only seed when no default Terms & Conditions template exists yet.
        $hasDefault = DB::table('term_templates')
            ->where('type', 'terms')
            ->where('is_default', true)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasDefault) {
            return;
        }

        DB::table('term_templates')->insert([
            'type' => 'terms',
            'name' => self::NAME,
            'content' => implode("\n", [
                'Note: Cancellation charges of 10% of the refund amount are applicable on sales.',
                '1. Visa approval is solely at the discretion of the immigration authorities.',
                '2. TRIPMANTRA TRAVEL & TOURISM LLC applies for tourist visas under the guidance of DTCM rules. In case of rejection for any reason, we will not be responsible for the application charges, which are non-refundable.',
                '3. In case of an air ticket change or refund, charges apply as per the airline plus the company service charge. Refunds will be made after a minimum period of one month.',
                '4. If the applicant does not leave the UAE or change visa within the required time as per the visa, all fines will be paid by the Applicant / Guarantor.',
                '5. In case of any overstay or absconding of the applicant, all fines will be paid by the Applicant / Guarantor of the Applicant.',
                '6. Check all the details on the visa; if there is any mistake, let us know within two days. We are not responsible if the passenger is offloaded or not allowed to travel because of this.',
                '7. If an INSIDE COUNTRY visa change application is rejected for any reason, only the change-status amount is refundable.',
                '8. If the applicant books an air ticket before the tourist visa is approved, TRIPMANTRA TRAVEL & TOURISM LLC will not be responsible for any charges.',
                '9. All payments must be made to the company account only. TRIPMANTRA TRAVEL & TOURISM LLC is not liable for any amount paid to any other personal account.',
            ]),
            'is_default' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('term_templates')->where('type', 'terms')->where('name', self::NAME)->delete();
    }
};
