<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Patient accounts made through self sign-up always proved their email (the
 * account isn't written until the 6-digit code matches), but EmailVerifiedAt
 * was never stamped for them. Admins can now create patient accounts too,
 * and those start unverified — so EnsurePatientIsVerified needs the column
 * to mean something for patients. Stamp every existing patient as verified
 * (as of when they signed up) so nobody already using the portal gets locked
 * out.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('tbl_useraccount')
            ->where('AccountRole', 'patient')
            ->whereNull('EmailVerifiedAt')
            ->update(['EmailVerifiedAt' => DB::raw('COALESCE(DateCreated, NOW())')]);
    }

    public function down(): void
    {
        // Nothing to undo — the stamps are true either way.
    }
};
