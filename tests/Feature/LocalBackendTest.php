<?php

namespace Tests\Feature;

use App\Jobs\SyncLocalInvoiceToFasahJob;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-12 12:00:00 Asia/Riyadh');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function owner(): User
    {
        $this->seed();
        $staff = Staff::create(['code' => 'OWNER', 'first_name' => 'Test', 'last_name' => 'Owner', 'email' => 'owner@example.test', 'role_id' => Role::where('slug', 'owner')->firstOrFail()->id, 'login_allowed' => true]);

        return User::create(['staff_id' => $staff->id, 'name' => 'Test Owner', 'email' => 'owner@example.test', 'password' => 'test-password-123', 'active' => true]);
    }

    public function test_login_and_first_password_change(): void
    {
        $owner = $this->owner();
        $owner->update(['must_change_password' => true]);
        $this->post(route('login.store'), ['email' => $owner->email, 'password' => 'test-password-123'])->assertRedirect();
        $this->get(route('dashboard'))->assertRedirect(route('password.edit'));
        $this->post(route('password.update'), ['current_password' => 'test-password-123', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])->assertRedirect(route('dashboard'));
        $this->assertTrue(Hash::check('new-password-123', $owner->fresh()->password));
        $this->assertFalse($owner->fresh()->must_change_password);
    }

    public function test_empty_pages_and_client_staff_role_activity_crud(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee('0', false);
        $this->get(route('invoices.index'))->assertOk()->assertSee('لا توجد فواتير');
        $this->get(route('clients.index'))->assertOk()->assertSee('لا يوجد عملاء');
        $this->get(route('clients.create'))->assertOk();
        $this->get(route('staff.index'))->assertOk();
        $this->get(route('staff.create'))->assertOk();
        $this->get(route('settings.roles'))->assertOk();
        $this->get(route('settings.activity'))->assertOk();
        $this->post(route('clients.store'), ['code' => 'C-1', 'type' => 'business', 'business_name' => 'عميل الاختبار', 'contacts' => [['name' => 'جهة اتصال', 'email' => 'contact@example.test']]])->assertRedirect();
        $client = Client::firstOrFail();
        $this->assertCount(1, $client->contacts);
        $this->get(route('clients.show', $client))->assertOk()->assertSee('عميل الاختبار');
        $this->get(route('clients.edit', $client))->assertOk();
        $this->put(route('clients.update', $client), ['code' => 'C-1', 'type' => 'business', 'business_name' => 'عميل محدث'])->assertRedirect();
        $this->assertSame('عميل محدث', $client->fresh()->business_name);
        $this->post(route('roles.store'), ['name' => 'مراجع', 'permissions' => ['clients.view']])->assertRedirect();
        $role = Role::where('name', 'مراجع')->firstOrFail();
        $this->post(route('staff.store'), ['first_name' => 'Sara', 'last_name' => 'Test', 'code' => 'S-1', 'type' => 'مستخدم', 'role_id' => $role->id, 'email' => 'sara@example.test', 'login_allowed' => 1, 'password' => 'staff-password-123', 'password_confirmation' => 'staff-password-123'])->assertRedirect();
        $staff = Staff::where('code', 'S-1')->firstOrFail();
        $this->assertTrue($staff->user->must_change_password);
        $this->get(route('staff.edit', $staff))->assertOk();
        $this->assertTrue($staff->user->fresh()->permits('clients.view'));
        $this->actingAs($staff->user)->get(route('invoices.index'))->assertRedirect(route('password.edit'));
        $staff->user->update(['must_change_password' => false]);
        $this->get(route('invoices.index'))->assertForbidden();
        $this->get(route('clients.index'))->assertOk();
    }

    public function test_invoice_issue_calculation_payment_and_lock(): void
    {
        Http::fake();
        $owner = $this->owner();
        $this->actingAs($owner);
        $client = Client::create(['code' => 'C-1', 'type' => 'business', 'business_name' => 'Client']);
        $this->get(route('invoices.create'))->assertOk();
        $this->post(route('invoices.store'), ['action' => 'draft'])->assertRedirect();
        $draft = Invoice::firstOrFail();
        $this->assertNull($draft->number);
        $this->put(route('invoices.update', $draft), [
            'action' => 'issue', 'client_id' => $client->id, 'invoice_date' => '2026-10-12', 'issue_date' => '2026-10-12',
            'fasah_type' => 'importer', 'fasah_meta' => ['importer_number' => 'IMP-1', 'port' => '30'],
            'discount_value' => 0, 'discount_kind' => 'amount', 'items' => [['name' => 'Freight', 'unit_price' => 100, 'quantity' => 2, 'discount' => 10, 'tax_one' => 15, 'tax_two' => 0]],
        ])->assertRedirect();
        $invoice = $draft->fresh();
        $this->assertSame(1, $invoice->number);
        $this->assertEquals(218.50, $invoice->total);
        $this->post(route('invoices.store'), [
            'action' => 'issue', 'client_id' => $client->id, 'invoice_date' => '2026-10-12', 'issue_date' => '2026-10-12',
            'fasah_type' => 'importer', 'fasah_meta' => ['importer_number' => 'IMP-1', 'port' => '30'],
            'items' => [['name' => 'Second', 'unit_price' => 1, 'quantity' => 1]],
        ])->assertRedirect();
        $this->assertSame(2, Invoice::where('id', '!=', $invoice->id)->firstOrFail()->number);
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('218.50');
        $this->get(route('invoices.edit', $invoice))->assertOk();
        $this->post(route('invoices.payments.store', $invoice), ['amount' => 100, 'paid_on' => '2026-10-12', 'method' => 'نقدًا'])->assertRedirect();
        $this->assertEquals(118.50, $invoice->fresh()->balance);
        $this->post(route('invoices.payments.store', $invoice), ['amount' => 200, 'paid_on' => '2026-10-12', 'method' => 'نقدًا'])->assertSessionHasErrors('amount');
        $this->post(route('invoices.payments.store', $invoice), ['amount' => 118.50, 'paid_on' => '2026-10-12', 'method' => 'نقدًا'])->assertRedirect();
        $this->assertSame('مدفوعة', $invoice->fresh()->display_status);
        $this->put(route('invoices.update', $invoice), ['action' => 'metadata', 'items' => [['name' => 'Changed', 'unit_price' => 1]], 'notes' => 'Updated'])->assertRedirect();
        $this->assertEquals(218.50, $invoice->fresh()->total);
        $this->assertSame('Freight', $invoice->fresh()->items->first()->name);
        $this->delete(route('invoices.destroy', $invoice))->assertSessionHasErrors('invoice');
        Http::assertNothingSent();
    }

    public function test_manual_invoice_number_is_kept_in_draft_unique_on_issue_and_advances_automatic_sequence(): void
    {
        Queue::fake();
        $this->actingAs($this->owner());
        $client = Client::create(['code' => 'NUMBER-CLIENT', 'type' => 'business', 'business_name' => 'Numbered client']);
        $data = [
            'action' => 'issue', 'client_id' => $client->id, 'invoice_date' => '2026-10-12',
            'issue_date' => '2026-10-12', 'fasah_type' => 'importer',
            'fasah_meta' => ['importer_number' => 'IMP-1', 'port' => '30'],
            'items' => [['name' => 'Freight', 'unit_price' => 100, 'quantity' => 1]],
        ];

        $this->post(route('invoices.store'), ['action' => 'draft', 'invoice_number' => 700])->assertRedirect();
        $draft = Invoice::firstOrFail();
        $this->assertNull($draft->number);
        $this->assertEquals(700, $draft->requested_number);
        $this->get(route('invoices.edit', $draft))->assertOk()->assertSee('value="700"', false);

        $this->put(route('invoices.update', $draft), $data)->assertRedirect();
        $this->assertSame(700, $draft->fresh()->number);
        $this->assertSame('LOCAL-700', $draft->fresh()->fasahSync->internal_invoice_number);

        $this->post(route('invoices.store'), $data)->assertRedirect();
        $this->assertSame(701, Invoice::where('id', '!=', $draft->id)->firstOrFail()->number);

        $this->post(route('invoices.store'), $data + ['invoice_number' => 700])->assertSessionHasErrors('invoice_number');
        $this->post(route('invoices.store'), $data + ['invoice_number' => '12.5'])->assertSessionHasErrors('invoice_number');
        $this->assertSame(2, Invoice::count());
        Queue::assertPushed(SyncLocalInvoiceToFasahJob::class, 2);
    }

    public function test_last_owner_cannot_be_deactivated_and_owner_command_is_interactive(): void
    {
        $this->seed();
        $this->artisan('app:create-owner')
            ->expectsQuestion('First name', 'Original')
            ->expectsQuestion('Last name', 'Owner')
            ->expectsQuestion('Email', 'original@example.test')
            ->expectsQuestion('Password (at least 12 characters)', 'strong-password-123')
            ->assertSuccessful();
        $owner = User::where('email', 'original@example.test')->firstOrFail();
        $this->actingAs($owner)->delete(route('staff.destroy', $owner->staff))->assertSessionHasErrors('role_id');
        $this->assertTrue($owner->fresh()->active);
        $this->assertSame('نشط', $owner->staff->fresh()->status);
    }

    public function test_private_attachment_and_dashboard_use_database_records(): void
    {
        Storage::fake('local');
        $owner = $this->owner();
        $this->actingAs($owner)->post(route('clients.store'), [
            'code' => 'C-2', 'type' => 'business', 'business_name' => 'شركة اختبار',
            'attachments' => [UploadedFile::fake()->create('agreement.pdf', 20, 'application/pdf')],
        ])->assertRedirect();
        $client = Client::firstOrFail();
        $file = $client->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($file->path);
        $this->get(route('attachments.download', $file))->assertOk();
        $this->get(route('dashboard'))->assertOk()->assertSee('1', false);
        $this->get(route('clients.index'))->assertOk()->assertSee('شركة اختبار');
        $this->get(route('settings.activity'))->assertOk()->assertSee('client.saved', false);
    }

    public function test_staff_permissions_cannot_assign_owner_or_read_hidden_dashboard_data(): void
    {
        $owner = $this->owner();
        $limitedRole = Role::create(['slug' => 'limited', 'name' => 'Limited']);
        $limitedRole->permissions()->create(['permission' => 'staff.create']);
        $limitedRole->permissions()->create(['permission' => 'staff.edit']);
        $limitedRole->permissions()->create(['permission' => 'clients.view']);
        $staff = Staff::create(['code' => 'LIMITED', 'first_name' => 'Limited', 'last_name' => 'User', 'email' => 'limited@example.test', 'role_id' => $limitedRole->id, 'login_allowed' => true]);
        $user = User::create(['staff_id' => $staff->id, 'name' => 'Limited User', 'email' => $staff->email, 'password' => 'limited-password-123', 'active' => true]);
        $client = Client::create(['code' => 'SECRET', 'type' => 'business', 'business_name' => 'Visible Client']);
        Invoice::create(['client_id' => $client->id, 'status' => 'issued', 'total' => 123.45, 'amount_paid' => 0]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('إجمالي الفواتير الصادرة')->assertDontSee('أحدث الأنشطة');
        $this->get(route('clients.show', $client))->assertOk()->assertDontSee('123.45')->assertDontSee('الرصيد المستحق');
        $this->get(route('staff.edit', $owner->staff))->assertForbidden();
        $this->post(route('staff.store'), ['first_name' => 'Fake', 'last_name' => 'Owner', 'code' => 'FAKE', 'role_id' => Role::where('slug', 'owner')->firstOrFail()->id, 'type' => 'مستخدم'])
            ->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('staff', ['code' => 'FAKE']);

        $user->update(['active' => false]);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
