<?php

namespace Tests\Feature;

use App\Mail\EmailChangedMail;
use App\Mail\OtpMail;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Email change: own account = current email code → new email code → changed.
 * The migrations are MySQL-only, so run against the local MySQL (each test is
 * rolled back, mail is faked):
 *   DB_CONNECTION=mysql DB_DATABASE=<your db> php artisan test --filter=EmailChangeTest
 */
class EmailChangeTest extends TestCase
{
    use DatabaseTransactions;

    private function account(string $email, string $type = 'Patient', string $role = 'patient'): UserAccount
    {
        return UserAccount::create([
            'Email' => $email,
            'Password' => bcrypt('secret-pass-123'),
            'AccountRole' => $role,
            'AccountType' => $type,
            'DateCreated' => now(),
            'EmailVerifiedAt' => now(),
        ]);
    }

    private function as(UserAccount $u, string $role = 'patient', bool $super = false): static
    {
        return $this->withSession([
            'user_id' => $u->UserID,
            'user_role' => $role,
            'user_email' => $u->Email,
            'account_type' => $u->AccountType,
            'is_super_admin' => $super,
        ]);
    }

    /** The code in the last OtpMail sent to $to. */
    private function codeSentTo(string $to): string
    {
        $mail = collect(Mail::sent(OtpMail::class))->last(fn ($m) => $m->hasTo($to));
        $this->assertNotNull($mail, "No code was sent to {$to}");

        return $mail->code;
    }

    public function test_own_email_needs_current_then_new_email_codes(): void
    {
        Mail::fake();
        $u = $this->account('old@example.com');
        $this->as($u);

        // Skipping step 1 is refused, and no code goes to the new address.
        $this->postJson(route('emailChange.send'), ['new_email' => 'new@example.com'])
            ->assertStatus(403)->assertJson(['restart' => 'identity']);
        Mail::assertNotSent(OtpMail::class, fn ($m) => $m->hasTo('new@example.com'));

        // Step 1: code to the CURRENT email.
        $this->postJson(route('emailChange.sendCurrent'))->assertOk();
        $this->postJson(route('emailChange.verifyCurrent'), ['code' => '000000'])->assertStatus(422);
        $this->postJson(route('emailChange.verifyCurrent'), ['code' => $this->codeSentTo('old@example.com')])->assertOk();

        // Step 2: code to the NEW email. Still unchanged at this point.
        $this->postJson(route('emailChange.send'), ['new_email' => 'new@example.com'])->assertOk();
        $this->assertSame('old@example.com', $u->fresh()->Email);

        // The current email's code can't stand in for the new email's code.
        $oldCode = $this->codeSentTo('old@example.com');
        $newCode = $this->codeSentTo('new@example.com');
        if ($oldCode !== $newCode) {
            $this->postJson(route('emailChange.verify'), ['code' => $oldCode])->assertStatus(422);
        }

        // Step 3: verified → changed, and the old inbox is told.
        $this->postJson(route('emailChange.verify'), ['code' => $newCode])->assertOk();
        $this->assertSame('new@example.com', $u->fresh()->Email);
        Mail::assertSent(EmailChangedMail::class, fn ($m) => $m->hasTo('old@example.com') && $m->newEmail === 'new@example.com');

        // The unlock is used up: another change starts from step 1 again.
        $this->postJson(route('emailChange.send'), ['new_email' => 'third@example.com'])
            ->assertStatus(403)->assertJson(['restart' => 'identity']);
    }

    public function test_abandoning_after_step_one_or_two_changes_nothing(): void
    {
        Mail::fake();
        $u = $this->account('old@example.com');
        $this->as($u);

        $this->postJson(route('emailChange.sendCurrent'))->assertOk();
        $this->postJson(route('emailChange.verifyCurrent'), ['code' => $this->codeSentTo('old@example.com')])->assertOk();
        $this->postJson(route('emailChange.send'), ['new_email' => 'new@example.com'])->assertOk();

        // Page refreshed / modal reopened: it starts over at step 1, which
        // drops the half-finished new-email code.
        $this->travel(2)->minutes();
        $this->postJson(route('emailChange.sendCurrent'))->assertOk();
        $this->postJson(route('emailChange.verify'), ['code' => $this->codeSentTo('new@example.com')])
            ->assertStatus(403);
        $this->assertSame('old@example.com', $u->fresh()->Email);
    }

    public function test_step_one_unlock_expires(): void
    {
        Mail::fake();
        $u = $this->account('old@example.com');
        $this->as($u);

        $this->postJson(route('emailChange.sendCurrent'))->assertOk();
        $this->postJson(route('emailChange.verifyCurrent'), ['code' => $this->codeSentTo('old@example.com')])->assertOk();

        $this->travel(16)->minutes();
        $this->postJson(route('emailChange.send'), ['new_email' => 'new@example.com'])
            ->assertStatus(403)->assertJson(['restart' => 'identity']);
        $this->assertSame('old@example.com', $u->fresh()->Email);
    }

    public function test_staff_own_email_also_needs_current_email_code(): void
    {
        Mail::fake();
        $staff = $this->account('dentist@example.com', 'Staff', 'admin');
        $this->as($staff, 'admin');

        $this->postJson(route('emailChange.send'), ['new_email' => 'dr.new@example.com'])
            ->assertStatus(403)->assertJson(['restart' => 'identity']);
    }

    public function test_admin_changing_a_patient_needs_only_the_new_email_and_tells_the_old_one(): void
    {
        Mail::fake();
        $admin = $this->account('admin@example.com', 'Staff', 'admin');
        $patient = $this->account('patient-old@example.com');
        $this->as($admin, 'admin');

        // The current-email step is for your own account only.
        $this->postJson(route('emailChange.sendCurrent'), ['user_id' => $patient->UserID])->assertStatus(422);

        $this->postJson(route('emailChange.send'), ['user_id' => $patient->UserID, 'new_email' => 'patient-new@example.com'])->assertOk();
        $this->assertSame('patient-old@example.com', $patient->fresh()->Email);

        $this->postJson(route('emailChange.verify'), ['user_id' => $patient->UserID, 'code' => $this->codeSentTo('patient-new@example.com')])->assertOk();
        $this->assertSame('patient-new@example.com', $patient->fresh()->Email);
        Mail::assertSent(EmailChangedMail::class, fn ($m) => $m->hasTo('patient-old@example.com') && !$m->changedBySelf);
    }
}
